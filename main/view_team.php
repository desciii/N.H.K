<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id'])) {
  die("Team ID is required.");
}

$team_id = $_GET['id'];

// Get team info
$stmt = $pdo->prepare("SELECT t.*, u.username AS creator FROM teams t JOIN users u ON t.created_by = u.id WHERE t.id = ?");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

if (!$team) {
  die("Team not found.");
}

// Get team members
$membersStmt = $pdo->prepare("
  SELECT u.username, tm.role 
  FROM team_members tm 
  JOIN users u ON tm.user_id = u.id 
  WHERE tm.team_id = ?
");
$membersStmt->execute([$team_id]);
$members = $membersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($team['name']) ?> - Team</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

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
      margin-top: 60px; /* height of nav */
    }

    .sidebar {
      width: 220px;
      background-color: #d6ccc2;
      padding: 20px;
      height: calc(100vh - 60px);
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    .sidebar h3 {
      font-size: 18px;
      margin-bottom: 15px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar li {
      margin-bottom: 10px;
    }

    .sidebar a {
      text-decoration: none;
      color: #333;
      transition: color 0.2s;
    }

    .sidebar a:hover {
      color: #4caf50;
    }

    .main {
      flex: 1;
      padding: 30px;
    }

    .team-header {
      background-color: #e3d5ca;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .team-header h1 {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .team-header p {
      color: #555;
      margin-bottom: 5px;
    }

    .team-members {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .team-members h2 {
      font-size: 20px;
      margin-bottom: 15px;
    }

    .member {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
    }

    .nav-left a {
      text-decoration: none;
      display: flex;
      align-items: center;
      color: #333;
    }

    .nav-left img {
      height: 30px;
      margin-right: 10px;
    }

    .nav-title {
      font-weight: bold;
      font-size: 18px;
    }

    a {
      text-decoration: none;
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../views/nav.php'; ?>

  <!-- Sidebar + Main Content -->
  <div class="layout">
    <!-- Sidebar -->
    <div class="sidebar">
      <h3>Your Teams</h3>
      <ul>
        <?php
        $stmt = $pdo->prepare("
          SELECT DISTINCT t.id, t.name
          FROM teams t
          LEFT JOIN team_members tm ON t.id = tm.team_id
          WHERE t.created_by = ? OR tm.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $teams = $stmt->fetchAll();

        foreach ($teams as $t): ?>
          <li><a href="view_team.php?id=<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></a></li>
        <?php endforeach; ?>
        <?php if (empty($teams)): ?>
          <li>No teams found</li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Main -->
    <div class="main">
      <div class="team-header">
        <h1><?= htmlspecialchars($team['name']) ?></h1>
        <p><?= htmlspecialchars($team['description']) ?></p>
        <p class="text-sm">Created by <strong><?= htmlspecialchars($team['creator']) ?></strong> on <?= date('F j, Y', strtotime($team['created_at'])) ?></p>
      </div>

      <div class="team-members">
        <h2>Team Members</h2>
        <?php foreach ($members as $member): ?>
          <div class="member">
            <span><?= htmlspecialchars($member['username']) ?></span>
            <span style="font-size: 14px; color: #888;"><?= htmlspecialchars($member['role']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</body>
</html>
