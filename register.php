<?php
require 'db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $display = trim($_POST['display_name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
 
    if (!$username || !$display || !$email || !$pass) {
        $err = "Please fill all fields.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, display_name, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $display, $email, $hash);
        if ($stmt->execute()) {
            echo "<script>localStorage.setItem('msg','Registration successful. Please login.'); window.location='login.php';</script>";
            exit;
        } else {
            if ($mysqli->errno === 1062) $err = "Username or email already taken.";
            else $err = "Error: " . $mysqli->error;
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register - MiniTwitter</title>
<style>
/* internal CSS: clean, modern */
body{font-family:Segoe UI,Roboto,Arial;background:#f2f5f7;margin:0;padding:0;display:flex;align-items:center;justify-content:center;height:100vh}
.card{background:#fff;padding:28px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,0.08);width:380px}
h2{margin:0 0 10px;font-size:20px;color:#111}
.input{width:100%;padding:10px;margin:8px 0;border:1px solid #e1e6ea;border-radius:6px}
.btn{width:100%;padding:10px;background:#1da1f2;border:none;color:#fff;border-radius:6px;cursor:pointer;font-weight:600}
.err{color:#b00020;margin:8px 0}
.small{font-size:13px;color:#555;text-align:center;margin-top:12px}
.link{color:#1da1f2;cursor:pointer}
</style>
</head>
<body>
<div class="card">
  <h2>Create account</h2>
  <?php if($err): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?>
  <form method="post" onsubmit="return validate();">
    <input class="input" name="username" id="username" placeholder="username (no spaces)" />
    <input class="input" name="display_name" id="display_name" placeholder="Display name" />
    <input class="input" name="email" id="email" placeholder="Email" />
    <input class="input" name="password" id="password" type="password" placeholder="Password (min 6)" />
    <button class="btn" type="submit">Sign up</button>
  </form>
  <div class="small">Already have account? <span class="link" onclick="window.location='login.php'">Login</span></div>
</div>
 
<script>
function validate(){
  var u=document.getElementById('username').value.trim();
  var p=document.getElementById('password').value;
  if(!u || !p){ alert('Fill fields'); return false; }
  if(p.length<6){ alert('Password too short'); return false; }
  if(/\s/.test(u)){ alert('Username cannot contain spaces'); return false; }
  return true;
}
</script>
</body>
</html>
 
