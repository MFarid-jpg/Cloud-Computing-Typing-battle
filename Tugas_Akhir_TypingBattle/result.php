<?php
session_start();
include 'db_config.php';

if(empty($_SESSION['user_id'])){header("Location: index.php");exit();}

$userId    = (int)$_SESSION['user_id'];
$nickname  = $_SESSION['nickname'];
$sessionId = (int)($_GET['session_id'] ?? $_SESSION['session_id'] ?? 0);

$wpm = (float)($_GET['wpm'] ?? 0);
$acc = (float)($_GET['acc'] ?? 0);
$err = (float)($_GET['err'] ?? 100 - $acc);
$r1  = (float)($_GET['r1']  ?? 0);
$r5  = (float)($_GET['r5']  ?? 0);

$status = "Pemula";
if($wpm>80)      $status="Legenda";
elseif($wpm>60)  $status="Sangat Cepat";
elseif($wpm>40)  $status="Cepat";
elseif($wpm>25)  $status="Rata-rata";

$trend = $r5 >= $r1 ? '📈 Meningkat' : '📉 Menurun';
$trendColor = $r5 >= $r1 ? '#9ece6a' : '#ca4754';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Battle – Hasil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#1a1b1e;--sur:#25262b;--sur2:#2c2e33;--brd:#3a3c42;--y:#e2b714;--txt:#d1d0c5;--mut:#646669;--red:#ca4754;--grn:#9ece6a;--cyn:#78dce8;--mono:'Share Tech Mono',monospace;--disp:'Rajdhani',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg);color:var(--txt);font-family:var(--disp);min-height:100vh;display:flex;justify-content:center;align-items:flex-start;padding:30px 16px;gap:20px;flex-wrap:wrap}

        /* ── Kartu kiri: hasil pemain ini ── */
        .my-card{background:var(--sur);border:1px solid var(--brd);border-radius:18px;padding:35px 32px;width:420px;flex-shrink:0}
        .rc-top{text-align:center;margin-bottom:24px}
        .rc-title{font-size:1.3rem;font-weight:700;letter-spacing:2px;margin-bottom:4px}
        .rc-nick{font-family:var(--mono);font-size:.82rem;color:var(--mut)}
        .wpm-big{font-size:5.5rem;font-weight:700;color:var(--y);line-height:1;margin:10px 0 4px}
        .wpm-label{font-family:var(--mono);font-size:.75rem;color:var(--mut);letter-spacing:3px;text-transform:uppercase}
        .status-badge{display:inline-block;background:rgba(120,220,232,.15);color:var(--cyn);border:1px solid rgba(120,220,232,.3);border-radius:20px;padding:4px 14px;font-size:.78rem;letter-spacing:2px;text-transform:uppercase;margin:10px 0 20px;font-weight:600}

        .sgrid{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-bottom:18px}
        .sbox{background:var(--sur2);border-radius:10px;padding:14px;text-align:center}
        .sbl{font-family:var(--mono);font-size:.62rem;color:var(--mut);letter-spacing:2px;text-transform:uppercase;margin-bottom:5px}
        .sbv{font-size:1.4rem;font-weight:700}

        .progress-box{background:rgba(226,183,20,.05);border:1px dashed rgba(226,183,20,.3);border-radius:12px;padding:16px;margin-bottom:20px}
        .pb-title{font-size:.72rem;color:var(--y);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;font-weight:600}
        .pb-row{display:flex;justify-content:space-between;align-items:center}
        .pb-val{font-size:1.2rem;font-weight:700}
        .pb-label{font-family:var(--mono);font-size:.68rem;color:var(--mut)}
        .pb-arrow{font-size:1.4rem;color:var(--y)}

        .btn-main{display:block;width:100%;padding:15px;background:var(--y);border:none;border-radius:10px;font-family:var(--disp);font-size:1.05rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#1a1b1e;cursor:pointer;text-decoration:none;text-align:center;box-shadow:0 4px 0 #a07800;transition:transform .15s,box-shadow .15s;margin-bottom:10px}
        .btn-main:hover{transform:translateY(-2px);box-shadow:0 6px 0 #a07800}
        .btn-sec{display:block;text-align:center;font-family:var(--mono);font-size:.75rem;color:var(--mut);text-decoration:none;padding:6px}
        .btn-sec:hover{color:var(--txt)}

        /* ── Kartu kanan: papan pemimpin ── */
        .leaderboard{background:var(--sur);border:1px solid var(--brd);border-radius:18px;padding:28px;width:420px;flex-shrink:0}
        .lb-title{font-size:1rem;font-weight:700;letter-spacing:2px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
        .lb-title::after{content:'';flex:1;height:1px;background:var(--brd)}

        .lb-list{list-style:none;display:flex;flex-direction:column;gap:10px}
        .lb-item{display:flex;align-items:center;gap:13px;background:var(--sur2);border:1px solid var(--brd);border-radius:10px;padding:13px 15px}
        .lb-item.me{border-color:var(--y);background:rgba(226,183,20,.07)}
        .lb-item.rank1{border-color:var(--y)}
        .lb-item.rank2{border-color:#c0c0c0}
        .lb-item.rank3{border-color:#cd7f32}

        .rank-num{font-size:1.1rem;font-weight:700;width:26px;text-align:center;flex-shrink:0}
        .rank1 .rank-num{color:#ffd700}
        .rank2 .rank-num{color:#c0c0c0}
        .rank3 .rank-num{color:#cd7f32}

        .lb-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;flex-shrink:0;text-transform:uppercase}
        .lb-info{flex:1}
        .lb-name{font-size:.95rem;font-weight:600}
        .lb-sub{font-family:var(--mono);font-size:.68rem;color:var(--mut);margin-top:2px}
        .lb-wpm{font-size:1.15rem;font-weight:700;color:var(--y);font-family:var(--mono)}
        .lb-wpm small{font-size:.6rem;color:var(--mut)}

        .lb-loading{font-family:var(--mono);font-size:.78rem;color:var(--mut);text-align:center;padding:20px}

        /* warna rank */
        .medal{font-size:1.2rem}
    </style>
</head>
<body>

<!-- Kartu Hasil Pribadi -->
<div class="my-card">
    <div class="rc-top">
        <div class="rc-title">🏁 Balapan Selesai!</div>
        <div class="rc-nick">Hebat, <strong><?= htmlspecialchars($nickname) ?></strong>!</div>
    </div>

    <div style="text-align:center">
        <div class="wpm-big"><?= round($wpm) ?></div>
        <div class="wpm-label">WPM</div>
        <div class="status-badge"><?= $status ?></div>
    </div>

    <div class="sgrid">
        <div class="sbox">
            <div class="sbl">Akurasi</div>
            <div class="sbv" style="color:var(--grn)"><?= round($acc) ?>%</div>
        </div>
        <div class="sbox">
            <div class="sbl">Error Rate</div>
            <div class="sbv" style="color:var(--red)"><?= round($err) ?>%</div>
        </div>
        <div class="sbox">
            <div class="sbl">WPM Awal (R1)</div>
            <div class="sbv" style="color:var(--cyn)"><?= round($r1) ?></div>
        </div>
        <div class="sbox">
            <div class="sbl">WPM Akhir (R5)</div>
            <div class="sbv" style="color:var(--y)"><?= round($r5) ?></div>
        </div>
    </div>

    <div class="progress-box">
        <div class="pb-title">Statistik Kemajuan R1 → R5</div>
        <div class="pb-row">
            <div style="text-align:left">
                <div class="pb-label">Awal (R1)</div>
                <div class="pb-val"><?= round($r1) ?> WPM</div>
            </div>
            <div>
                <div class="pb-arrow">➔</div>
                <div style="font-family:var(--mono);font-size:.7rem;color:<?= $trendColor ?>;text-align:center;margin-top:3px"><?= $trend ?></div>
            </div>
            <div style="text-align:right">
                <div class="pb-label">Akhir (R5)</div>
                <div class="pb-val"><?= round($r5) ?> WPM</div>
            </div>
        </div>
    </div>

    <a href="game.php" class="btn-main">🔁 Main Lagi</a>
    <a href="index.php" class="btn-sec">Keluar ke Menu Utama</a>
</div>

<!-- Papan Pemimpin Semua Pemain -->
<div class="leaderboard">
    <div class="lb-title">🏆 Papan Pemimpin</div>
    <ul class="lb-list" id="lbList">
        <li class="lb-loading">Memuat hasil pemain lain...</li>
    </ul>
</div>

<script>
const MY_ID   = <?= $userId ?>;
const SES_ID  = <?= $sessionId ?>;
const COLORS  = ['#e2b714','#78dce8','#9ece6a','#ff9e64','#c678dd','#61afef','#e06c75','#b5c4d1'];
const MEDALS  = ['🥇','🥈','🥉'];

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}

function renderLB(players){
    const list = document.getElementById('lbList');
    if(!players||!players.length){
        list.innerHTML='<li class="lb-loading">Belum ada data pemain lain.</li>';
        return;
    }
    list.innerHTML='';
    players.forEach((p,i)=>{
        const isMe  = p.user_id==MY_ID;
        const col   = COLORS[i%COLORS.length];
        const rank  = i+1;
        const medal = MEDALS[i]||'';
        const rankCls = rank<=3?` rank${rank}`:'';
        const li    = document.createElement('li');
        li.className = 'lb-item'+(isMe?' me':'')+rankCls;
        li.innerHTML=`
            <div class="rank-num">${medal||rank}</div>
            <div class="lb-avatar" style="background:${col}22;color:${col};border:2px solid ${col}44">${esc(p.nickname.charAt(0).toUpperCase())}</div>
            <div class="lb-info">
                <div class="lb-name">${esc(p.nickname)}${isMe?' <span style="font-size:.65rem;color:var(--y);font-family:var(--mono)">(kamu)</span>':''}</div>
                <div class="lb-sub">Akurasi ${parseFloat(p.accuracy||0).toFixed(0)}% · Error ${parseFloat(p.error_rate||0).toFixed(0)}%</div>
            </div>
            <div class="lb-wpm">${parseFloat(p.wpm||0).toFixed(0)}<small> WPM</small></div>`;
        list.appendChild(li);
    });
}

// Poll sampai semua selesai atau timeout 30 detik
let attempts = 0;
async function pollResults(){
    try{
        const r = await fetch(`api.php?action=get_results&session_id=${SES_ID}`);
        const d = await r.json();
        renderLB(d.players);
        attempts++;
        if(attempts<20) setTimeout(pollResults,1500); // polling 30 detik
    }catch(e){ console.error(e); }
}

pollResults();
</script>
</body>
</html>