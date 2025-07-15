<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_POST['team_id'], $_POST['title'], $_POST['due_date'])) {
  die("Missing fields.");
}

$team_id = $_POST['team_id'];
$title = $_POST['title'];
$description = $_POST['description'] ?? '';
$due_date = $_POST['due_date'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, status, assigned_to, team_id) VALUES (?, ?, ?, 'pending', ?, ?)");
$stmt->execute([$title, $description, $due_date, $user_id, $team_id]);

header("Location: view_team.php?id=" . $team_id);
exit;
