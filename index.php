<?php
// index.php - Landing Page
session_start();

// If logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Tracker - Build Better Habits</title>
    <link rel="stylesheet" href="assests/css/style.css">
    <link rel="stylesheet" href="assests/css/dark-mode.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        (function(){ var t = localStorage.getItem('theme'); if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t); })();
    </script>
    <style>
        .hero-section {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .btn-cta {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .features-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 40px 0;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .feature-card i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-cta {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-check-circle"></i> HabitTracker
            </div>
            <div class="nav-menu">
                <button type="button" id="darkModeToggle" class="btn-icon" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-primary">Sign Up Free</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <h1>Build Better Habits,<br>One Day at a Time</h1>
            <p>Track your daily habits, maintain streaks, and achieve your goals with our simple habit tracker.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn-cta btn-primary">Get Started Free</a>
                <a href="#features" class="btn-cta btn-secondary">Learn More</a>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="feature-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Daily Tracking</h3>
                <p>Mark your habits complete each day with a single click. Visual calendar shows your progress.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-fire"></i>
                <h3>Streak Tracking</h3>
                <p>Build momentum with streak counters. Watch your consistency grow day by day.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>Progress Charts</h3>
                <p>Visualize your habit completion with beautiful charts and analytics.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-moon"></i>
                <h3>Dark Mode</h3>
                <p>Easy on the eyes with automatic dark mode that follows your system preference.</p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="page-footer" style="text-align: center; padding: 40px 0; color: #666;">
            <p>&copy; 2026 HabitTracker. Built with PHP, MySQL, and JavaScript.</p>
        </footer>
        <footer class="page-footer" style="text-align: center; padding: 40px 0; color: #666;">
            <p>&copy; Developed by Mohammed Aakif.S</p>
        </footer>
    </div>

    <style>
        .btn-login {
            color: #667eea;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        .navbar .btn-primary {
            padding: 8px 20px;
            font-size: 1rem;
        }
    </style>
    <script src="assests/js/dark-mode.js"></script>
</body>
</html>