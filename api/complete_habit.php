<?php
// api/complete_habit.php
// Subject: Web Technology - Streak Logic, Transactions

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
$habit_id = $_POST['habit_id'] ?? 0;
$log_date = $_POST['log_date'] ?? date('Y-m-d');
$completed = filter_var($_POST['completed'] ?? true, FILTER_VALIDATE_BOOLEAN);

// Validate habit belongs to user
try {
    // Check habit ownership
    $check_query = "SELECT habit_id FROM habits WHERE habit_id = :habit_id AND user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':habit_id', $habit_id);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();

    if($check_stmt->rowCount() === 0) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Habit not found']);
        exit();
    }

    // Begin transaction
    $db->beginTransaction();

    // Insert or update log (use separate param for UPDATE for PDO compatibility)
    $log_query = "INSERT INTO habit_logs (habit_id, log_date, completed) 
                  VALUES (:habit_id, :log_date, :completed)
                  ON DUPLICATE KEY UPDATE completed = :completed_upd";
    
    $log_stmt = $db->prepare($log_query);
    $log_stmt->bindParam(':habit_id', $habit_id);
    $log_stmt->bindParam(':log_date', $log_date);
    $log_stmt->bindParam(':completed', $completed, PDO::PARAM_BOOL);
    $log_stmt->bindParam(':completed_upd', $completed, PDO::PARAM_BOOL);
    $log_stmt->execute();

    // Calculate and update streak
    $streak_data = calculateStreak($db, $habit_id, $log_date, $completed);
    
    // Update streaks table
    $streak_query = "UPDATE streaks SET 
                      current_streak = :current_streak,
                      longest_streak = :longest_streak,
                      last_completed = :last_completed
                      WHERE habit_id = :habit_id";
    
    $streak_stmt = $db->prepare($streak_query);
    $streak_stmt->bindParam(':current_streak', $streak_data['current_streak']);
    $streak_stmt->bindParam(':longest_streak', $streak_data['longest_streak']);
    $streak_stmt->bindParam(':last_completed', $streak_data['last_completed']);
    $streak_stmt->bindParam(':habit_id', $habit_id);
    $streak_stmt->execute();

    // Commit transaction
    $db->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'streak' => $streak_data,
        'message' => $completed ? 'Habit completed!' : 'Habit uncompleted'
    ]);

} catch(PDOException $e) {
    $db->rollBack();
    error_log("Complete habit error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update habit']);
}

/**
 * Calculate streak based on habit logs
 * @param PDO $db Database connection
 * @param int $habit_id Habit ID
 * @param string $current_date Current date being updated
 * @param bool $completed Whether completed or uncompleted
 * @return array Streak data
 */
function calculateStreak($db, $habit_id, $current_date, $completed) {
    // Get all completed logs for this habit, ordered by date
    $query = "SELECT log_date FROM habit_logs 
              WHERE habit_id = :habit_id AND completed = 1 
              ORDER BY log_date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':habit_id', $habit_id);
    $stmt->execute();
    
    $completed_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $current_streak = 0;
    $longest_streak = 0;
    $last_completed = null;
    
    if($completed) {
        // Add current date to the list if completing
        array_unshift($completed_dates, $current_date);
        // Remove duplicates (shouldn't happen but just in case)
        $completed_dates = array_unique($completed_dates);
    } else {
        // Remove current date if uncompleting
        $completed_dates = array_diff($completed_dates, [$current_date]);
    }
    
    // Sort dates in descending order
    rsort($completed_dates);
    
    if(!empty($completed_dates)) {
        $last_completed = $completed_dates[0];
        
        // Calculate current streak
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Check if last completion is today or yesterday to maintain streak
        if(in_array($today, $completed_dates) || in_array($yesterday, $completed_dates)) {
            $current_streak = 1;
            $prev_date = $completed_dates[0];
            
            for($i = 1; $i < count($completed_dates); $i++) {
                $current_date = $completed_dates[$i];
                $expected_prev = date('Y-m-d', strtotime($prev_date . ' -1 day'));
                
                if($current_date == $expected_prev) {
                    $current_streak++;
                    $prev_date = $current_date;
                } else {
                    break;
                }
            }
        }
        
        // Calculate longest streak (scan all dates)
        $longest_streak = 1;
        $streak_length = 1;
        $prev_date = $completed_dates[0];
        
        for($i = 1; $i < count($completed_dates); $i++) {
            $current_date = $completed_dates[$i];
            $expected_prev = date('Y-m-d', strtotime($prev_date . ' -1 day'));
            
            if($current_date == $expected_prev) {
                $streak_length++;
                $longest_streak = max($longest_streak, $streak_length);
            } else {
                $streak_length = 1;
            }
            
            $prev_date = $current_date;
        }
    }
    
    return [
        'current_streak' => $current_streak,
        'longest_streak' => $longest_streak,
        'last_completed' => $last_completed
    ];
}
?>