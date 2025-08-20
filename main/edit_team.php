<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\edit_team.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check authentication and input
if (!isset($_SESSION['user_id']) || !isset($_POST['team_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$team_id = $_POST['team_id'] ?? '';
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate input
if ($name === '') {
    echo json_encode(['success' => false, 'error' => 'Team name cannot be empty.']);
    exit;
}

// Fetch team and check ownership
$stmt = $pdo->prepare("SELECT created_by FROM teams WHERE id = ?");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

if (!$team) {
    echo json_encode(['success' => false, 'error' => 'Team not found.']);
    exit;
}
if ($team['created_by'] != $user_id) {
    echo json_encode(['success' => false, 'error' => 'You are not authorized to edit this team.']);
    exit;
}

// Update team
$stmt = $pdo->prepare("UPDATE teams SET name = ?, description = ? WHERE id = ?");
if ($stmt->execute([$name, $description, $team_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update team.']);
}
exit;