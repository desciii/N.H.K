<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_POST['team_id'], $_POST['username'], $_POST['role'])) {
  die("Incomplete form data.");
}

$team_id = intval($_POST['team_id']);
$username = trim($_POST['username']);
$role = $_POST['role'];

$validRoles = ['member', 'admin'];
if (!in_array($role, $validRoles)) {
  die("Invalid role selected.");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
  die("User not found.");
}

$user_id = $user['id'];

$stmt = $pdo->prepare("SELECT * FROM team_members WHERE user_id = ? AND team_id = ?");
$stmt->execute([$user_id, $team_id]);
$existing = $stmt->fetch();

if ($existing) {
  die("User is already in the team.");
}

$insert = $pdo->prepare("INSERT INTO team_members (user_id, team_id, role) VALUES (?, ?, ?)");
$insert->execute([$user_id, $team_id, $role]);

header("Location: view_team.php?id=" . $team_id);
exit;
