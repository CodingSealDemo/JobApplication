<?php
// register.php
// User registration page with a registration key check.

require_once 'auth.php';
require_once 'db.php';

// Redirect to dashboard if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $registration_key = trim($_POST['registration_key'] ?? '');

    if (empty($username) || empty($password) || empty($confirm_password) || empty($registration_key)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($registration_key !== REGISTRATION_KEY) {
        $error = 'Invalid Registration Key.';
    } else {
        try {
            // Check if username is taken
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username is already taken.';
            } else {
                // Hash the password securely with bcrypt
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new user
                $insert_stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                $insert_stmt->execute([$username, $password_hash]);

                $success = 'Registration successful! You can now <a href="login.php" class="alert-link">login</a>.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred during registration. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JobTracker</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            max-width: 450px;
            width: 100%;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .brand-logo {
            font-size: 2.5rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="card register-card p-4">
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="brand-logo mb-2">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <h3 class="card-title fw-bold">Create an Account</h3>
            <p class="text-muted small">Enter details below to register your tracker account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autocomplete="off">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min. 8 characters" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="registration_key" class="form-label">Registration Key</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="text" class="form-control" id="registration_key" name="registration_key" placeholder="Enter invitation registration key" required autocomplete="off">
                </div>
                <div class="form-text">This site is invitation-only. Please input the authorized key.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                <i class="bi bi-person-plus-fill me-2"></i> Register
            </button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted small">Already have an account?</span>
            <a href="login.php" class="text-decoration-none small fw-bold">Sign In</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
