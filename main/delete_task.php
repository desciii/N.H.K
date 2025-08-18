<?php
session_start();
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check if task_id is provided
if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
    echo json_encode(['success' => false, 'error' => 'Task ID is required']);
    exit;
}

$task_id = $_POST['task_id'];
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if user has permission to delete this task (optional - you might want to verify team membership)
    $checkStmt = $pdo->prepare("
        SELECT t.id, tm.team_id 
        FROM tasks t 
        JOIN team_members tm ON t.team_id = tm.team_id 
        WHERE t.id = ? AND tm.user_id = ?
    ");
    $checkStmt->execute([$task_id, $user_id]);
    
    if (!$checkStmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Task not found or access denied']);
        exit;
    }
    
    // Delete task assignments first (if you have a task_assignments table)
    $deleteAssignmentsStmt = $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?");
    $deleteAssignmentsStmt->execute([$task_id]);
    
    // Delete the task
    $deleteTaskStmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $result = $deleteTaskStmt->execute([$task_id]);
    
    if ($result && $deleteTaskStmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => true]);
        exit; // Important: exit here to prevent any further output
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Task not found or already deleted']);
        exit;
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// This should never be reached, but just in case:
echo json_encode(['success' => false, 'error' => 'Unexpected error']);
exit;
?>