<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}
$me = $_SESSION['user_id'];
 
// handle tweet post via POST to same file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_content'])) {
    $content = trim($_POST['tweet_content']);
    if ($content !== '') {
        $stmt = $mysqli->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
        $stmt->bind_param('is',$me,$content);
        $stmt->execute();
    }
    // JS redirect (per your instruction)
    echo "<script>window.location='index.php';</script>";
    exit;
}
 
// fetch feed: tweets from people you follow + your tweets; newest first
$q = "SELECT t.id, t.content, t.created_at, u.id AS user_id, u.username, u.display_name
      FROM tweets t
      JOIN users u ON t.user_id = u.id
      WHERE t.user_id IN (
         SELECT following_id FROM followers WHERE follower_id = ?
      ) OR t.user_id = ?
      ORDER BY t.created_at DESC
      LIMIT 100";
$stmt = $mysqli->prepare($q);
$stmt->bind_param('ii',$me,$me);
$stmt->execute();
$res = $stmt->get_result();
 
function timeAgo($ts){
    $t = strtotime($ts);
    $diff = time()-$t;
    if($diff<60) return $diff.'s';
    if($diff<3600) return floor($diff/60).'m';
    if($diff<86400) return floor($diff/3600).'h';
    return floor($diff/86400).'d';
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Home - MiniTwitter</title>
<style>
/* internal CSS: modern feed */
:root{--blue:#1da1f2}
body{font-family:Segoe UI,Roboto,Arial;background:#e6eef6;margin:0;padding:0}
.container{max-width:900px;margin:24px auto;display:flex;gap:20px;padding:0 16px}
.left{flex:1;min-width:0}
.right{width:300px}
.card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 6px 18px rgba(2,6,23,0.06);margin-bottom:16px}
.tweetbox textarea{width:100%;border:1px solid #dde9f2;border-radius:8px;padding:12px;font-size:15px;resize:none}
.btn{background:var(--blue);color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:600}
.tweet{border-bottom:1px solid #f1f5f9;padding:12px 0;display:flex;gap:12px}
.tweet:last-child{border-bottom:none}
.tmeta{font-size:13px;color:#555}
.action{cursor:pointer;color:#1b7bbf;margin-right:10px;font-size:14px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.userline{font-weight:700}
.small{color:#6b7280;font-size:13px}
.link{color:var(--blue);cursor:pointer}
.nav{background:#fff;border-radius:12px;padding:12px;position:sticky;top:16px}
.search{width:100%;padding:8px;border-radius:8px;border:1px solid #e2eefb}
</style>
</head>
<body>
<div class="container">
  <div class="left">
    <div class="card">
      <div class="header">
        <div>
          <div style="font-weight:800;font-size:18px">Home</div>
          <div class="small">What's happening?</div>
        </div>
        <div><button class="btn" onclick="window.location='profile.php'">Profile</button></div>
      </div>
 
      <form class="tweetbox" method="post">
        <textarea name="tweet_content" placeholder="Write your tweet (max 280 chars)" maxlength="280" rows="3"></textarea>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
          <div class="small">You are posting as <strong><?php
            $u = $mysqli->prepare("SELECT username FROM users WHERE id=?");
            $u->bind_param('i',$me); $u->execute(); $r=$u->get_result(); $ru=$r->fetch_assoc();
            echo htmlspecialchars($ru['username']);
          ?></strong></div>
          <button class="btn" type="submit">Tweet</button>
        </div>
      </form>
    </div>
 
    <div class="card">
      <div style="font-weight:800;margin-bottom:6px">Latest tweets</div>
      <?php while($row = $res->fetch_assoc()): ?>
        <?php
          // get likes count and whether me liked
          $lt = $mysqli->prepare("SELECT COUNT(*) c FROM likes WHERE tweet_id=?");
          $lt->bind_param('i',$row['id']); $lt->execute(); $lr=$lt->get_result()->fetch_assoc();
          $liked = $mysqli->prepare("SELECT id FROM likes WHERE tweet_id=? AND user_id=?");
          $liked->bind_param('ii',$row['id'],$me); $liked->execute(); $liked = (bool)$liked->get_result()->fetch_assoc();
        ?>
        <div class="tweet">
          <div style="width:48px;height:48px;border-radius:50%;background:#cfe8ff;display:flex;align-items:center;justify-content:center;font-weight:700;color:#034f84">
            <?=strtoupper(htmlspecialchars($row['username'][0]))?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;justify-content:space-between">
              <div>
                <span class="userline"><?=htmlspecialchars($row['display_name'])?></span>
                <span class="small"> @<?=htmlspecialchars($row['username'])?> · <?=timeAgo($row['created_at'])?></span>
              </div>
              <div>
                <?php if($row['user_id']==$me): ?>
                  <span class="link" onclick="window.location='edit_tweet.php?id=<?=$row['id']?>'">Edit</span>
                  &nbsp; <span class="link" onclick="window.location='delete_tweet.php?id=<?=$row['id']?>'">Delete</span>
                <?php else: 
                  // show follow/unfollow
                  $f = $mysqli->prepare("SELECT id FROM followers WHERE follower_id=? AND following_id=?");
                  $f->bind_param('ii',$me,$row['user_id']); $f->execute(); $isf = (bool)$f->get_result()->fetch_assoc();
                  if($isf): ?>
                    <span class="link" onclick="follow(<?=$row['user_id']?>,'unfollow')">Unfollow</span>
                  <?php else: ?>
                    <span class="link" onclick="follow(<?=$row['user_id']?>,'follow')">Follow</span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
            <div style="margin-top:6px;word-wrap:break-word"><?=htmlspecialchars($row['content'])?></div>
            <div style="margin-top:8px">
              <span class="action" onclick="likeTweet(<?=$row['id']?>)"><?= $liked ? '♥' : '♡' ?> <?= $lr['c'] ?></span>
              <span class="action" onclick="window.location='comment.php?id=<?=$row['id']?>'">💬</span>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
 
  <div class="right">
    <div class="nav card">
      <div style="font-weight:700;margin-bottom:8px">Search people</div>
      <input id="search" class="search" placeholder="Search username..." oninput="doSearch()" />
      <div id="results" style="margin-top:8px"></div>
      <div style="margin-top:12px;text-align:center">
        <button class="btn" onclick="window.location='logout.php'">Logout</button>
      </div>
    </div>
    <div class="card">
      <div style="font-weight:700;margin-bottom:8px">Your quick stats</div>
      <?php
         $c = $mysqli->prepare("SELECT (SELECT COUNT(*) FROM tweets WHERE user_id=?) tweets, (SELECT COUNT(*) FROM followers WHERE following_id=?) followers, (SELECT COUNT(*) FROM followers WHERE follower_id=?) following");
         $c->bind_param('iii',$me,$me,$me); $c->execute(); $cr = $c->get_result()->fetch_row();
      ?>
      <div style="display:flex;justify-content:space-around">
        <div style="text-align:center"><div style="font-weight:700"><?=$cr[0]?></div><div class="small">Tweets</div></div>
        <div style="text-align:center"><div style="font-weight:700"><?=$cr[1]?></div><div class="small">Followers</div></div>
        <div style="text-align:center"><div style="font-weight:700"><?=$cr[2]?></div><div class="small">Following</div></div>
      </div>
    </div>
  </div>
</div>
 
<script>
function likeTweet(id){
  fetch('like.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'tweet_id='+encodeURIComponent(id)
  }).then(()=>{ location.reload(); });
}
function follow(target,action){
  fetch('follow.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'target='+encodeURIComponent(target)+'&action='+encodeURIComponent(action)
  }).then(()=>{ location.reload(); });
}
function doSearch(){
  var q=document.getElementById('search').value;
  if(q.length<2){ document.getElementById('results').innerHTML=''; return; }
  fetch('search.php?q='+encodeURIComponent(q)).then(r=>r.json()).then(data=>{
    var html='';
    data.forEach(function(u){
      html += '<div style="padding:8px;border-bottom:1px solid #f1f5f9"><strong>'+u.display_name+'</strong> @'+u.username+' <span style="float:right"><a href="profile.php?id='+u.id+'">View</a></span></div>';
    });
    document.getElementById('results').innerHTML = html;
  });
}
</script>
</body>
</html>
