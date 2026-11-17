<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $blotter_id = $_GET['id'];
    $status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE blotter SET status = ? WHERE blotter_id = ?");
    $stmt->bind_param("si", $status, $blotter_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../views/blotter.php");
exit();
?>