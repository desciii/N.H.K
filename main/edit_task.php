<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\edit_task.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $assigned_users = $_POST['assigned_users'] ?? [];

    // Start transaction
    $pdo->beginTransaction();

    // Update task
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, due_date = ?, status = ? 
        WHERE id = ?
    ");
    $stmt->execute([$title, $description, $due_date, $status, $task_id]);

    // Delete old assignments
    $stmt = $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?");
    $stmt->execute([$task_id]);

    // Add new assignments
    if (!empty($assigned_users)) {
        $stmt = $pdo->prepare("
            INSERT INTO task_assignments (task_id, user_id)
            SELECT ?, id FROM users WHERE username IN (" . str_repeat('?,', count($assigned_users) - 1) . "?)
        ");
        $params = array_merge([$task_id], $assigned_users);
        $stmt->execute($params);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}