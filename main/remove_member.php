<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'error' => 'Not authenticated']);
  exit;
}

if (!isset($_POST['team_id']) || !isset($_POST['user_id'])) {
  echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
  exit;
}

$team_id = $_POST['team_id'];
$user_id_to_remove = $_POST['user_id'];
$current_user_id = $_SESSION['user_id'];

try {
  // Check if current user is the team creator
  $stmt = $pdo->prepare("SELECT created_by FROM teams WHERE id = ?");
  $stmt->execute([$team_id]);
  $team = $stmt->fetch();

  if (!$team) {
    echo json_encode(['success' => false, 'error' => 'Team not found']);
    exit;
  }

  if ($team['created_by'] != $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Only the team creator can remove members']);
    exit;
  }

  // Prevent creator from removing themselves
  if ($user_id_to_remove == $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'You cannot remove yourself from the team']);
    exit;
  }

  // Remove the member from the team
  $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
  $stmt->execute([$team_id, $user_id_to_remove]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Member removed successfully']);
  } else {
    echo json_encode(['success' => false, 'error' => 'Member not found in team']);
  }

} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>