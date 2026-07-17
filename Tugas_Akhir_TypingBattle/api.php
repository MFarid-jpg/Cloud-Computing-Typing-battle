<?php
session_start();
include 'db_config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch($action){

// ── Lobby: ambil daftar pemain ────────────────────────────────────────────────
case 'get_lobby':
    $sid = (int)($_GET['session_id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT rp.user_id, u.nickname, gs.host_id, gs.status,
               TIMESTAMPDIFF(SECOND, rp.joined_at, NOW()) AS sec_ago
        FROM room_players rp
        JOIN users u         ON u.id  = rp.user_id
        JOIN game_sessions gs ON gs.id = rp.session_id
        WHERE rp.session_id = ?
        ORDER BY rp.joined_at ASC
    ");
    $stmt->execute([$sid]);
    $rows   = $stmt->fetchAll();
    $status = 'waiting';
    $players = [];
    foreach($rows as $r){
        $s   = (int)$r['sec_ago'];
        $ago = $s < 60 ? "{$s}d yang lalu" : floor($s/60)."m yang lalu";
        $players[] = [
            'user_id'    => $r['user_id'],
            'nickname'   => $r['nickname'],
            'is_host'    => ($r['user_id'] == $r['host_id']) ? 1 : 0,
            'joined_ago' => $ago,
        ];
        $status = $r['status'];
    }
    echo json_encode(['players'=>$players,'status'=>$status]);
    break;

// ── Host: mulai game ──────────────────────────────────────────────────────────
case 'start_game':
    $sid = (int)($_GET['session_id'] ?? 0);
    // Verifikasi host
    $s = $pdo->prepare("SELECT host_id FROM game_sessions WHERE id=?");
    $s->execute([$sid]);
    $row = $s->fetch();
    if(!$row || $row['host_id'] != $_SESSION['user_id']){
        echo json_encode(['ok'=>false,'error'=>'Bukan host']); break;
    }
    // Pilih 5 kalimat acak, simpan JSON ke phrase_ids
    $ps = $pdo->query("SELECT id, content FROM phrases ORDER BY RAND() LIMIT 5");
    $phrases = $ps->fetchAll();
    $pdo->prepare("UPDATE game_sessions SET status='playing', phrase_ids=? WHERE id=?")
        ->execute([json_encode($phrases), $sid]);
    echo json_encode(['ok'=>true]);
    break;

// ── Game: ambil kalimat ───────────────────────────────────────────────────────
case 'get_phrases':
    $sid = (int)($_GET['session_id'] ?? 0);
    $s   = $pdo->prepare("SELECT phrase_ids FROM game_sessions WHERE id=?");
    $s->execute([$sid]);
    $row = $s->fetch();
    if($row && $row['phrase_ids']){
        $phrases = json_decode($row['phrase_ids'], true);
        echo json_encode(['phrases'=>$phrases]);
    } else {
        // Fallback acak
        $ps = $pdo->query("SELECT id, content FROM phrases ORDER BY RAND() LIMIT 5");
        echo json_encode(['phrases'=>$ps->fetchAll()]);
    }
    break;

// ── Game: update progres pemain ───────────────────────────────────────────────
case 'update_progress':
    $sid      = (int)($_GET['session_id'] ?? 0);
    $uid      = (int)($_GET['user_id']    ?? 0);
    $progress = (float)($_GET['progress']   ?? 0);
    $round    = (int)($_GET['round']      ?? 1);
    $wpm      = (float)($_GET['wpm']        ?? 0);
    $acc      = (float)($_GET['accuracy']   ?? 100);
    $err      = (float)($_GET['error_rate'] ?? 0);

    $chk = $pdo->prepare("SELECT id FROM player_progress WHERE session_id=? AND user_id=?");
    $chk->execute([$sid,$uid]);
    if($chk->fetch()){
        $pdo->prepare("UPDATE player_progress SET progress=?,current_round=?,wpm=?,accuracy=?,error_rate=?,updated_at=NOW() WHERE session_id=? AND user_id=?")
            ->execute([$progress,$round,$wpm,$acc,$err,$sid,$uid]);
    } else {
        $pdo->prepare("INSERT INTO player_progress (session_id,user_id,progress,current_round,wpm,accuracy,error_rate) VALUES (?,?,?,?,?,?,?)")
            ->execute([$sid,$uid,$progress,$round,$wpm,$acc,$err]);
    }
    echo json_encode(['ok'=>true]);
    break;

// ── Game: ambil progres semua pemain ─────────────────────────────────────────
case 'get_progress':
    $sid  = (int)($_GET['session_id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT pp.user_id, u.nickname, pp.progress, pp.current_round,
               pp.wpm, pp.accuracy, pp.error_rate, pp.finished
        FROM player_progress pp
        JOIN users u ON u.id = pp.user_id
        WHERE pp.session_id = ?
        ORDER BY pp.finished ASC, pp.progress DESC, pp.wpm DESC
    ");
    $stmt->execute([$sid]);
    echo json_encode(['players'=>$stmt->fetchAll()]);
    break;

// ── Game: pemain selesai ──────────────────────────────────────────────────────
case 'finish':
    $sid    = (int)($_GET['session_id']  ?? 0);
    $uid    = (int)($_GET['user_id']     ?? 0);
    $wpm    = (float)($_GET['wpm']       ?? 0);
    $acc    = (float)($_GET['accuracy']  ?? 0);
    $err    = (float)($_GET['error_rate']?? 0);
    $r1     = (float)($_GET['r1']        ?? 0);
    $r5     = (float)($_GET['r5']        ?? 0);
    $tErr   = (int)($_GET['total_errors']?? 0);
    $tChar  = (int)($_GET['total_chars'] ?? 0);

    $pdo->prepare("UPDATE player_progress SET finished=1,progress=100,wpm=?,accuracy=?,error_rate=?,updated_at=NOW() WHERE session_id=? AND user_id=?")
        ->execute([$wpm,$acc,$err,$sid,$uid]);

    $pdo->prepare("INSERT INTO game_results (user_id,session_id,wpm,accuracy,error_rate,raw_wpm,total_errors,total_chars,wpm_round1,wpm_round5) VALUES (?,?,?,?,?,?,?,?,?,?)")
        ->execute([$uid,$sid,$wpm,$acc,$err,$wpm,$tErr,$tChar,$r1,$r5]);

    echo json_encode(['ok'=>true]);
    break;

// ── Result: ambil hasil semua pemain ─────────────────────────────────────────
case 'get_results':
    $sid  = (int)($_GET['session_id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT pp.user_id, u.nickname, pp.wpm, pp.accuracy, pp.error_rate,
               pp.finished, pp.progress, pp.current_round
        FROM player_progress pp
        JOIN users u ON u.id = pp.user_id
        WHERE pp.session_id = ?
        ORDER BY pp.wpm DESC
    ");
    $stmt->execute([$sid]);
    echo json_encode(['players'=>$stmt->fetchAll()]);
    break;

// ── Lobby: hapus player saat keluar ──────────────────────────────────────────
case 'leave_lobby':
    $sid = (int)($_GET['session_id'] ?? 0);
    $uid = (int)($_GET['user_id']    ?? 0);
    $pdo->prepare("DELETE FROM room_players WHERE session_id=? AND user_id=?")->execute([$sid,$uid]);
    echo json_encode(['ok'=>true]);
    break;

default:
    http_response_code(404);
    echo json_encode(['error'=>'Action tidak ditemukan']);
}