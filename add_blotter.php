<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complainant = $_POST['complainant'];
    $respondent = $_POST['respondent'];
    $incident_type = $_POST['incident_type'];
    $incident_date = $_POST['incident_date'];
    $location = $_POST['location'];
    $details = $_POST['details'];

    $stmt = $conn->prepare("INSERT INTO blotter (complainant, respondent, incident_type, incident_date, location, details, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssss", $complainant, $respondent, $incident_type, $incident_date, $location, $details);

    if ($stmt->execute()) {
        header("Location: ../views/blotter.php?success=1");
    } else {
        header("Location: ../views/blotter.php?error=1");
    }
    $stmt->close();
}
?>