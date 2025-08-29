<?php
require 'db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass = $_POST['password'];
    if (!$username || !$pass) $err = "Fill fields.";
    else {
        $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($pass, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                echo "<script>window.location='index.php';</script>";
                exit;
            } else $err = "Invalid credentials.";
        } else $err = "Invalid credentials.";
    }
}
$msg = '';
if(isset($_SESSION['flash'])) { $msg = $_SESSION['flash']; unset($_SESSION['flash']); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - MiniTwitter</title>
<style>
body{font-family:Segoe UI,Roboto,Arial;background:#f7fbfd;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.card{background:#fff;padding:28px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,0.06);width:360px}
h2{margin:0 0 8px;color:#111}
.input{width:100%;padding:10px;margin:8px 0;border:1px solid #e6eef5;border-radius:6px}
.btn{width:100%;padding:10px;background:#1da1f2;border:none;color:#fff;border-radius:6px;cursor:pointer;font-weight:600}
.err{color:#b00020;margin:8px 0}
.small{font-size:13px;color:#555;text-align:center;margin-top:12px}
.link{color:#1da1f2;cursor:pointer}
</style>
</head>
<body>
<div class="card">
  <h2>Welcome back</h2>
  <?php if($err): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?>
  <?php if($msg): ?><div style="color:green;margin-bottom:10px"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <form method="post">
    <input class="input" name="username" placeholder="username or email" />
    <input class="input" name="password" type="password" placeholder="password" />
    <button class="btn" type="submit">Login</button>
  </form>
  <div class="small">No account? <span class="link" onclick="window.location='register.php'">Sign up</span></div>
</div>
</body>
</html>
 
