<?php
/**
 * N4P (Not4Posers) POS System
 * Users Management Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser($conn);
$users = getAllUsers($conn);

// Handle adding/editing user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? '';
    $user_role = sanitize($_POST['user_role']);
    
    if (isset($_POST['add_user'])) {
        if (empty($password)) {
            setErrorMessage('Password is required for new users!');
        } else {
            $hashed_password = hashPassword($password);
            $query = "INSERT INTO users (full_name, username, email, password, user_role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $full_name, $username, $email, $hashed_password, $user_role);
            
            if ($stmt->execute()) {
                setSuccessMessage('User added successfully!');
                header('Location: ' . APP_URL . '/pages/users.php');
                exit;
            } else {
                setErrorMessage('Error adding user: ' . $stmt->error);
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    if ($delete_id === getCurrentUserId()) {
        setErrorMessage('Cannot delete your own account!');
    } else {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            setSuccessMessage('User deleted successfully!');
        } else {
            setErrorMessage('Error deleting user!');
        }
    }
    header('Location: ' . APP_URL . '/pages/users.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management | N4P POS System</title>
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
                <a href="<?php echo APP_URL; ?>/pages/inventory.php">Inventory</a>
                <a href="<?php echo APP_URL; ?>/pages/users.php" class="active">Users</a>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
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
            <li><a href="<?php echo APP_URL; ?>/pages/inventory.php">📈 Inventory</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/users.php" class="active">👥 Users</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <?php 
            $success = getSuccessMessage();
            if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
            
            $error = getErrorMessage();
            if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="page-title">Users Management</h1>
                <button onclick="openAddUserModal()" class="btn btn-primary">
                    ➕ Add User
                </button>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $u['user_role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($u['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $u['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($u['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($u['id'] !== getCurrentUserId()): ?>
                                            <a href="<?php echo APP_URL; ?>/pages/users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this user?')">Delete</a>
                                        <?php else: ?>
                                            <span style="color: #6b7280;">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add User Modal -->
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New User</h2>
                <button class="modal-close" onclick="closeAddUserModal()">×</button>
            </div>

            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="user_role">Role *</label>
                        <select id="user_role" name="user_role" required>
                            <option value="cashier">Cashier</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('active');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('active');
        }

        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function logout() {
            if (confirm('Logout?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addUserModal');
            if (event.target === modal) {
                closeAddUserModal();
            }
        });
    </script>
</body>
</html>
