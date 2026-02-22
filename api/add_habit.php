<?php
// api/add_habit.php
// Subject: Web Technology - CRUD Operations

session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$habit_name = trim($_POST['habit_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$color = $_POST['color'] ?? '#4CAF50';

// Validate input
if(empty($habit_name)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Habit name is required']);
    exit();
}

if(strlen($habit_name) > 100) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Habit name too long']);
    exit();
}

try {
    // Begin transaction
    $db->beginTransaction();

    // Insert habit
    $query = "INSERT INTO habits (user_id, habit_name, description, color) 
              VALUES (:user_id, :habit_name, :description, :color)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':habit_name', $habit_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':color', $color);
    
    $stmt->execute();
    $habit_id = $db->lastInsertId();

    // Create streak record
    $streak_query = "INSERT INTO streaks (habit_id) VALUES (:habit_id)";
    $streak_stmt = $db->prepare($streak_query);
    $streak_stmt->bindParam(':habit_id', $habit_id);
    $streak_stmt->execute();

    // Commit transaction
    $db->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'habit_id' => $habit_id,
        'message' => 'Habit created successfully'
    ]);

} catch(PDOException $e) {
    // Rollback on error
    $db->rollBack();
    error_log("Add habit error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to create habit']);
}
?>