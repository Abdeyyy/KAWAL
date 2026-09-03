<?php
// login.php - Admin login page for KAWAL
require_once 'config.php';

$error = '';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Process login post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - KAWAL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">KAWAL <span>.</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Simulator</a></li>
            <li><a href="login.php" style="color: var(--primary);">Admin Login</a></li>
        </ul>
    </header>

    <div class="glass form-container">
        <h2 class="form-title">Admin Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert danger"><?php echo esc($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">USERNAME</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="glow-btn" style="width: 100%; margin-top: 10px;">
                Masuk Dashboard
            </button>
        </form>
    </div>

</body>
</html>
