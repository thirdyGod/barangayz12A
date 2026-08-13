<?php
/**
 * Login Screen for Barangay Zone 12-A Admin Panel
 * 
 * SECURITY NOTES:
 * - 1. This system uses session-based authentication with session_regenerate_id() 
 *      to defend against session fixation attacks.
 * - 2. Passwords are verified using password_verify() against bcrypt hashes.
 * - 3. WARNING: The placeholder admin password ("admin123") seeded in database.sql
 *      must be updated immediately before any public deployment.
 * - 4. All SQL queries use prepared statements via PDO to defend against SQL Injection.
 */

session_start();
require_once __DIR__ . '/../config.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form inputs
    $username = isset($_POST['username']) ? trim(filter_var($_POST['username'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Using prepared statement to search for admin username
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            // Verify password using password_verify
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_user_id'] = $admin['id'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Barangay Zone 12-A</title>
    <link rel="stylesheet" href="assets/admin_style.css">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-logo" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
        <img src="../assets/images/logo.png" alt="Logo" style="height: 65px; width: 65px; object-fit: contain; margin-bottom: 0.5rem;">
        <div class="login-logo-title">ZONE 12-A</div>
        <div class="login-logo-badge">Administration Portal</div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form action="login.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn-action btn-add" style="width: 100%; font-size: 1rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-top: 1rem; justify-content: center;">
            <i class="bi bi-shield-lock-fill"></i> Secure Log In
        </button>
    </form>
    
    <div style="margin-top: 2rem; font-size: 0.75rem; color: var(--color-text-muted); text-align: center; line-height: 1.4;">
        ⚠️ <strong>Security Notice:</strong> Access is restricted to authorized personnel only. Login sessions are monitored.
    </div>
</div>

</body>
</html>
