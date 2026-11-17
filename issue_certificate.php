<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resident_id = $_POST['resident_id'];
    $cert_type = $_POST['cert_type'];
    $purpose = $_POST['purpose'];
    $date_issued = date('Y-m-d');
    $issued_by = 1; 

    $stmt = $conn->prepare("INSERT INTO certificates (resident_id, cert_type, purpose, date_issued, issued_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $resident_id, $cert_type, $purpose, $date_issued, $issued_by);

    if ($stmt->execute()) {
        $cert_id = $stmt->insert_id;
        // Redirect diretso sa print preview page
        header("Location: ../views/print_certificate.php?id=" . $cert_id);
    } else {
        header("Location: ../views/certificates.php?error=1");
    }
    $stmt->close();
}
?>