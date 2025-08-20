<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\leave_team.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['team_id'])) {
    header("Location: team_dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$team_id = $_POST['team_id'];

// Remove from team_members
$stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
$stmt->execute([$team_id, $user_id]);

header("Location: team_dashboard.php");
exit;