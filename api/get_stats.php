<?php
// api/get_stats.php
// Subject: DBMS - Data Aggregation
// Session must be started first so auth works when called via fetch() with credentials.
// Auth key must match login.php: $_SESSION['user_id']

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fixed 7 dates for chart labels: Sun through Sat (Chart.js will show weekday names)
    // Reference week (2024-01-07 = Sunday) so new Date(date) parses and toLocaleDateString('weekday') gives Sun..Sat
    $dates = ['2024-01-07', '2024-01-08', '2024-01-09', '2024-01-10', '2024-01-11', '2024-01-12', '2024-01-13'];

    // Get user's active habits
    $habit_query = "SELECT habit_id, habit_name, color
                    FROM habits
                    WHERE user_id = :user_id AND is_active = 1
                    ORDER BY habit_name";
    $habit_stmt = $db->prepare($habit_query);
    $habit_stmt->bindParam(':user_id', $user_id);
    $habit_stmt->execute();
    $habits = $habit_stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = [
        'dates' => $dates,
        'habits' => []
    ];

    // For each habit: group habit_logs by day of week (1=Sun .. 7=Sat), no date range filter
    // Returns 7 numbers (Sun to Sat): 1 if completed at least once on that weekday, 0 otherwise
    foreach ($habits as $habit) {
        $daily_completions = [0, 0, 0, 0, 0, 0, 0]; // Sun=0 .. Sat=6

        $dow_query = "SELECT DAYOFWEEK(log_date) AS dow
                      FROM habit_logs
                      WHERE habit_id = :habit_id AND completed = 1
                      GROUP BY DAYOFWEEK(log_date)";
        $dow_stmt = $db->prepare($dow_query);
        $dow_stmt->bindParam(':habit_id', $habit['habit_id']);
        $dow_stmt->execute();

        while ($row = $dow_stmt->fetch(PDO::FETCH_ASSOC)) {
            $dow = (int) $row['dow']; // 1=Sunday .. 7=Saturday
            $index = $dow - 1;         // 0=Sunday .. 6=Saturday
            if ($index >= 0 && $index < 7) {
                $daily_completions[$index] = 1;
            }
        }

        $stats['habits'][] = [
            'name' => $habit['habit_name'],
            'color' => $habit['color'] ?? '#4CAF50',
            'daily_completions' => $daily_completions
        ];
    }

    // Overall statistics (all time, no date filter)
    $overall_query = "SELECT
                        COUNT(DISTINCT h.habit_id) AS total_habits,
                        COALESCE(SUM(CASE WHEN l.completed = 1 THEN 1 ELSE 0 END), 0) AS total_completions,
                        COUNT(DISTINCT l.log_date) AS active_days
                      FROM habits h
                      LEFT JOIN habit_logs l ON h.habit_id = l.habit_id
                      WHERE h.user_id = :user_id AND h.is_active = 1";
    $overall_stmt = $db->prepare($overall_query);
    $overall_stmt->bindParam(':user_id', $user_id);
    $overall_stmt->execute();
    $stats['overall'] = $overall_stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Get stats error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to load statistics']);
}
