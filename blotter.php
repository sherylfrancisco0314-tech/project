
<?php
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Search and pagination settings
$search = trim($_GET['search'] ?? '');
$perPage = 50;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Count matching blotter records
if ($search !== '') {
    $keyword = "%{$search}%";

    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM blotter
        WHERE CONCAT_WS(
            ' ',
            complainant,
            respondent,
            incident_type,
            location,
            details,
            status
        ) LIKE ?
    ");

    $countStmt->bind_param("s", $keyword);
    $countStmt->execute();

    $totalRows = (int) $countStmt
        ->get_result()
        ->fetch_assoc()['total'];

    $countStmt->close();
} else {
    $countResult = $conn->query(
        "SELECT COUNT(*) AS total FROM blotter"
    );

    $totalRows = (int) $countResult
        ->fetch_assoc()['total'];
}

$totalPages = max(1, (int) ceil($totalRows / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// Fetch current page only
if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT *
        FROM blotter
        WHERE CONCAT_WS(
            ' ',
            complainant,
            respondent,
            incident_type,
            location,
            details,
            status
        ) LIKE ?
        ORDER BY incident_date DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("sii", $keyword, $perPage, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT *
        FROM blotter
        ORDER BY incident_date DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$blotters = $stmt->get_result();
?>

<div class="main-content">
    <h1>Blotter / Incident Records</h1>
    <p>Manage incident reports and monitor their settlement status.</p>

    <div class="card">
        <h3>File New Incident Report</h3>

        <form
            action="../actions/add_blotter.php"
            method="POST"
            class="blotter-form"
        >
            <div class="blotter-field">
                <label for="complainant">Complainant Name</label>
                <input
                    type="text"
                    id="complainant"
                    name="complainant"
                    placeholder="Enter complainant name"
                    required
                >
            </div>

            <div class="blotter-field">
                <label for="respondent">Respondent Name</label>
                <input
                    type="text"
                    id="respondent"
                    name="respondent"
                    placeholder="Enter respondent name"
                    required
                >
            </div>

            <div class="blotter-field">
                <label for="incident_type">Incident Type</label>
                <input
                    type="text"
                    id="incident_type"
                    name="incident_type"
                    placeholder="e.g. Alitan"
                    required
                >
            </div>

            <div class="blotter-field">
                <label for="incident_date">Date and Time</label>
                <input
                    type="datetime-local"
                    id="incident_date"
                    name="incident_date"
                    required
                >
            </div>

            <div class="blotter-field full-width">
                <label for="location">Incident Location</label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    placeholder="Enter incident location"
                    required
                >
            </div>

            <div class="blotter-field full-width">
                <label for="details">Incident Details</label>
                <textarea
                    id="details"
                    name="details"
                    placeholder="Describe the incident..."
                    required
                ></textarea>
            </div>

            <button type="submit" class="blotter-submit">
                Submit Blotter Report
            </button>
        </form>
    </div>

    <div class="card">
        <h3>Incident List</h3>

        <div class="resident-search-wrapper">
            <form method="GET">
                <input
                    type="search"
                    name="search"
                    class="resident-search-input"
                    placeholder="Search complainant, respondent, incident, location, or status..."
                    aria-label="Search blotter records"
                    value="<?= htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <button type="submit" class="blotter-submit">
                    Search
                </button>

        
            </form>

            <p class="resident-search-count">
                <?= number_format($totalRows) ?>
                <?= $totalRows === 1
                    ? 'incident found'
                    : 'incidents found' ?>
            </p>
        </div>

        <div class="blotter-table-wrapper">
            <table class="blotter-table">
                <thead>
                    <tr>
                        <th>Complainant</th>
                        <th>Respondent</th>
                        <th>Incident</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($blotters->num_rows > 0): ?>

                        <?php while ($row = $blotters->fetch_assoc()): ?>
                            <?php
                            $status = $row['status'] ?? 'Pending';

                            $statusClass = match ($status) {
                                'Settled' => 'settled',
                                'Unsettled' => 'unsettled',
                                default => 'pending'
                            };
                            ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $row['complainant'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['respondent'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['incident_type'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['incident_date'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="blotter-status <?= $statusClass ?>">
                                        <?= htmlspecialchars(
                                            $status,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="blotter-actions">

                                        <?php if ($status !== 'Settled'): ?>
                                            <a
                                                class="blotter-action settle"
                                                href="../actions/update_blotter_status.php?id=<?= (int) $row['blotter_id'] ?>&amp;status=Settled"
                                                onclick="return confirm('Mark this incident as settled?');"
                                            >
                                                Mark Settled
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($status !== 'Unsettled'): ?>
                                            <a
                                                class="blotter-action unsettle"
                                                href="../actions/update_blotter_status.php?id=<?= (int) $row['blotter_id'] ?>&amp;status=Unsettled"
                                                onclick="return confirm('Mark this incident as unsettled?');"
                                            >
                                                Mark Unsettled
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($status !== 'Pending'): ?>
                                            <a
                                                class="blotter-action pending"
                                                href="../actions/update_blotter_status.php?id=<?= (int) $row['blotter_id'] ?>&amp;status=Pending"
                                                onclick="return confirm('Revert this incident to pending?');"
                                            >
                                                Revert to Pending
                                            </a>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="resident-empty-state">
                                <?= $search !== ''
                                    ? 'No matching incident records found.'
                                    : 'No incident records found.' ?>
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="resident-pagination">

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&amp;search=<?= urlencode($search) ?>">
                        Previous
                    </a>
                <?php endif; ?>

                <span>
                    Page <?= $page ?> of <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&amp;search=<?= urlencode($search) ?>">
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
