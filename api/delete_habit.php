<?php
// api/delete_habit.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    jsonResponse(false, 'Not authenticated');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse(false, 'Method not allowed');
}

$user_id = $_SESSION['user_id'];
$habit_id = $_POST['habit_id'] ?? 0;

try {
    // Verify habit belongs to user
    $check_query = "SELECT habit_id FROM habits WHERE habit_id = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$habit_id, $user_id]);
    
    if ($check_stmt->rowCount() === 0) {
        http_response_code(404);
        jsonResponse(false, 'Habit not found');
    }
    
    // Soft delete (or hard delete - your choice)
    $delete_query = "UPDATE habits SET is_active = 0 WHERE habit_id = ?";
    // Or for hard delete:
    // $delete_query = "DELETE FROM habits WHERE habit_id = ?";
    
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->execute([$habit_id]);
    
    jsonResponse(true, 'Habit deleted successfully');
    
} catch (PDOException $e) {
    error_log("Delete habit error: " . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, 'Failed to delete habit');
}
?>