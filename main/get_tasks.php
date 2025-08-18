<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/debug.log');

session_start();
require_once 'db.php';

error_log("Starting get_tasks.php");

try {
    $team_id = $_GET['team_id'] ?? null;
    
    if (!$team_id) {
        throw new Exception('Team ID is required');
    }

    // Query matching your database schema
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.title,
            t.description,
            t.due_date,
            t.status,
            t.team_id,
            GROUP_CONCAT(u.username) as assigned_users
        FROM tasks t
        LEFT JOIN task_assignments ta ON t.id = ta.task_id
        LEFT JOIN users u ON ta.user_id = u.id
        WHERE t.team_id = ?
        GROUP BY t.id, t.title, t.description, t.due_date, t.status, t.team_id
    ");
    
    $stmt->execute([$team_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($tasks) . " tasks for team_id: " . $team_id);

    $events = [];
    foreach ($tasks as $task) {
        $event = [
            'id' => $task['id'],
            'title' => $task['title'],
            'start' => $task['due_date'],
            'allDay' => true,
            'extendedProps' => [
                'description' => $task['description'],
                'status' => $task['status'],
                'assigned_users' => $task['assigned_users'] ? explode(',', $task['assigned_users']) : []
            ]
        ];

        // Set color based on status from your enum('pending','in_progress','done')
        $event['backgroundColor'] = match($task['status']) {
            'done' => '#4caf50',
            'in_progress' => '#ff9800',
            default => '#f44336'
        };

        $events[] = $event;
    }

    header('Content-Type: application/json');
    echo json_encode($events);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}