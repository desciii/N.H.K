<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
  }

  $name = trim($_POST['name']);
  $description = trim($_POST['description']);
  $created_by = $_SESSION['user_id'];
  $created_at = date('Y-m-d H:i:s');

  try {
    $pdo->beginTransaction();

    // Insert into teams table
    $stmt = $pdo->prepare("INSERT INTO teams (name, description, created_by, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $created_by, $created_at]);
    $team_id = $pdo->lastInsertId();

    // Add the creator as a team leader
    $stmt = $pdo->prepare("INSERT INTO team_members (user_id, team_id, role) VALUES (?, ?, ?)");
    $stmt->execute([$created_by, $team_id, 'leader']);

    $pdo->commit();

    // Redirect to the view_team page
    header("Location: view_team.php?id=" . $team_id);
    exit;

  } catch (Exception $e) {
    $pdo->rollBack();
    die("Error creating team: " . $e->getMessage());
  }
}
?>
