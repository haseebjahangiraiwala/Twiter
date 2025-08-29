<?php
// search.php
session_start();
 
// ✅ Database connection
$host = "localhost";
$user = "uulevslgtrnau";   // your DB username
$pass = "ld4dy42tkorz";    // your DB password
$db   = "dbcxge8bd2chzb";  // your DB name
 
$conn = new mysqli($host, $user, $pass, $db);
 
// Check DB connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
 
// ✅ Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f8fa;
      padding: 20px;
    }
    .result-box {
      background: #fff;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .username {
      font-weight: bold;
      color: #1da1f2;
    }
    .tweet {
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <h2>🔍 Search Results for: "<?php echo htmlspecialchars($query); ?>"</h2>
  <hr>
 
<?php
if ($query != "") {
    // ✅ Search tweets
    $sql = "SELECT tweets.content, tweets.created_at, users.username 
            FROM tweets 
            JOIN users ON tweets.user_id = users.id 
            WHERE tweets.content LIKE ? 
            ORDER BY tweets.created_at DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
 
    if ($result->num_rows > 0) {
        echo "<h3>Tweets:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='result-box'>";
            echo "<div class='username'>@" . htmlspecialchars($row['username']) . "</div>";
            echo "<div class='tweet'>" . htmlspecialchars($row['content']) . "</div>";
            echo "<small>" . $row['created_at'] . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p>No tweets found.</p>";
    }
 
    // ✅ Search users
    $sql2 = "SELECT id, username FROM users WHERE username LIKE ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $searchTerm);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
 
    if ($result2->num_rows > 0) {
        echo "<h3>Users:</h3>";
        while ($row2 = $result2->fetch_assoc()) {
 
