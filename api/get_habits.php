<?php
// api/get_habits.php
// Subject: Web Technology - REST API, JSON

session_start();
require_once '../config/database.php';

// Check authentication
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get all habits for user with streak data
    $query = "SELECT 
                h.*,
                COALESCE(s.current_streak, 0) as current_streak,
                COALESCE(s.longest_streak, 0) as longest_streak,
                s.last_completed
              FROM habits h
              LEFT JOIN streaks s ON h.habit_id = s.habit_id
              WHERE h.user_id = :user_id AND h.is_active = 1
              ORDER BY h.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $habits = $stmt->fetchAll();

    // Get logs for last 7 days for each habit
    foreach($habits as &$habit) {
        $log_query = "SELECT log_date, completed 
                      FROM habit_logs 
                      WHERE habit_id = :habit_id 
                      AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      ORDER BY log_date DESC";
        
        $log_stmt = $db->prepare($log_query);
        $log_stmt->bindParam(':habit_id', $habit['habit_id']);
        $log_stmt->execute();
        
        $habit['logs'] = $log_stmt->fetchAll();
    }
    unset($habit);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'habits' => $habits
    ]);

} catch(PDOException $e) {
    error_log("Get habits error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>