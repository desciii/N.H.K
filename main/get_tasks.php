<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['team_id'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$team_id = $_GET['team_id'];

$stmt = $pdo->prepare("SELECT id, title, due_date FROM tasks WHERE team_id = ?");
$stmt->execute([$team_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($tasks as $task) {
  $events[] = [
    'id' => $task['id'],
    'title' => $task['title'],
    'start' => $task['due_date'], // Must be in YYYY-MM-DD format
    'allDay' => true
  ];
}

header('Content-Type: application/json');
echo json_encode($events);
