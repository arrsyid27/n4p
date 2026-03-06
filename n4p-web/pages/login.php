<?php
/**
 * N4P (Not4Posers) POS System
 * Login Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}

$error = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // Login query
        $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (default password: admin123)
            if (verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['user_role'];
                
                header('Location: ' . APP_URL . '/pages/dashboard.php');
                exit;
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username atau email tidak ditemukan!';
        }
    }
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = sanitize($_POST['reg_username'] ?? '');
    $email = sanitize($_POST['reg_email'] ?? '');
    $full_name = sanitize($_POST['reg_full_name'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $password_confirm = $_POST['reg_password_confirm'] ?? '';
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if username or email already exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Username atau email sudah terdaftar!';
        } else {
            // Create new user
            $hashed_password = hashPassword($password);
            $insert_query = "INSERT INTO users (username, email, full_name, password, user_role) VALUES (?, ?, ?, ?, 'cashier')";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);
            
            if ($insert_stmt->execute()) {
                // Auto login after signup
                $new_user_id = $conn->insert_id;
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['user_role'] = 'cashier';
                
                header('Location: ' . APP_URL . '/pages/dashboard.php');
                exit;
            } else {
                $error = 'Gagal membuat akun. Silakan coba lagi!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | N4P - Not4Posers POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-box {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            margin: 1rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 3rem;
            font-weight: 700;
            color: #000;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
        }

        .logo-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            letter-spacing: 1px;
        }

        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .tab-btn {
            flex: 1;
            padding: 1rem;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            bottom: -2px;
        }

        .tab-btn.active {
            color: #6b21a8;
            border-bottom-color: #6b21a8;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border-left: 4px solid #ef4444;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .form-group input {
            border: 1px solid #e5e7eb;
        }

        .form-group input:focus {
            border-color: #6b21a8;
            box-shadow: 0 0 0 3px rgba(107, 33, 168, 0.1);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .login-footer a {
            color: #6b21a8;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .success-message {
            background-color: #dcfce7;
            color: #166534;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border-left: 4px solid #10b981;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 1.5rem;
            }

            .logo {
                font-size: 2.5rem;
            }

            .tabs {
                margin-bottom: 1.5rem;
            }

            .tab-btn {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">N4P</div>
                <div class="logo-subtitle">Not4Posers POS System</div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message show">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn <?php echo $tab === 'login' ? 'active' : ''; ?>" onclick="switchTab(event, 'login')">
                    Sign In
                </button>
                <button class="tab-btn <?php echo $tab === 'signup' ? 'active' : ''; ?>" onclick="switchTab(event, 'signup')">
                    Sign Up
                </button>
            </div>

            <!-- Login Tab -->
            <div id="login" class="tab-content <?php echo $tab === 'login' ? 'active' : ''; ?>">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username atau Email</label>
                        <input type="text" id="username" name="username" placeholder="Masukkan username atau email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary btn-block btn-large">
                        Sign In
                    </button>
                </form>

                <div class="login-footer">
                    Demo: Username: <strong>admin</strong> | Password: <strong>admin123</strong>
                </div>
            </div>

            <!-- Signup Tab -->
            <div id="signup" class="tab-content <?php echo $tab === 'signup' ? 'active' : ''; ?>">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="reg_full_name">Nama Lengkap</label>
                        <input type="text" id="reg_full_name" name="reg_full_name" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_username">Username</label>
                        <input type="text" id="reg_username" name="reg_username" placeholder="Pilih username" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_email">Email</label>
                        <input type="email" id="reg_email" name="reg_email" placeholder="Masukkan email" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="reg_password" placeholder="Minimal 6 karakter" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_password_confirm">Konfirmasi Password</label>
                        <input type="password" id="reg_password_confirm" name="reg_password_confirm" placeholder="Konfirmasi password" required>
                    </div>

                    <button type="submit" name="signup" class="btn btn-primary btn-block btn-large">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(event, tabName) {
            event.preventDefault();
            
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Update URL
            window.history.pushState({}, '', '?tab=' + tabName);
        }
    </script>
</body>
</html>
