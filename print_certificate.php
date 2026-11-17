<?php
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$cert_id = $_GET['id'];


$query = "SELECT c.*, r.first_name, r.middle_name, r.last_name, r.purok, r.birthdate, r.civil_status 
          FROM certificates c 
          JOIN residents r ON c.resident_id = r.resident_id 
          WHERE c.cert_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cert_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Certificate not found.");
}

$data = $result->fetch_assoc();

// Compute age from birthdate
$birthDate = new DateTime($data['birthdate']);
$today = new DateTime();
$age = $today->diff($birthDate)->y;

// Format full name
$middle_initial = !empty($data['middle_name']) ? strtoupper(substr($data['middle_name'], 0, 1)) . '.' : '';
$fullname = strtoupper("{$data['first_name']} {$middle_initial} {$data['last_name']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - <?php echo $data['cert_type']; ?></title>
    <!-- External CSS link -->
    <link rel="stylesheet" href="../css/print.css">
</head>
<body onload="window.print()">

    <!-- Print Control Button -->
    <div class="no-print print-controls">
        <button onclick="window.print()">Print Document</button>
        <a href="certificates.php">Back</a>
    </div>

    <!-- Letterhead -->
    <div class="header">
        <h4>Republic of the Philippines</h4>
        <h4>Province of Zambales</h4>
        <h4>Municipality of Masinloc</h4>
        <h2>BARANGAY Inhobol</h2>
        <h1>OFFICE OF THE PUNONG BARANGAY</h1>
    </div>

   
    <div class="cert-title"><?php echo strtoupper($data['cert_type']); ?></div>

    <div class="cert-body">
        <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
        
        <p>
            This is to certify that <strong><?php echo $fullname; ?></strong>, <?php echo $age; ?> years old, 
            <?php echo strtolower($data['civil_status']); ?>, a resident of Purok <?php echo $data['purok']; ?>, 
            this barangay, is personally known to me to be a person of good moral character and a law-abiding citizen.
        </p>

        <p>
            This certification is being issued upon the request of the above-named person for 
            <strong><?php echo htmlspecialchars($data['purpose']); ?></strong> and for whatever legal intent it may serve best.
        </p>

        <p>
            Given this <?php echo date('jS', strtotime($data['date_issued'])); ?> day of <?php echo date('F, Y', strtotime($data['date_issued'])); ?> 
            at Barangay Inhobol, Masinloc, Zambales.
        </p>
    </div>

    <!-- Signatory -->
    <div class="signature-section">
        <div style="font-size: 14px;">Certified by:</div>
        <div class="signature-line">HON. EDGAR ANGELES</div>
        <div style="font-size: 14px; margin-top: 2px;">Punong Barangay</div>
    </div>

</body>
</html>