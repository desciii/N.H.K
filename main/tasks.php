<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\tasks.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all tasks assigned to the user
$stmt = $pdo->prepare("
  SELECT t.id, t.title, t.due_date, t.status, t.team_id, teams.name AS team_name
  FROM tasks t
  JOIN task_assignments ta ON t.id = ta.task_id
  JOIN teams ON t.team_id = teams.id
  WHERE ta.user_id = ?
  ORDER BY t.due_date ASC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Tasks</title>
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
    .tasks-section {
      background-color: #fff;
      padding: 24px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      margin-bottom: 30px;
      margin-left: 10px;
    }
    .tasks-section h2 {
      font-size: 20px;
      margin-bottom: 15px;
    }
    .task-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .task-item {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
    }
    .task-title {
      font-weight: bold;
      color: #333;
    }
    .task-meta {
      font-size: 13px;
      color: #888;
    }
    .badge {
      font-size: 13px;
      padding: 4px 10px;
      border-radius: 12px;
    }
    .badge.done {
      background: #4caf50;
      color: #fff;
    }
    .badge.in_progress {
      background: #ff9800;
      color: #fff;
    }
    .badge.pending {
      background: #2196f3;
      color: #fff;
    }
    .badge.overdue {
      background: #dc3545;
      color: #fff;
    }
    a {
      text-decoration: none;
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
      <div class="tasks-section">
        <h2><i class="fas fa-tasks"></i> My Tasks</h2>
        <?php
            $pending = [];
            $in_progress = [];
            $done = [];
            foreach ($tasks as $task) {
            if ($task['status'] === 'pending') $pending[] = $task;
            elseif ($task['status'] === 'in_progress') $in_progress[] = $task;
            elseif ($task['status'] === 'done') $done[] = $task;
            }
        ?>

        <?php if (count($pending)): ?>
            <h4>Pending</h4>
            <ul class="task-list">
            <?php foreach ($pending as $task): ?>
                <li class="task-item">
                <div>
                    <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                    <div class="task-meta">
                    <?= htmlspecialchars($task['team_name']) ?> &middot;
                    Due: <?= htmlspecialchars(date('M j, Y', strtotime($task['due_date']))) ?>
                    </div>
                </div>
                <span class="badge pending">Pending</span>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (count($in_progress)): ?>
            <h4>In Progress</h4>
            <ul class="task-list">
            <?php foreach ($in_progress as $task): ?>
                <li class="task-item">
                <div>
                    <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                    <div class="task-meta">
                    <?= htmlspecialchars($task['team_name']) ?> &middot;
                    Due: <?= htmlspecialchars(date('M j, Y', strtotime($task['due_date']))) ?>
                    </div>
                </div>
                <span class="badge in_progress">In Progress</span>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (count($done)): ?>
            <h4>Completed</h4>
            <ul class="task-list">
            <?php foreach ($done as $task): ?>
                <li class="task-item">
                <div>
                    <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                    <div class="task-meta">
                    <?= htmlspecialchars($task['team_name']) ?> &middot;
                    Due: <?= htmlspecialchars(date('M j, Y', strtotime($task['due_date']))) ?>
                    </div>
                </div>
                <span class="badge done">Done</span>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!count($pending) && !count($in_progress) && !count($done)): ?>
            <p>No tasks assigned to you.</p>
        <?php endif; ?>
        </div>
    </div>
  </div>
</body>
</html>