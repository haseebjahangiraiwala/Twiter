<?php
require 'db.php';
if(!isset($_SESSION['user_id'])) { echo "<script>window.location='login.php';</script>"; exit; }
$me = $_SESSION['user_id'];
$uid = isset($_GET['id']) ? intval($_GET['id']) : $me;
 
// update profile
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bio'])) {
    $bio = $_POST['bio'];
    $stmt = $mysqli->prepare("UPDATE users SET bio=? WHERE id=?");
    $stmt->bind_param('si',$bio,$me); $stmt->execute();
    echo "<script>window.location='profile.php';</script>"; exit;
}
 
// fetch user
$stmt = $mysqli->prepare("SELECT id,username,display_name,bio,created_at FROM users WHERE id=?");
$stmt->bind_param('i',$uid); $stmt->execute(); $user = $stmt->get_result()->fetch_assoc();
 
// fetch tweets of this user
$tw = $mysqli->prepare("SELECT * FROM tweets WHERE user_id=? ORDER BY created_at DESC LIMIT 200");
$tw->bind_param('i',$uid); $tw->execute(); $tweets = $tw->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($user['display_name'])?> - Profile</title>
<style>
body{font-family:Segoe UI,Roboto,Arial;background:#eef6fb;margin:0;padding:20px}
.card{background:#fff;padding:18px;border-radius:12px;max-width:900px;margin:0 auto;box-shadow:0 8px 20px rgba(0,0,0,0.04)}
.header{display:flex;justify-content:space-between;align-items:center}
.btn{background:#1da1f2;color:#fff;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
.small{color:#666}
.tweet{border-top:1px solid #f1f5f9;padding:12px 0}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <div>
      <div style="font-weight:800;font-size:20px"><?=htmlspecialchars($user['display_name'])?></div>
      <div class="small">@<?=htmlspecialchars($user['username'])?> · Joined <?=date('M Y', strtotime($user['created_at']))?></div>
    </div>
    <div>
      <?php if($uid !== $me): 
        $f = $mysqli->prepare("SELECT id FROM followers WHERE follower_id=? AND following_id=?");
        $f->bind_param('ii',$me,$uid); $f->execute(); $isf = (bool)$f->get_result()->fetch_assoc();
        if($isf): ?>
          <button class="btn" onclick="follow(<?=$uid?>,'unfollow')">Unfollow</button>
        <?php else: ?>
          <button class="btn" onclick="follow(<?=$uid?>,'follow')">Follow</button>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn" onclick="document.getElementById('edit').style.display='block'">Edit Profile</button>
      <?php endif; ?>
    </div>
  </div>
 
  <div style="margin-top:12px"><?=nl2br(htmlspecialchars($user['bio']))?></div>
 
  <div id="edit" style="display:none;margin-top:12px">
    <form method="post">
      <textarea name="bio" style="width:100%;height:80px;border:1px solid #e6eef8;border-radius:8px;padding:8px"><?=htmlspecialchars($user['bio'])?></textarea>
      <div style="margin-top:8px"><button class="btn" type="submit">Save</button></div>
    </form>
  </div>
 
  <div style="margin-top:16px;font-weight:700">Tweets</div>
  <?php while($t = $tweets->fetch_assoc()): ?>
    <div class="tweet">
      <div style="display:flex;justify-content:space-between">
        <div><strong><?=htmlspecialchars($user['display_name'])?></strong> <span class="small">@<?=htmlspecialchars($user['username'])?> · <?=date('M j, H:i',strtotime($t['created_at']))?></span></div>
        <?php if($t['user_id'] == $me): ?>
          <div><a href="edit_tweet.php?id=<?=$t['id']?>">Edit</a> · <a href="delete_tweet.php?id=<?=$t['id']?>">Delete</a></div>
        <?php endif; ?>
      </div>
      <div style="margin-top:8px"><?=htmlspecialchars($t['content'])?></div>
    </div>
  <?php endwhile; ?>
</div>
 
<script>
function follow(target,action){
  fetch('follow.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'target='+encodeURIComponent(target)+'&action='+encodeURIComponent(action)
  }).then(()=>{ location.reload(); });
}
</script>
</body>
</html>
 
