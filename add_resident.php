<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $purok = $_POST['purok'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $birthdate = $_POST['birthdate'];
    $contact_number = $_POST['contact_number'];

    $stmt = $conn->prepare("INSERT INTO residents (first_name, middle_name, last_name, purok, gender, civil_status, birthdate, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $purok, $gender, $civil_status, $birthdate, $contact_number);

    if ($stmt->execute()) {
        header("Location: ../views/residents.php?success=1");
    } else {
        header("Location: ../views/residents.php?error=1");
    }
    $stmt->close();
}
?>