<?php
/**
 * N4P (Not4Posers) POS System
 * User Profile Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $user_id = getCurrentUserId();
    
    $query = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $full_name, $phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        setSuccessMessage('Profile updated successfully!');
        header('Location: ' . APP_URL . '/pages/profile.php');
        exit;
    } else {
        setErrorMessage('Error updating profile!');
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = getCurrentUserId();
    
    // Verify old password
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
    
    if (!verifyPassword($old_password, $current_user['password'])) {
        setErrorMessage('Old password is incorrect!');
    } elseif ($new_password !== $confirm_password) {
        setErrorMessage('New passwords do not match!');
    } elseif (strlen($new_password) < 6) {
        setErrorMessage('New password must be at least 6 characters!');
    } else {
        $hashed_password = hashPassword($new_password);
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            setSuccessMessage('Password changed successfully!');
            header('Location: ' . APP_URL . '/pages/profile.php');
            exit;
        } else {
            setErrorMessage('Error changing password!');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | N4P POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>/" style="text-decoration: none; color: inherit;">N4P</a>
            </div>
            
            <div class="navbar-nav">
                <a href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/pos.php">POS</a>
                <a href="<?php echo APP_URL; ?>/pages/products.php">Products</a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php">Transactions</a>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?php echo APP_URL; ?>/pages/profile.php" class="active">My Profile</a>
                        <button onclick="logout()" style="color: #ef4444;">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="<?php echo APP_URL; ?>/pages/dashboard.php">📊 Dashboard</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/pos.php">🛒 POS / Checkout</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/products.php">📦 Products</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/transactions.php">📋 Transactions</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container" style="max-width: 600px;">
            <?php 
            $success = getSuccessMessage();
            if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
            
            $error = getErrorMessage();
            if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            ?>

            <h1 class="page-title">My Profile</h1>

            <!-- Profile Information -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Profile Information</h2>
                </div>

                <form method="POST" action="">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" value="<?php echo ucfirst($user['user_role']); ?>" disabled>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Change Password</h2>
                </div>

                <form method="POST" action="">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="old_password">Current Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function logout() {
            if (confirm('Logout?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }
    </script>
</body>
</html>
