<?php
require 'db.php';
session_unset();
session_destroy();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Logged out</title></head><body>
<script>localStorage.setItem('msg','Logged out'); window.location='login.php';</script>
</body></html>
 
