<?php
// dashboard.php - Protected page (requires login)
session_start();
require_once 'config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Tracker - Dashboard</title>
    <link rel="stylesheet" href="assests/css/style.css">
    <link rel="stylesheet" href="assests/css/dark-mode.css">
    <script>
        (function(){ var t = localStorage.getItem('theme'); if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t); })();
    </script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js for progress charts (pinned version for reliable rendering) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body data-user-id="<?php echo (int)$user_id; ?>" data-user-theme="<?php echo !empty($_SESSION['dark_mode']) ? 'dark' : 'light'; ?>">
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-check-circle"></i> HabitTracker
            </div>
            <div class="nav-menu">
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <!-- Add Habit Form -->
            <section class="add-habit-section">
                <h2>Create New Habit</h2>
                <form id="habitForm" class="habit-form">
                    <div class="form-group">
                        <input type="text" id="habitName" placeholder="Habit name (e.g., Exercise)" required>
                    </div>
                    <div class="form-group">
                        <textarea id="habitDescription" placeholder="Description (optional)"></textarea>
                   
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus"></i> Add Habit
                    </button>
                </form>
            </section>

            <!-- Habits Grid -->
            <section class="habits-section">
                <h2>My Habits</h2>
                <div id="habitsContainer" class="habits-grid">
                    <!-- Habits will be loaded here via JavaScript -->
                </div>
            </section>

            <!-- Progress Charts -->
            <section class="charts-section">
                <h2>Weekly Progress</h2>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <!-- Templates -->
    <template id="habitCardTemplate">
        <div class="habit-card" data-habit-id="">
            <div class="habit-header">
                <h3 class="habit-name"></h3>
                <button class="btn-delete" title="Delete habit">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <p class="habit-description"></p>
            <div class="habit-stats">
                <span class="streak">
                    <i class="fas fa-fire"></i>
                    <span class="current-streak">0</span> day streak
                </span>
                <span class="longest-streak">
                    <i class="fas fa-trophy"></i>
                    <span class="longest">0</span> longest
                </span>
            </div>
            <div class="week-grid">
                <!-- Days will be added here -->
            </div>
        </div>
    </template>

    <template id="dayButtonTemplate">
        <button class="day-btn" data-date="">
            <span class="day-name"></span>
            <span class="day-date"></span>
            <i class="fas fa-check check-icon"></i>
        </button>
    </template>

    <script src="assests/js/main.js"></script>
    <script src="assests/js/chart.js"></script>
    <script src="assests/js/dark-mode.js"></script>
</body>
</html>