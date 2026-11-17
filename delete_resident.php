
<?php
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $resident_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($resident_id !== false && $resident_id !== null && $resident_id > 0) {
        $stmt = $conn->prepare(
            "DELETE FROM residents WHERE resident_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("i", $resident_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header("Location: ../views/residents.php");
exit();
?>
