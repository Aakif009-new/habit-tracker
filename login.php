<?php
// login.php
// Subject: Web Technology - Session Management, Security

session_start();

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if(empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        try {
            // Get user from database
            $query = "SELECT user_id, username, email, password_hash, dark_mode 
                     FROM users WHERE username = :username OR email = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if($user = $stmt->fetch()) {
                // Verify password
                if(password_verify($password, $user['password_hash'])) {
                    // Password correct - start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['dark_mode'] = $user['dark_mode'];

                    // Update last login
                    $update_query = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':user_id', $user['user_id']);
                    $update_stmt->execute();

                    // Set remember me cookie if requested (30 days)
                    if($remember) {
                        $token = bin2hex(random_bytes(32));
                        // Store token in database (you'd need a remember_tokens table)
                        // For now, just set a simple cookie
                        setcookie('remember_user', $user['user_id'], time() + (86400 * 30), '/');
                    }

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Login failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Habit Tracker</title>
    <link rel="stylesheet" href="assests/css/style.css">
    <link rel="stylesheet" href="assests/css/dark-mode.css">
    <script>
        (function(){ var t = localStorage.getItem('theme'); if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t); })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* ----- Login page: elegant & ethereal ----- */
        body.login-page {
            min-height: 100vh;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            overflow-x: hidden;
        }

        .login-page .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            background: linear-gradient(135deg, #e8eaf6 0%, #fce4ec 30%, #e3f2fd 60%, #f3e5f5 100%);
            background-size: 400% 400%;
            animation: gradientShift 18s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .login-page .auth-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(186, 164, 239, 0.35), transparent),
                        radial-gradient(ellipse 60% 40% at 90% 80%, rgba(255, 183, 213, 0.25), transparent),
                        radial-gradient(ellipse 50% 30% at 10% 60%, rgba(179, 206, 255, 0.3), transparent);
            pointer-events: none;
        }

        .login-page .auth-card {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(131, 131, 179, 0.12),
                        0 2px 8px rgba(0, 0, 0, 0.04),
                        inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.6);
            animation: cardFloat 6s ease-in-out infinite;
        }

        @keyframes cardFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .login-page .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-page .auth-header i {
            display: inline-block;
            color: #9a7bd4;
            filter: drop-shadow(0 4px 12px rgba(154, 123, 212, 0.35));
            animation: iconGlow 4s ease-in-out infinite;
        }

        @keyframes iconGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.92; transform: scale(1.02); }
        }

        .login-page .auth-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.25rem;
            font-weight: 500;
            letter-spacing: 0.02em;
            color: #2d2a3a;
            margin: 1rem 0 0.35rem;
            line-height: 1.2;
        }

        .login-page .auth-header p {
            font-size: 0.95rem;
            font-weight: 300;
            color: #6b6785;
            letter-spacing: 0.01em;
        }

        .login-page .error-message {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.9rem 1rem;
            margin-bottom: 1.5rem;
            background: rgba(244, 143, 177, 0.15);
            border: 1px solid rgba(244, 143, 177, 0.35);
            border-radius: 12px;
            color: #b84a6b;
            font-size: 0.9rem;
            font-weight: 400;
        }

        .login-page .error-message i {
            flex-shrink: 0;
            opacity: 0.9;
        }

        .login-page .auth-form .form-group {
            margin-bottom: 1.35rem;
        }

        .login-page .auth-form label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #4a4659;
            margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }

        .login-page .auth-form label i {
            margin-right: 0.5rem;
            color: #9a7bd4;
            opacity: 0.85;
        }

        .login-page .auth-form input[type="text"],
        .login-page .auth-form input[type="password"] {
            width: 100%;
            padding: 0.95rem 1.1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 400;
            color: #2d2a3a;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(154, 123, 212, 0.2);
            border-radius: 14px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .login-page .auth-form input::placeholder {
            color: #9e9bb0;
        }

        .login-page .auth-form input:focus {
            outline: none;
            border-color: rgba(154, 123, 212, 0.5);
            box-shadow: 0 0 0 3px rgba(154, 123, 212, 0.12);
            background: rgba(255, 255, 255, 0.95);
        }

        .login-page .auth-form .form-group-remember {
            flex-direction: row;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .login-page .auth-form .form-group-remember input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: #9a7bd4;
            cursor: pointer;
        }

        .login-page .auth-form .form-group-remember label {
            margin: 0 0 0 0.6rem;
            display: inline;
            cursor: pointer;
            font-weight: 400;
        }

        .login-page .auth-form .btn-primary.btn-block {
            width: 100%;
            padding: 1rem 1.5rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            color: #fff;
            background: linear-gradient(135deg, #9a7bd4 0%, #b794d6 100%);
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(154, 123, 212, 0.35);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.25s ease, opacity 0.2s ease;
        }

        .login-page .auth-form .btn-primary.btn-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(154, 123, 212, 0.4);
        }

        .login-page .auth-form .btn-primary.btn-block:active {
            transform: translateY(0);
        }

        .login-page .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.75rem;
            border-top: 1px solid rgba(154, 123, 212, 0.15);
            font-size: 0.9rem;
            color: #6b6785;
        }

        .login-page .auth-footer a {
            color: #9a7bd4;
            text-decoration: none;
            font-weight: 500;
            letter-spacing: 0.02em;
            transition: color 0.2s ease, text-decoration 0.2s ease;
        }

        .login-page .auth-footer a:hover {
            color: #7b5fb8;
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-check-circle fa-3x"></i>
                <h1>Welcome Back</h1>
                <p>Login to continue tracking your habits</p>
            </div>

            <?php if($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username or Email
                    </label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required placeholder="Enter your username or email">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" 
                           required placeholder="Enter your password">
                </div>

                <div class="form-group form-group-remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>