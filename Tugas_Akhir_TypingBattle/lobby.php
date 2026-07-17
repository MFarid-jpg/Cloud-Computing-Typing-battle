<?php
session_start();
include 'db_config.php';

// Guard: harus login dulu
if (empty($_SESSION['user_id'])) {
    header("Location: index.php"); exit();
}

$userId   = (int)$_SESSION['user_id'];
$nickname = $_SESSION['nickname'];

// ── Cari room yang masih 'waiting' ───────────────────────────────────────────
$stmt = $pdo->query("SELECT * FROM game_sessions WHERE status='waiting' ORDER BY created_at DESC LIMIT 1");
$room = $stmt->fetch();

if (!$room) {
    // Tidak ada room → buat baru, jadilah host
    $roomCode = strtoupper(substr(md5(uniqid('',true)), 0, 6));
    $pdo->prepare("INSERT INTO game_sessions (room_code, host_id, status) VALUES (?,?,'waiting')")
        ->execute([$roomCode, $userId]);
    $sessionId = (int)$pdo->lastInsertId();
    $_SESSION['is_host'] = true;
} else {
    $sessionId = (int)$room['id'];
    $roomCode  = $room['room_code'];
    $_SESSION['is_host'] = ($room['host_id'] == $userId);
}

$_SESSION['session_id'] = $sessionId;
$_SESSION['room_code']  = $roomCode;

// ── Daftarkan pemain ke room (jika belum) ─────────────────────────────────────
$chk = $pdo->prepare("SELECT id FROM room_players WHERE session_id=? AND user_id=?");
$chk->execute([$sessionId, $userId]);
if (!$chk->fetch()) {
    $pdo->prepare("INSERT INTO room_players (session_id, user_id) VALUES (?,?)")
        ->execute([$sessionId, $userId]);
}

$isHost = $_SESSION['is_host'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Battle – Lobby</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#1a1b1e;--sur:#25262b;--sur2:#2c2e33;--brd:#3a3c42;--y:#e2b714;--yd:rgba(226,183,20,.1);--txt:#d1d0c5;--mut:#646669;--red:#ca4754;--grn:#9ece6a;--cyn:#78dce8;--mono:'Share Tech Mono',monospace;--disp:'Rajdhani',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg);color:var(--txt);font-family:var(--disp);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center}
        body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(226,183,20,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(226,183,20,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;animation:grid 20s linear infinite}
        @keyframes grid{to{background-position:40px 40px}}

        .wrap{width:95%;max-width:820px;animation:up .4s ease}
        @keyframes up{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
        .logo{font-size:1.9rem;font-weight:700;color:var(--y);letter-spacing:2px}
        .logo span{color:var(--txt)}
        .room-badge{background:var(--yd);border:1px solid rgba(226,183,20,.3);border-radius:8px;padding:8px 16px;font-family:var(--mono);font-size:.82rem;color:var(--y);letter-spacing:2px}

        .grid{display:grid;grid-template-columns:1fr 290px;gap:18px}

        .card{background:var(--sur);border:1px solid var(--brd);border-radius:14px;padding:22px}
        .ctitle{font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:var(--mut);margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .ctitle::after{content:'';flex:1;height:1px;background:var(--brd)}

        /* player list */
        .plist{list-style:none;display:flex;flex-direction:column;gap:9px}
        .pitem{display:flex;align-items:center;gap:12px;background:var(--sur2);border:1px solid var(--brd);border-radius:10px;padding:13px 15px;animation:fi .3s ease}
        @keyframes fi{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}
        .pitem.me{border-color:var(--y);background:var(--yd)}
        .pitem.host-item{border-color:var(--cyn)}
        .avatar{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;flex-shrink:0;text-transform:uppercase}
        .pname{font-size:1rem;font-weight:600;letter-spacing:.5px}
        .pmeta{font-family:var(--mono);font-size:.68rem;color:var(--mut);margin-top:2px}
        .tags{display:flex;gap:5px;flex-wrap:wrap;justify-content:flex-end;margin-left:auto}
        .tag{font-size:.6rem;letter-spacing:1.5px;text-transform:uppercase;padding:2px 7px;border-radius:4px;font-weight:600}
        .tag-host{background:rgba(120,220,232,.15);color:var(--cyn);border:1px solid rgba(120,220,232,.3)}
        .tag-you{background:var(--yd);color:var(--y);border:1px solid rgba(226,183,20,.3)}
        .tag-ready{background:rgba(158,206,106,.15);color:var(--grn);border:1px solid rgba(158,206,106,.3)}
        .empty{display:flex;align-items:center;gap:12px;border:1px dashed var(--brd);border-radius:10px;padding:13px 15px;opacity:.35}
        .empty-av{width:38px;height:38px;border-radius:50%;background:var(--brd);display:flex;align-items:center;justify-content:center}

        /* right panel */
        .rpanel{display:flex;flex-direction:column;gap:18px}
        .srow{display:flex;justify-content:space-between;margin-bottom:9px;font-size:.88rem}
        .slabel{font-family:var(--mono);font-size:.72rem;color:var(--mut)}
        .sval{font-weight:600}
        .sval.g{color:var(--grn)}.sval.y{color:var(--y)}.sval.c{color:var(--cyn)}

        /* dots */
        .dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--grn);margin-right:5px;animation:lp 1.5s infinite}
        @keyframes lp{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.35;transform:scale(.65)}}

        /* buttons */
        .btn-start{width:100%;padding:15px;background:var(--y);border:none;border-radius:10px;font-family:var(--disp);font-size:1.05rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#1a1b1e;cursor:pointer;box-shadow:0 4px 0 #a07800;transition:transform .15s,box-shadow .15s}
        .btn-start:hover{transform:translateY(-2px);box-shadow:0 6px 0 #a07800}
        .btn-start:active{transform:translateY(2px);box-shadow:0 2px 0 #a07800}
        .btn-start:disabled{opacity:.45;cursor:not-allowed;transform:none}
        .waiting-msg{text-align:center;font-family:var(--mono);font-size:.72rem;color:var(--mut);line-height:2;padding:12px;background:var(--sur2);border-radius:8px}
        .wdots span{animation:wd 1.4s infinite;color:var(--y)}
        .wdots span:nth-child(2){animation-delay:.2s}
        .wdots span:nth-child(3){animation-delay:.4s}
        @keyframes wd{0%,80%,100%{opacity:0}40%{opacity:1}}

        /* countdown overlay */
        .cdover{position:fixed;inset:0;background:rgba(0,0,0,.93);display:none;justify-content:center;align-items:center;z-index:1000;flex-direction:column;gap:16px}
        .cdover.on{display:flex}
        .cdnum{font-size:9rem;font-weight:700;color:var(--y);font-family:var(--disp);line-height:1;animation:pp 1s ease-in-out}
        @keyframes pp{0%{transform:scale(1.5);opacity:0}60%{transform:scale(1.05);opacity:1}100%{transform:scale(1);opacity:1}}
        .cdlabel{font-family:var(--mono);color:var(--mut);letter-spacing:4px;font-size:.85rem}
    </style>
</head>
<body>

<!-- Countdown overlay -->
<div class="cdover" id="cdover">
    <div class="cdlabel">BALAPAN DIMULAI DALAM</div>
    <div class="cdnum" id="cdnum">3</div>
</div>

<div class="wrap">
    <div class="top">
        <div class="logo">Typing<span>Battle</span></div>
        <div class="room-badge">ROOM: <?= $roomCode ?></div>
    </div>

    <div class="grid">
        <!-- Kiri: list pemain -->
        <div class="card">
            <div class="ctitle">
                <span class="dot"></span>Pemain di Lobby
                <span style="font-family:var(--mono);font-size:.78rem;color:var(--y)" id="pcnt">0/8</span>
            </div>
            <ul class="plist" id="plist"></ul>
        </div>

        <!-- Kanan: info + kontrol -->
        <div class="rpanel">
            <div class="card">
                <div class="ctitle">Info Room</div>
                <div class="srow"><span class="slabel">Status</span><span class="sval g">● Menunggu</span></div>
                <div class="srow"><span class="slabel">Ronde</span><span class="sval y">5 Ronde</span></div>
                <div class="srow"><span class="slabel">Kamu</span><span class="sval"><?= htmlspecialchars($nickname) ?></span></div>
                <div class="srow"><span class="slabel">Role</span>
                    <span class="sval <?= $isHost ? 'c' : 'g' ?>"><?= $isHost ? '👑 Host' : '🎮 Player' ?></span>
                </div>
            </div>

            <div class="card">
                <?php if ($isHost): ?>
                    <button class="btn-start" id="startBtn" onclick="hostStart()">🏁 Mulai Race</button>
                    <p style="font-family:var(--mono);font-size:.68rem;color:var(--mut);text-align:center;margin-top:11px;line-height:1.9">
                        Kamu adalah Host.<br>Tekan tombol untuk memulai balapan!
                    </p>
                <?php else: ?>
                    <div class="waiting-msg">
                        Menunggu host memulai<br>
                        <span class="wdots"><span>•</span><span>•</span><span>•</span></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const MY_ID    = <?= $userId ?>;
const SES_ID   = <?= $sessionId ?>;
const IS_HOST  = <?= $isHost ? 'true' : 'false' ?>;
const COLORS   = ['#e2b714','#78dce8','#9ece6a','#ff9e64','#c678dd','#61afef','#e06c75','#b5c4d1'];

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
function initial(n){return n.charAt(0).toUpperCase()}

function renderPlayers(players){
    const list  = document.getElementById('plist');
    const badge = document.getElementById('pcnt');
    badge.textContent = players.length + '/8';
    list.innerHTML = '';

    players.forEach((p,i)=>{
        const isMe   = p.user_id == MY_ID;
        const isHost = p.is_host == 1;
        const col    = COLORS[i % COLORS.length];
        const li     = document.createElement('li');
        li.className = 'pitem' + (isMe?' me':'') + (isHost?' host-item':'');
        li.innerHTML = `
            <div class="avatar" style="background:${col}22;color:${col};border:2px solid ${col}55">${initial(p.nickname)}</div>
            <div>
                <div class="pname">${esc(p.nickname)}</div>
                <div class="pmeta">bergabung ${p.joined_ago}</div>
            </div>
            <div class="tags">
                ${isHost?'<span class="tag tag-host">Host</span>':''}
                ${isMe  ?'<span class="tag tag-you">Kamu</span>':''}
                ${!isHost&&!isMe?'<span class="tag tag-ready">Siap</span>':''}
            </div>`;
        list.appendChild(li);
    });

    // Slot kosong (min tampilkan 4 slot)
    const empties = Math.max(0, 4 - players.length);
    for(let i=0;i<empties;i++){
        const li = document.createElement('li');
        li.className = 'empty';
        li.innerHTML = '<div class="empty-av">👤</div><span style="font-family:var(--mono);font-size:.78rem;color:var(--mut)">Menunggu pemain...</span>';
        list.appendChild(li);
    }
}

let pollTimer;
async function poll(){
    try{
        const r = await fetch(`api.php?action=get_lobby&session_id=${SES_ID}`);
        const d = await r.json();
        renderPlayers(d.players);
        if(d.status === 'playing'){
            clearInterval(pollTimer);
            runCountdown();
        }
    }catch(e){}
}

async function hostStart(){
    const btn = document.getElementById('startBtn');
    if(btn){ btn.disabled=true; btn.textContent='Memulai...'; }
    try{
        await fetch(`api.php?action=start_game&session_id=${SES_ID}`,{method:'POST'});
        clearInterval(pollTimer);
        runCountdown();
    }catch(e){
        if(btn){ btn.disabled=false; btn.textContent='🏁 Mulai Race'; }
    }
}

let cdRunning = false;
function runCountdown(){
    if(cdRunning) return;
    cdRunning = true;
    const ov  = document.getElementById('cdover');
    const num = document.getElementById('cdnum');
    ov.classList.add('on');
    let c = 3;
    const tick = setInterval(()=>{
        num.style.animation='none';
        num.offsetHeight; // reflow
        num.style.animation='pp 1s ease-in-out';
        if(c>0){ num.textContent=c; num.style.color='var(--y)'; }
        else if(c===0){ num.textContent='GO!'; num.style.color='var(--grn)'; }
        else{ clearInterval(tick); window.location.href='game.php'; }
        c--;
    },1000);
}

poll();
pollTimer = setInterval(poll, 2000);

window.addEventListener('beforeunload',()=>{
    navigator.sendBeacon(`api.php?action=leave_lobby&session_id=${SES_ID}&user_id=${MY_ID}`);
});
</script>
</body>
</html>