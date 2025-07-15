<?php
require_once 'db.php';
session_start();

$team_id = $_POST['team_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$due_date = $_POST['due_date'];
$assigned_usernames = $_POST['assigned_users'] ?? [];

// Insert task
$stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, team_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$title, $description, $due_date, $team_id]);
$task_id = $pdo->lastInsertId();

// Assign users (many-to-many)
if (!empty($assigned_usernames)) {
  $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $assignStmt = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");

  foreach ($assigned_usernames as $username) {
    $userStmt->execute([$username]);
    if ($user = $userStmt->fetch()) {
      $assignStmt->execute([$task_id, $user['id']]);
    }
  }
}

header("Location: view_team.php?id=" . $team_id);
exit;
