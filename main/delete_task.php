<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/debug.log');

session_start();
require_once 'db.php';

header('Content-Type: application/json');

try {
    $task_id = $_POST['task_id'] ?? null;

    if (!$task_id) {
        throw new Exception('Task ID is required');
    }

    // Delete task assignments first
    $stmt = $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?");
    $stmt->execute([$task_id]);

    // Delete the task itself
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Database error in delete_task.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    error_log("General error in delete_task.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
