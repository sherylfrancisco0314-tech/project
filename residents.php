
<?php
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Pagination
$perPage = 50;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $perPage;


if ($search !== '') {
    $keyword = "%{$search}%";

    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM residents
        WHERE CONCAT_WS(
            ' ',
            last_name,
            first_name,
            middle_name,
            purok,
            gender,
            civil_status,
            contact_number
        ) LIKE ?
    ");

    $countStmt->bind_param("s", $keyword);
    $countStmt->execute();
    $totalRows = (int) $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();
} else {
    $countResult = $conn->query(
        "SELECT COUNT(*) AS total FROM residents"
    );

    $totalRows = (int) $countResult->fetch_assoc()['total'];
}

$totalPages = max(1, (int) ceil($totalRows / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT *
        FROM residents
        WHERE CONCAT_WS(
            ' ',
            last_name,
            first_name,
            middle_name,
            purok,
            gender,
            civil_status,
            contact_number
        ) LIKE ?
        ORDER BY resident_id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("sii", $keyword, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("
        SELECT *
        FROM residents
        ORDER BY resident_id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="main-content">
    <h1>Resident Management</h1>
    <p>Manage barangay resident information and records.</p>

    <div class="card resident-form-card">
        <h3>Add New Resident</h3>

        <form action="../actions/add_resident.php" method="POST" class="resident-form">
            <div class="resident-field">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name"
                       placeholder="First name" maxlength="50" required>
            </div>

            <div class="resident-field">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name"
                       placeholder="Middle name" maxlength="50">
            </div>

            <div class="resident-field">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name"
                       placeholder="Last name" maxlength="50" required>
            </div>

            <div class="resident-field">
                <label for="purok">Purok</label>
                <input type="text" id="purok" name="purok"
                       placeholder="Enter purok" maxlength="50" required>
            </div>

            <div class="resident-field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="resident-field">
                <label for="civil_status">Civil Status</label>
                <select id="civil_status" name="civil_status" required>
                    <option value="">Select Civil Status</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Separated">Separated</option>
                </select>
            </div>

            <div class="resident-field">
                <label for="birthdate">Birthdate</label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>

            <div class="resident-field">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number"
                       placeholder="Contact number" maxlength="20">
            </div>

            <div class="resident-form-action">
                <button type="submit" class="resident-save-btn">
                    Save Resident
                </button>
            </div>
        </form>
    </div>

    <div class="card resident-list-card">
        <h3>Resident List</h3>

        <div class="resident-search-wrapper">
            <form method="GET">
                <input
                    type="search"
                    name="search"
                    class="resident-search-input"
                    placeholder="Search by name, purok, gender, civil status, or contact..."
                    aria-label="Search resident records"
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                >

                <button type="submit" class="certificate-submit">
                    Search
                </button>

            
            </form>

            <p class="resident-search-count">
                <?= number_format($totalRows) ?>
                <?= $totalRows === 1 ? 'resident found' : 'residents found' ?>
            </p>
        </div>

        <div class="resident-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Purok</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $fullName = $row['last_name'] . ', ' .
                                $row['first_name'];

                            if (!empty($row['middle_name'])) {
                                $fullName .= ' ' . $row['middle_name'];
                            }
                            ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['purok'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['gender'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['civil_status'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['contact_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <form
                                        action="../actions/delete_resident.php"
                                        method="POST"
                                        class="resident-delete-form"
                                        onsubmit="return confirm('Are you sure you want to delete this resident?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $row['resident_id'] ?>"
                                        >

                                        <button type="submit" class="resident-delete-btn">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="resident-empty-state">
                                <?= $search !== ''
                                    ? 'No matching residents found.'
                                    : 'No resident records found.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="resident-pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                        Previous
                    </a>
                <?php endif; ?>

                <span>
                    Page <?= $page ?> of <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
include '../includes/footer.php';
?>
