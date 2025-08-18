<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $task_id = $_POST['task_id'];
  $title = $_POST['title'];
  $description = $_POST['description'];
  $due_date = $_POST['due_date'];
  $status = $_POST['status'];
  $assigned_usernames = $_POST['assigned_users']; // array

  // Convert usernames to user_ids
  $user_ids = [];
  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  foreach ($assigned_usernames as $username) {
    $stmt->execute([$username]);
    if ($user = $stmt->fetch()) {
      $user_ids[] = $user['id'];
    }
  }

  // Update the task table
  $updateTask = $pdo->prepare("UPDATE tasks SET title=?, description=?, due_date=?, status=? WHERE id=?");
  $updateTask->execute([$title, $description, $due_date, $status, $task_id]);

  // Optional: clear + reassign task assignments if you have a separate table

  header("Location: view_team.php?id=" . $_POST['team_id']);
  exit;
}
