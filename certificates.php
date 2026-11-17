<?php
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$residents = $conn->query("SELECT resident_id, first_name, middle_name, last_name FROM residents ORDER BY last_name ASC");
?>

<div class="main-content">
    <h1>Certificate Issuance</h1>
    
    <div class="card" style="max-width: 600px;">
        <h3>Generate Clearance / Certificate</h3>
        <form action="../actions/issue_certificate.php" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;" onsubmit="return validateResident()">
        
            <div>
                <label>Search Resident Name</label>
                <input type="text" id="resident_input" list="resident_list" placeholder="Type last name or first name..." required autocomplete="off">
                <datalist id="resident_list">
                    <?php while($res = $residents->fetch_assoc()): ?>
                        <?php 
                            $fullname = "{$res['last_name']}, {$res['first_name']} {$res['middle_name']}";
                        ?>
                        <option value="<?php echo htmlspecialchars($fullname); ?>" data-id="<?php echo $res['resident_id']; ?>">
                    <?php endwhile; ?>
                </datalist>
                <input type="hidden" name="resident_id" id="resident_id">
            </div>

            <div>
                <label>Certificate Type</label>
                <select name="cert_type" required>
                    <option value="Barangay Clearance">Barangay Clearance</option>
                    <option value="Certificate of Indigency">Certificate of Indigency</option>
                    <option value="Certificate of Residency">Certificate of Residency</option>
                </select>
            </div>

            <div>
                <label>Purpose</label>
                <input type="text" name="purpose" placeholder="e.g. Employment, Local Requirement" required>
            </div>

            <button type="submit" style="background: #0a84ff; color: white;">Generate & Print</button>
        </form>
    </div>
</div>

<script>
document.getElementById('resident_input').addEventListener('input', function() {
    let val = this.value;
    let datalist = document.getElementById('resident_list');
    let options = datalist.options;
    let hiddenId = document.getElementById('resident_id');
    
    hiddenId.value = "";
    
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === val) {
            hiddenId.value = options[i].getAttribute('data-id');
            break;
        }
    }
});

function validateResident() {
    let hiddenId = document.getElementById('resident_id').value;
    if (!hiddenId) {
        alert("Please select a valid resident from the suggestion list.");
        return false;
    }
    return true;
}
</script>

<?php include '../includes/footer.php'; ?>