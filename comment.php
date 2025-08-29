<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { echo "<script>window.location='login.php';</script>"; exit;}
$me = $_SESSION['user_id'];
$tweet_id = intval($_GET['id'] ?? 0);
if (!$tweet_id) { echo "Invalid tweet."; exit; }
 
// handle post comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $c = trim($_POST['comment']);
    if ($c !== '') {
        $ins = $mysqli->prepare("INSERT INTO comments (user_id,tweet_id,content) VALUES (?,?,?)");
        $ins->bind_param('iis',$me,$tweet_id,$c); $ins->execute();
    }
    echo "<script>window.location='comment.php?id={$tweet_id}';</script>"; exit;
}
 
// fetch tweet
$tq = $mysqli->prepare("SELECT t.*, u.username, u.display_name FROM tweets t JOIN users u ON t.user_id=u.id WHERE t.id=?");
$tq->bind_param('i',$tweet_id); $tq->execute(); $tweet = $tq->get_result()->fetch_assoc();
$cm = $mysqli->prepare("SELECT c.*, u.username, u.display_name FROM comments c JOIN users u ON c.user_id=u.id WHERE c.tweet_id=? ORDER BY c.created_at ASC");
$cm->bind_param('i',$tweet_id); $cm->execute(); $comments = $cm->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Comments</title>
<style>
body{font-family:Segoe UI,Roboto,Arial;background:#f2f6fb;margin:0;padding:20px}
.card{max-width:700px;margin:0 auto;background:#fff;padding:18px;border-radius:12px;box-shadow:0 8px 20px rgba(0,0,0,0.04)}
.input{width:100%;padding:10px;border:1px solid #e6eef8;border-radius:8px}
.btn{background:#1da1f2;color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer}
.comment{border-top:1px solid #f1f5f9;padding:12px 0}
.small{color:#666}
</style>
</head>
<body>
<div class="card">
  <div><strong><?=htmlspecialchars($tweet['display_name'])?></strong> @<?=htmlspecialchars($tweet['username'])?> · <?=htmlspecialchars($tweet['created_at'])?></div>
  <div style="margin-top:8px"><?=htmlspecialchars($tweet['content'])?></div>
 
  <div style="margin-top:16px">
    <form method="post">
      <input name="comment" class="input" placeholder="Write a reply..." />
      <div style="margin-top:8px"><button class="btn" type="submit">Reply</button></div>
    </form>
  </div>
 
  <div style="margin-top:16px;font-weight:700">Replies</div>
  <?php while($c = $comments->fetch_assoc()): ?>
    <div class="comment">
      <div><strong><?=htmlspecialchars($c['display_name'])?></strong> <span class="small">@<?=htmlspecialchars($c['username'])?> · <?=htmlspecialchars($c['created_at'])?></span></div>
      <div style="margin-top:6px"><?=htmlspecialchars($c['content'])?></div>
    </div>
  <?php endwhile; ?>
  <div style="margin-top:12px"><button class="btn" onclick="window.location='index.php'">Back</button></div>
</div>
</body>
</html>
 
