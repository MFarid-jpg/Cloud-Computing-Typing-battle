<?php
session_start();
include 'db_config.php';

if(empty($_SESSION['user_id'])){header("Location: index.php");exit();}

$userId    = (int)$_SESSION['user_id'];
$nickname  = $_SESSION['nickname'];
$sessionId = (int)($_SESSION['session_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Battle – Race</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#1a1b1e;--sur:#25262b;--sur2:#2c2e33;--brd:#3a3c42;--y:#e2b714;--txt:#d1d0c5;--mut:#646669;--cor:#d1d0c5;--inc:#ca4754;--red:#ca4754;--grn:#9ece6a;--cyn:#78dce8;--mono:'Share Tech Mono',monospace;--disp:'Rajdhani',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg);color:var(--txt);font-family:var(--disp);min-height:100vh;display:flex;justify-content:center;align-items:center;overflow:hidden}

        .layout{display:flex;gap:18px;width:97%;max-width:1220px}

        /* ── Typing area ── */
        .tarea{flex:2.2;background:var(--sur);border:1px solid var(--brd);border-radius:16px;padding:28px}
        .meta{display:flex;gap:18px;margin-bottom:18px;font-family:var(--mono);font-size:.82rem;color:var(--mut);border-bottom:1px solid var(--brd);padding-bottom:12px;flex-wrap:wrap}
        .mv{color:var(--y);font-weight:bold;font-size:.95rem}

        .tdisp{font-family:var(--mono);font-size:1.55rem;line-height:1.85;margin-bottom:22px;color:var(--mut);min-height:115px;word-break:break-word}
        .tdisp .cor{color:var(--cor)}
        .tdisp .inc{color:var(--inc);text-decoration:underline;text-decoration-color:var(--inc)}

        .tinput{width:100%;height:105px;background:var(--sur2);border:2px solid var(--brd);border-radius:10px;color:#fff;caret-color:var(--y);padding:13px 15px;font-family:var(--mono);font-size:1.25rem;resize:none;outline:none;transition:border-color .2s,box-shadow .2s}
        .tinput:focus{border-color:var(--y);box-shadow:0 0 0 3px rgba(226,183,20,.08)}
        .tinput:disabled{opacity:.35;cursor:not-allowed}

        .rdots{display:flex;gap:6px;margin-top:14px}
        .rd{flex:1;height:4px;background:var(--brd);border-radius:2px;transition:background .3s}
        .rd.done{background:var(--y)}.rd.act{background:var(--grn);animation:rp 1s ease infinite}
        @keyframes rp{0%,100%{opacity:1}50%{opacity:.4}}

        /* ── Right panel ── */
        .rpanel{flex:.92;display:flex;flex-direction:column;gap:16px}
        .card{background:var(--sur);border:1px solid var(--brd);border-radius:14px;padding:18px}
        .ctitle{font-size:.68rem;letter-spacing:3px;text-transform:uppercase;color:var(--mut);margin-bottom:13px}

        .ppitem{margin-bottom:13px}
        .ppitem:last-child{margin-bottom:0}
        .pph{display:flex;justify-content:space-between;margin-bottom:4px}
        .ppname{font-size:.85rem;font-weight:600}.ppname.me{color:var(--y)}
        .ppwpm{font-family:var(--mono);font-size:.72rem;color:var(--mut)}
        .ppbg{background:var(--sur2);border-radius:4px;height:7px;overflow:hidden}
        .ppbar{height:100%;border-radius:4px;transition:width .35s ease}
        .ppbar.me{background:var(--y)}.ppbar.ot{background:var(--cyn)}.ppbar.fin{background:var(--grn)}

        .sgrid{display:grid;grid-template-columns:1fr 1fr;gap:9px}
        .sbox{background:var(--sur2);border-radius:8px;padding:11px;text-align:center}
        .sbl{font-family:var(--mono);font-size:.62rem;color:var(--mut);letter-spacing:2px;text-transform:uppercase;margin-bottom:3px}
        .sbv{font-size:1.35rem;font-weight:700}
        .sbv.wc{color:var(--y)}.sbv.ac{color:var(--grn)}.sbv.ec{color:var(--red)}.sbv.rc{color:var(--cyn)}

        /* finish overlay */
        .fover{position:fixed;inset:0;background:rgba(0,0,0,.72);display:none;justify-content:center;align-items:center;z-index:500}
        .fover.on{display:flex}
        .fbox{background:var(--sur);border:2px solid var(--grn);border-radius:16px;padding:28px 40px;text-align:center;animation:pop .4s cubic-bezier(.34,1.56,.64,1)}
        @keyframes pop{from{transform:scale(.7);opacity:0}to{transform:scale(1);opacity:1}}
        .fbox h2{font-family:var(--disp);font-size:1.7rem;color:var(--grn);margin-bottom:7px}
        .fbox p{font-family:var(--mono);font-size:.8rem;color:var(--mut)}
    </style>
</head>
<body>

<div class="fover" id="fover">
    <div class="fbox">
        <h2>🏁 Semua Ronde Selesai!</h2>
        <p>Mengalihkan ke halaman hasil...</p>
    </div>
</div>

<div class="layout">
    <div class="tarea">
        <div class="meta">
            <div>Ronde: <span class="mv" id="rnum">1</span>/5</div>
            <div>WPM: <span class="mv" id="wpmD">0</span></div>
            <div>Akurasi: <span class="mv" id="accD">100</span>%</div>
            <div>Error: <span class="mv" id="errD">0</span>%</div>
        </div>
        <div id="tdisp" class="tdisp"></div>
        <textarea id="tinput" class="tinput" disabled placeholder="Memuat kalimat..."></textarea>
        <div class="rdots" id="rdots">
            <div class="rd act"></div><div class="rd"></div><div class="rd"></div><div class="rd"></div><div class="rd"></div>
        </div>
    </div>

    <div class="rpanel">
        <div class="card">
            <div class="ctitle">🏁 Progres Pemain</div>
            <div id="plist"></div>
        </div>
        <div class="card">
            <div class="ctitle">📊 Statistikmu</div>
            <div class="sgrid">
                <div class="sbox"><div class="sbl">WPM</div><div class="sbv wc" id="sWpm">0</div></div>
                <div class="sbox"><div class="sbl">Akurasi</div><div class="sbv ac" id="sAcc">100%</div></div>
                <div class="sbox"><div class="sbl">Error</div><div class="sbv ec" id="sErr">0%</div></div>
                <div class="sbox"><div class="sbl">Ronde</div><div class="sbv rc" id="sRnd">1/5</div></div>
            </div>
        </div>
    </div>
</div>

<script>
const MY_ID   = <?= $userId ?>;
const MY_NICK = <?= json_encode($nickname) ?>;
const SES_ID  = <?= $sessionId ?>;
const MAX_RND = 5;
const COLORS  = ['#e2b714','#78dce8','#9ece6a','#ff9e64','#c678dd','#61afef','#e06c75','#b5c4d1'];

// ── State ──────────────────────────────────────────────────────────────────────
let phrases      = [];
let curRound     = 0;
let startTime    = null;
let wpmR1 = 0, wpmR5 = 0;

// Akurasi & error tracking
let totalTyped  = 0;   // semua penekanan tombol (karakter baru)
let totalErrors = 0;   // semua karakter yang diketik salah (saat pertama diketik)
let prevValue   = '';  // nilai input sebelum event ini
let charStates  = [];  // 'correct'|'incorrect'|'' per indeks

let myProgress = 0, myWpm = 0;
let colorMap = {}, colorIdx = 0;

function pColor(uid){
    if(!colorMap[uid]) colorMap[uid] = uid==MY_ID ? '#e2b714' : COLORS[colorIdx++ % COLORS.length];
    return colorMap[uid];
}
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}

// ── Load kalimat dari server ────────────────────────────────────────────────────
async function loadPhrases(){
    try{
        const r = await fetch(`api.php?action=get_phrases&session_id=${SES_ID}`);
        const d = await r.json();
        phrases = d.phrases;
    }catch(e){
        // Fallback kalimat jika server error
        phrases = [
            {id:1,content:"Matahari terbit dari ufuk timur membawa harapan baru bagi semua orang."},
            {id:2,content:"Belajar pemrograman web membutuhkan ketekunan dan logika yang kuat."},
            {id:3,content:"Teknologi kecerdasan buatan berkembang sangat pesat di era digital ini."},
            {id:4,content:"Mengetik dengan sepuluh jari dapat meningkatkan produktivitas kerja kita."},
            {id:5,content:"Sistem operasi Windows menggunakan sistem file NTFS untuk manajemen data."},
        ];
    }
    startRound(0);
}

// ── Mulai satu ronde ───────────────────────────────────────────────────────────
function startRound(idx){
    curRound    = idx;
    const sent  = phrases[idx].content;
    charStates  = new Array(sent.length).fill('');
    prevValue   = '';
    startTime   = new Date();

    renderText(sent, charStates);

    document.getElementById('rnum').textContent = idx+1;
    document.getElementById('sRnd').textContent = `${idx+1}/5`;

    // Update dots
    document.querySelectorAll('.rd').forEach((d,i)=>{
        d.className = 'rd' + (i<idx?' done':'') + (i===idx?' act':'');
    });

    const inp = document.getElementById('tinput');
    inp.value    = '';
    inp.disabled = false;
    inp.focus();
}

// ── Render teks dengan warna ────────────────────────────────────────────────────
function renderText(sent, states){
    const el = document.getElementById('tdisp');
    el.innerHTML = '';
    sent.split('').forEach((ch,i)=>{
        const s = document.createElement('span');
        s.textContent = ch;
        if(states[i]==='correct')   s.className='cor';
        if(states[i]==='incorrect') s.className='inc';
        el.appendChild(s);
    });
}

// ── Input handler ──────────────────────────────────────────────────────────────
document.getElementById('tinput').addEventListener('input', e=>{
    const sent     = phrases[curRound].content;
    const newVal   = e.target.value;

    if(newVal.length > prevValue.length){
        // Karakter baru diketik
        const idx    = newVal.length - 1;
        const ch     = newVal[idx];
        totalTyped++;
        if(ch === sent[idx]){
            charStates[idx] = 'correct';
        } else {
            charStates[idx] = 'incorrect';
            totalErrors++;   // ← dihitung setiap penekanan tombol yang salah
        }
    } else {
        // Backspace: reset state karakter yang dihapus (error sudah tercatat)
        for(let i = newVal.length; i < prevValue.length; i++) charStates[i] = '';
    }

    prevValue = newVal;
    renderText(sent, charStates);

    // Progres berurutan (berhenti di karakter salah pertama)
    let consec = 0;
    for(let i=0;i<newVal.length;i++){
        if(charStates[i]==='correct') consec++;
        else break;
    }

    // Statistik
    const elapsed = Math.max((new Date()-startTime)/60000, 0.001);
    const wpm     = Math.round((consec/5)/elapsed)||0;

    // Akurasi = (total ketikan - total error) / total ketikan × 100
    const acc = totalTyped>0 ? Math.max(0, Math.round(((totalTyped-totalErrors)/totalTyped)*100)) : 100;
    const err = 100-acc;

    // Update UI
    document.getElementById('wpmD').textContent = wpm;
    document.getElementById('accD').textContent = acc;
    document.getElementById('errD').textContent = err;
    document.getElementById('sWpm').textContent = wpm;
    document.getElementById('sAcc').textContent = acc+'%';
    document.getElementById('sErr').textContent = err+'%';

    myProgress = (consec/sent.length)*100;
    myWpm      = wpm;

    throttleUpdate(myProgress, wpm, acc, err);

    // Selesai ronde jika input === kalimat
    if(newVal === sent) doneRound(wpm, acc, err);
});

function doneRound(wpm, acc, err){
    document.getElementById('tinput').disabled = true;
    if(curRound===0) wpmR1 = wpm;
    if(curRound===MAX_RND-1) wpmR5 = wpm;
    if(curRound < MAX_RND-1){
        setTimeout(()=>startRound(curRound+1), 350);
    } else {
        wpmR5 = wpm;
        finishGame(wpm, acc, err);
    }
}

async function finishGame(wpm, acc, err){
    try{
        await fetch(`api.php?action=finish&session_id=${SES_ID}&user_id=${MY_ID}&wpm=${wpm}&accuracy=${acc}&error_rate=${err}&r1=${wpmR1}&r5=${wpmR5}&total_errors=${totalErrors}&total_chars=${totalTyped}`,{method:'POST'});
    }catch(e){}
    document.getElementById('fover').classList.add('on');
    setTimeout(()=>{
        window.location.href=`result.php?wpm=${wpm}&acc=${acc}&err=${err}&r1=${wpmR1}&r5=${wpmR5}&session_id=${SES_ID}`;
    },1600);
}

// ── Progress panel polling ─────────────────────────────────────────────────────
function renderPlist(players){
    const el = document.getElementById('plist');
    el.innerHTML='';
    players.forEach((p,i)=>{
        const isMe  = p.user_id==MY_ID;
        const col   = pColor(p.user_id);
        const prog  = isMe ? myProgress : parseFloat(p.progress||0);
        const w     = isMe ? myWpm      : parseFloat(p.wpm||0);
        const fin   = p.finished==1;
        const d     = document.createElement('div');
        d.className = 'ppitem';
        d.innerHTML = `
            <div class="pph">
                <span class="ppname${isMe?' me':''}">${esc(p.nickname)}${fin?' 🏁':''}</span>
                <span class="ppwpm">${Math.round(w)} WPM</span>
            </div>
            <div class="ppbg">
                <div class="ppbar ${isMe?'me':'ot'} ${fin?'fin':''}" style="width:${Math.min(100,prog).toFixed(1)}%;${!isMe?'background:'+col:''}"></div>
            </div>`;
        el.appendChild(d);
    });
}

let lastUp = 0;
function throttleUpdate(prog, wpm, acc, err){
    const now = Date.now();
    if(now-lastUp < 900) return;
    lastUp = now;
    fetch(`api.php?action=update_progress&session_id=${SES_ID}&user_id=${MY_ID}&progress=${prog.toFixed(1)}&round=${curRound+1}&wpm=${wpm}&accuracy=${acc}&error_rate=${err}`);
}

async function pollProg(){
    try{
        const r = await fetch(`api.php?action=get_progress&session_id=${SES_ID}`);
        const d = await r.json();
        renderPlist(d.players);
    }catch(e){}
}

loadPhrases();
pollProg();
setInterval(pollProg,1500);
</script>
</body>
</html>