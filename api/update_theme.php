<?php
// api/update_theme.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$theme = $_POST['theme'] ?? 'light';

// Validate theme
if (!in_array($theme, ['light', 'dark'])) {
    $theme = 'light';
}

try {
    $query = "UPDATE users SET dark_mode = ? WHERE user_id = ?";
    $dark_mode = ($theme === 'dark') ? 1 : 0;
    
    $stmt = $db->prepare($query);
    $stmt->execute([$dark_mode, $user_id]);
    
    // Update session
    $_SESSION['dark_mode'] = $dark_mode;
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Update theme error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
}
?>