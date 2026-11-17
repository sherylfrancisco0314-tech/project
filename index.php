
<?php
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin123') {
        session_regenerate_id(true);

        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['fullname'] = 'System Administrator';
        $_SESSION['role'] = 'Admin';

        header("Location: views/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>Barangay System</h2>
        <p class="login-subtitle">Sign in to your account</p>

        <?php if (!empty($error)): ?>
            <div class="login-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="login-field">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="login-field">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>

</body>
</html>
