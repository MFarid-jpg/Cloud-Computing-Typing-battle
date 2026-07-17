<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join'])) {
    $nickname = trim(strip_tags($_POST['nickname']));
    if (strlen($nickname) >= 2 && strlen($nickname) <= 20) {
        $stmt = $pdo->prepare("INSERT INTO users (nickname) VALUES (?)");
        $stmt->execute([$nickname]);
        $_SESSION['user_id']  = $pdo->lastInsertId();
        $_SESSION['nickname'] = $nickname;
        header("Location: lobby.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Battle</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#1a1b1e;--sur:#25262b;--brd:#3a3c42;--y:#e2b714;--txt:#d1d0c5;--mut:#646669;--mono:'Share Tech Mono',monospace;--disp:'Rajdhani',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg);color:var(--txt);font-family:var(--disp);min-height:100vh;display:flex;justify-content:center;align-items:center}
        body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(226,183,20,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(226,183,20,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;animation:grid 20s linear infinite}
        @keyframes grid{to{background-position:40px 40px}}
        .card{background:var(--sur);border:1px solid var(--brd);border-top:3px solid var(--y);border-radius:16px;padding:50px 45px;width:420px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.5);animation:up .45s ease}
        @keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .logo{font-size:2.8rem;font-weight:700;color:var(--y);letter-spacing:2px}
        .logo span{color:var(--txt)}
        .sub{font-family:var(--mono);font-size:.72rem;color:var(--mut);letter-spacing:3px;margin-bottom:36px}
        label{display:block;text-align:left;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;color:var(--mut);margin-bottom:7px}
        input[type=text]{width:100%;background:var(--bg);border:2px solid var(--brd);border-radius:8px;padding:13px 15px;font-family:var(--mono);font-size:1rem;color:var(--txt);outline:none;transition:border-color .2s,box-shadow .2s;caret-color:var(--y)}
        input[type=text]:focus{border-color:var(--y);box-shadow:0 0 0 3px rgba(226,183,20,.1)}
        .btn{margin-top:14px;width:100%;padding:15px;background:var(--y);border:none;border-radius:8px;font-family:var(--disp);font-size:1.05rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#1a1b1e;cursor:pointer;box-shadow:0 4px 0 #a07800;transition:transform .15s,box-shadow .15s}
        .btn:hover{transform:translateY(-2px);box-shadow:0 6px 0 #a07800}
        .btn:active{transform:translateY(2px);box-shadow:0 2px 0 #a07800}
        hr{border:none;border-top:1px solid var(--brd);margin:28px 0 18px}
        .hint{font-family:var(--mono);font-size:.68rem;color:var(--mut);line-height:1.9}
    </style>
</head>
<body>
<div class="card">
    <div class="logo">Typing<span>Battle</span></div>
    <p class="sub">REAL-TIME MULTIPLAYER RACE</p>
    <form method="POST">
        <label for="nick">Nickname Kamu</label>
        <input type="text" id="nick" name="nickname" placeholder="masukkan nama..." maxlength="20" required autofocus autocomplete="off">
        <button type="submit" name="join" class="btn">⌨️ Masuk ke Lobby</button>
    </form>
    <hr>
    <p class="hint">Bergabung ke lobby → tunggu pemain lain<br>Host pencet Mulai → hitung mundur → ketik!</p>
</div>
</body>
</html>