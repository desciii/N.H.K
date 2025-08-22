<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\profile.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, full_name, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  echo "<p>User not found.</p>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #edede9;
      color: #333;
    }
    nav {
      background-color: #d6ccc2;
      padding: 15px 30px;
      font-size: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      z-index: 100;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .layout {
      display: flex;
      margin-top: 60px;
      height: calc(100vh - 60px);
      overflow: hidden;
    }
    .sidebar {
      width: 220px;
      background-color: #d6ccc2;
      padding: 20px;
      height: calc(100vh - 60px);
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
      overflow-y: auto; 
      position: sticky;
      top: 60px;
      flex-shrink: 0;
      text-decoration: none;
    }
    .main {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
      height: calc(100vh - 60px);
      margin-left: 200px;
    }
    .profile-section {
      background-color: #fff;
      padding: 32px 28px;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.07);
      max-width: 500px;
      margin: 40px auto;
    }
    .profile-section h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 24px;
    }
    .profile-info {
      font-size: 16px;
      margin-bottom: 12px;
    }
    .profile-label {
      font-weight: 500;
      color: #888;
      margin-right: 8px;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../views/nav.php'; ?>
  <div class="layout">
    <div class="sidebar">
      <?php include __DIR__ . '/../views/teams.php'; ?>
    </div>
    <div class="main">
      <div class="profile-section">
        <h2><i class="fas fa-user"></i> My Profile</h2>
        <div class="profile-info">
          <span class="profile-label">Full Name:</span>
          <?= htmlspecialchars($user['full_name']) ?>
        </div>
        <div class="profile-info">
          <span class="profile-label">Username:</span>
          <?= htmlspecialchars($user['username']) ?>
        </div>
        <div class="profile-info">
          <span class="profile-label">Email:</span>
          <?= htmlspecialchars($user['email']) ?>
        </div>
        <div class="profile-info">
          <span class="profile-label">Member Since:</span>
          <?= date('F j, Y', strtotime($user['created_at'])) ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>