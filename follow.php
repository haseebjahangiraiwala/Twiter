<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit; }
$me = $_SESSION['user_id'];
$target = intval($_POST['target'] ?? 0);
$action = $_POST['action'] ?? '';
if (!$target || ($action!='follow' && $action!='unfollow')) { http_response_code(400); exit; }
if ($action === 'follow') {
    $ins = $mysqli->prepare("INSERT IGNORE INTO followers (follower_id, following_id) VALUES (?,?)");
    $ins->bind_param('ii',$me,$target); $ins->execute();
} else {
    $del = $mysqli->prepare("DELETE FROM followers WHERE follower_id=? AND following_id=?");
    $del->bind_param('ii',$me,$target); $del->execute();
}
echo 'ok';
