<?php
require 'db.php';
if(!isset($_SESSION['user_id'])) { echo "<script>window.location='login.php';</script>"; exit; }
$me = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "Invalid."; exit; }
 
// fetch tweet and ensure owner
$t = $mysqli->prepare("SELECT * FROM tweets WHERE id=? AND user_id=?");
$t->bind_param('ii',$id,$me); $t->execute(); $tweet = $t->get_result()->fetch_assoc();
if(!$tweet){ echo "Not found or not permitted."; exit; }
 
if($_SERVER['REQUEST_METHOD']==='POST'){
    $content = trim($_POST['content']);
    if($content===''){ $err='Content required.'; }
    else {
        $u = $mysqli->prepare("UPDATE tweets SET content=? WHERE id=?");
        $u->bind_param('si',$content,$id); $u->execute();
        echo "<script>window.location='index.php';</script>"; exit;
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Edit Tweet</title>
<style>
body{font-family:Segoe UI,Roboto,Arial;background:#f4f8fb;margin:0;padding:20px}
.card{background:#fff;max-width:700px;margin:0 auto;padding:16px;border-radius:10px;box-shadow:0 10px 24px rgba(2,6,23,0.06)}
.input{width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px}
.btn{background:#1da1f2;color:#fff;padding:8px 12px;border:none;border-radius:8px;cursor:pointer}
.err{color:#b00020}
</style>
</head><body>
<div class="card">
  <h3>Edit Tweet</h3>
  <?php if(isset($err)) echo "<div class='err'>$err</div>"; ?>
  <form method="post">
    <textarea name="content" class="input" rows="4"><?=htmlspecialchars($tweet['content'])?></textarea>
    <div style="margin-top:8px">
      <button class="btn" type="submit">Save</button>
      <button type="button" class="btn" style="background:#6b7280;margin-left:8px" onclick="window.location='index.php'">Cancel</button>
    </div>
  </form>
</div>
</body></html>
