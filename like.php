<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit; }
$me = $_SESSION['user_id'];
$t = intval($_POST['tweet_id'] ?? 0);
if (!$t) { http_response_code(400); exit; }
 
// check existing
$ch = $mysqli->prepare("SELECT id FROM likes WHERE user_id=? AND tweet_id=?");
$ch->bind_param('ii',$me,$t); $ch->execute(); $r = $ch->get_result();
if ($r->fetch_assoc()) {
    $d = $mysqli->prepare("DELETE FROM likes WHERE user_id=? AND tweet_id=?");
    $d->bind_param('ii',$me,$t); $d->execute();
} else {
    $i = $mysqli->prepare("INSERT INTO likes (user_id,tweet_id) VALUES (?,?)");
    $i->bind_param('ii',$me,$t); $i->execute();
}
echo 'ok';
 
