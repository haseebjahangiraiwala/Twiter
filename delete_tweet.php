<?php
require 'db.php';
if(!isset($_SESSION['user_id'])) { echo "<script>window.location='login.php';</script>"; exit; }
$me = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "Invalid."; exit; }
 
$t = $mysqli->prepare("SELECT * FROM tweets WHERE id=? AND user_id=?");
$t->bind_param('ii',$id,$me); $t->execute(); $tweet = $t->get_result()->fetch_assoc();
if(!$tweet){ echo "Not found or permission denied."; exit; }
 
if(isset($_POST['confirm'])){
    $d = $mysqli->prepare("DELETE FROM tweets WHERE id=?");
    $d->bind_param('i',$id); $d->execute();
    echo "<script>window.location='index.php';</script>"; exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Delete Tweet</title>
<style>
body{font-family:Segoe UI,Roboto,Arial;background:#f8fafc;margin:0;padding:40px}
.card{background:#fff;max-width:600px;margin:0 auto;padding:18px;border-radius:10px;box-shadow:0 10px 24px rgba(0,0,0,0.05)}
.btn{padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
.btn-danger{background:#ef4444;color:#fff}
.btn-cancel{background:#6b7280;color:#fff;margin-left:8px}
</style>
</head><body>
<div class="card">
  <h3>Delete Tweet</h3>
  <p>Are you sure you want to delete this tweet?</p>
  <blockquote style="background:#f1f5f9;padding:10px;border-radius:6px"><?=htmlspecialchars($tweet['content'])?></blockquote>
  <form method="post">
    <button class="btn btn-danger" name="confirm">Yes, Delete</button>
    <button type="button" class="btn btn-cancel" onclick="window.location='index.php'">Cancel</button>
  </form>
</div>
</body></html>
 
