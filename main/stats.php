<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

$total = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
$total->execute([$user_id]);
$totalTasks = $total->fetchColumn();

$upcoming = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND due_date >= CURDATE()");
$upcoming->execute([$user_id]);
$upcomingTasks = $upcoming->fetchColumn();

$completed = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'done'");
$completed->execute([$user_id]);
$completedTasks = $completed->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link rel="icon" href="../assets/logo.png" type="image/png" />
  <style>
    body {
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 0;
      background-color: #edede9;
    }
    nav {
      background-color: #d6ccc2;
      padding: 15px;
      text-align: center;
      font-size: 16px;
    }
    .dashboard {
      max-width: 960px;
      margin: 30px auto;
      padding: 0 20px;
    }
    .welcome {
      background-color: #e3d5ca;
      padding: 20px;
      border-radius: 10px;
      font-size: 18px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }
    .card {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }
    .card h3 {
      margin-top: 0;
      font-size: 18px;
      color: #333;
    }
    .card p {
      color: #555;
      font-size: 15px;
    }
  </style>
</head>
<body>

  <!-- Navigation Bar -->
    <?php include __DIR__ . '/../views/nav.php'; ?>

  <div class="dashboard">
    <div class="welcome">
      <h2>Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Student') ?>!</h2>
      <p>Here’s what’s going on with your tasks today.</p>
    </div>

  <div class="cards">
    <div class="card">
      <h3>Total Tasks</h3>
      <p><?= $totalTasks ?> tasks created</p>
    </div>
    <div class="card">
      <h3>Upcoming Deadlines</h3>
      <p><?= $upcomingTasks ?> due this week</p>
    </div>
    <div class="card">
      <h3>Completed</h3>
      <p><?= $completedTasks ?> tasks done</p>
    </div>
  </div>

  </div>

</body>
</html>
