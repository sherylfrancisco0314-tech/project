
<?php
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/header.php';
include '../includes/sidebar.php';

$res_count = $conn->query("SELECT COUNT(*) AS total FROM residents")->fetch_assoc()['total'];
$cert_count = $conn->query("SELECT COUNT(*) AS total FROM certificates")->fetch_assoc()['total'];
$blotter_count = $conn->query("SELECT COUNT(*) AS total FROM blotter")->fetch_assoc()['total'];
?>

<div class="main-content">
    <h1>Dashboard</h1>

    <p>
        Welcome back,
        <strong><?= htmlspecialchars($_SESSION['fullname'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></strong>!
    </p>

    <div class="stats-container">
        <div class="stat-box blue">
            <h3>Total Residents</h3>
            <p><?= number_format((int) $res_count) ?></p>
        </div>

        <div class="stat-box green">
            <h3>Certificates Issued</h3>
            <p><?= number_format((int) $cert_count) ?></p>
        </div>

        <div class="stat-box red">
            <h3>Blotter Cases</h3>
            <p><?= number_format((int) $blotter_count) ?></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
