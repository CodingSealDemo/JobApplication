<?php
// list.php
// Third page: Searchable, filterable list of all applications with editing/modal updating capabilities.

require_once 'auth.php';
require_once 'db.php';
require_once 'crypto.php';

require_login();

// Handle secure deletion if requested via post to prevent CSRF / unauthorized deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = intval($_POST['id'] ?? 0);
    if ($delete_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $_SESSION['user_id']]);
        header("Location: list.php?deleted=1");
        exit();
    }
}

// Fetch and decrypt all user applications
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$db_apps = $stmt->fetchAll();

$apps = [];
foreach ($db_apps as $app) {
    $apps[] = [
        'id' => $app['id'],
        'job_title' => decrypt_field($app['job_title']),
        'company_name' => decrypt_field($app['company_name']),
        'contact_person' => decrypt_field($app['contact_person']),
        'email' => decrypt_field($app['email']),
        'date_of_contact' => decrypt_field($app['date_of_contact']),
        'status' => decrypt_field($app['status']),
        'notes' => decrypt_field($app['notes']),
    ];
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold mb-1">All Job Applications</h1>
        <p class="text-muted">A complete searchable and filterable database of your job applications.</p>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Application
    </a>
</div>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Application updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-trash-fill me-2"></i> Application deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card p-3 mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="bi bi-funnel-fill me-1"></i> Column Filters & Search</h5>
        <div class="row g-2">
            <div class="col-md-2">
                <input type="text" id="search-title" class="form-control form-control-sm" placeholder="Search Title...">
            </div>
            <div class="col-md-2">
                <input type="text" id="search-company" class="form-control form-control-sm" placeholder="Search Company...">
            </div>
            <div class="col-md-2">
                <input type="text" id="search-contact" class="form-control form-control-sm" placeholder="Search Contact...">
            </div>
            <div class="col-md-2">
                <input type="text" id="search-email" class="form-control form-control-sm" placeholder="Search Email...">
            </div>
            <div class="col-md-2">
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="Open">Open</option>
                    <option value="Interview">Interview</option>
                    <option value="Offer">Offer</option>
                    <option value="Job pool">Job pool</option>
                    <option value="Declined">Declined</option>
                    <option value="Ghosted">Ghosted</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="clear-filters" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-eraser-fill me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="apps-table">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 60px;">ID</th>
                    <th scope="col">Job Title</th>
                    <th scope="col">Company</th>
                    <th scope="col">Contact Person</th>
                    <th scope="col">Email</th>
                    <th scope="col" style="width: 130px;">Date</th>
                    <th scope="col" style="width: 120px;">Status</th>
                    <th scope="col" style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($apps)): ?>
                    <tr id="no-apps-row">
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-folder2-open display-4 d-block mb-3"></i>
                            No applications found. <a href="add.php">Add your first application!</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($apps as $app): ?>
                        <tr class="app-row"
                            data-id="<?php echo $app['id']; ?>"
                            data-title="<?php echo htmlspecialchars($app['job_title']); ?>"
                            data-company="<?php echo htmlspecialchars($app['company_name']); ?>"
                            data-contact="<?php echo htmlspecialchars($app['contact_person']); ?>"
                            data-email="<?php echo htmlspecialchars($app['email']); ?>"
                            data-date="<?php echo htmlspecialchars($app['date_of_contact']); ?>"
                            data-status="<?php echo htmlspecialchars($app['status']); ?>"
                            data-notes="<?php echo htmlspecialchars($app['notes']); ?>">

                            <td class="text-muted fw-bold">#<?php echo $app['id']; ?></td>
                            <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($app['job_title']); ?></span></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['contact_person'] ?: '-'); ?></td>
                            <td>
                                <?php if (!empty($app['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" class="text-decoration-none small"><?php echo htmlspecialchars($app['email']); ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="small"><?php echo htmlspecialchars($app['date_of_contact']); ?></span></td>
                            <td>
                                <?php
                                $badge_class = 'bg-primary';
                                switch (strtolower($app['status'])) {
                                    case 'open': $badge_class = 'bg-success'; break;
                                    case 'interview': $badge_class = 'bg-info text-dark'; break;
                                    case 'offer': $badge_class = 'bg-warning text-dark'; break;
                                    case 'job pool': $badge_class = 'bg-secondary'; break;
                                    case 'declined': $badge_class = 'bg-danger'; break;
                                    case 'ghosted': $badge_class = 'bg-dark'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($app['status']); ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm edit-btn" title="Edit Application">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm delete-btn" title="Delete Application">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="no-matching-row" style="display: none;">
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-search display-4 d-block mb-3"></i>
                            No matching applications found. Try adjusting your filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Application Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editModalLabel"><i class="bi bi-pencil-square me-2 text-primary"></i> Edit Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_app.php" method="POST" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="edit-id-field" name="id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-title-field" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-title-field" name="job_title" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-company-field" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-company-field" name="company_name" required autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-contact-field" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="edit-contact-field" name="contact_person" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-email-field" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="edit-email-field" name="email" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-date-field" class="form-label">Date of Contact</label>
                            <input type="date" class="form-control" id="edit-date-field" name="date_of_contact">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-status-field" class="form-label">Status</label>
                            <select class="form-select" id="edit-status-field" name="status">
                                <option value="Open">Open</option>
                                <option value="Interview">Interview</option>
                                <option value="Offer">Offer</option>
                                <option value="Job pool">Job pool</option>
                                <option value="Declined">Declined</option>
                                <option value="Ghosted">Ghosted</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit-notes-field" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit-notes-field" name="notes" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel"><i class="bi bi-trash-fill me-2"></i> Delete Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to delete this job application? This action cannot be undone.</p>
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <strong class="d-block text-dark" id="delete-details-title"></strong>
                        <span class="text-muted small" id="delete-details-company"></span>
                    </div>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete-id-field" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Front-end dynamic search and filter implementation
    const searchTitle = document.getElementById("search-title");
    const searchCompany = document.getElementById("search-company");
    const searchContact = document.getElementById("search-contact");
    const searchEmail = document.getElementById("search-email");
    const filterStatus = document.getElementById("filter-status");
    const clearFiltersBtn = document.getElementById("clear-filters");

    const appRows = document.querySelectorAll(".app-row");
    const noMatchingRow = document.getElementById("no-matching-row");

    function applyFilters() {
        const queryTitle = searchTitle.value.toLowerCase().trim();
        const queryCompany = searchCompany.value.toLowerCase().trim();
        const queryContact = searchContact.value.toLowerCase().trim();
        const queryEmail = searchEmail.value.toLowerCase().trim();
        const queryStatus = filterStatus.value.toLowerCase().trim();

        let visibleCount = 0;

        appRows.forEach(row => {
            const title = row.getAttribute("data-title").toLowerCase();
            const company = row.getAttribute("data-company").toLowerCase();
            const contact = row.getAttribute("data-contact").toLowerCase();
            const email = row.getAttribute("data-email").toLowerCase();
            const status = row.getAttribute("data-status").toLowerCase();

            const matchesTitle = title.includes(queryTitle);
            const matchesCompany = company.includes(queryCompany);
            const matchesContact = contact.includes(queryContact);
            const matchesEmail = email.includes(queryEmail);
            const matchesStatus = queryStatus === "" || status === queryStatus;

            if (matchesTitle && matchesCompany && matchesContact && matchesEmail && matchesStatus) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        if (appRows.length > 0) {
            if (visibleCount === 0) {
                noMatchingRow.style.display = "";
            } else {
                noMatchingRow.style.display = "none";
            }
        }
    }

    // Attach real-time keyup and change events for interactive feedback
    [searchTitle, searchCompany, searchContact, searchEmail].forEach(el => {
        el.addEventListener("keyup", applyFilters);
    });
    filterStatus.addEventListener("change", applyFilters);

    // Reset filters handler
    clearFiltersBtn.addEventListener("click", function() {
        searchTitle.value = "";
        searchCompany.value = "";
        searchContact.value = "";
        searchEmail.value = "";
        filterStatus.value = "";
        applyFilters();
    });

    // Modal Edit Binding
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const editIdField = document.getElementById('edit-id-field');
    const editTitleField = document.getElementById('edit-title-field');
    const editCompanyField = document.getElementById('edit-company-field');
    const editContactField = document.getElementById('edit-contact-field');
    const editEmailField = document.getElementById('edit-email-field');
    const editDateField = document.getElementById('edit-date-field');
    const editStatusField = document.getElementById('edit-status-field');
    const editNotesField = document.getElementById('edit-notes-field');

    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const row = this.closest(".app-row");
            editIdField.value = row.getAttribute("data-id");
            editTitleField.value = row.getAttribute("data-title");
            editCompanyField.value = row.getAttribute("data-company");
            editContactField.value = row.getAttribute("data-contact");
            editEmailField.value = row.getAttribute("data-email");
            editDateField.value = row.getAttribute("data-date");
            editStatusField.value = row.getAttribute("data-status");
            editNotesField.value = row.getAttribute("data-notes");

            editModal.show();
        });
    });

    // Check if deep linking to edit an ID from the dashboard
    const urlParams = new URLSearchParams(window.location.search);
    const deepEditId = urlParams.get('edit_id');
    if (deepEditId) {
        const targetRow = document.querySelector(`.app-row[data-id="${deepEditId}"]`);
        if (targetRow) {
            editIdField.value = targetRow.getAttribute("data-id");
            editTitleField.value = targetRow.getAttribute("data-title");
            editCompanyField.value = targetRow.getAttribute("data-company");
            editContactField.value = targetRow.getAttribute("data-contact");
            editEmailField.value = targetRow.getAttribute("data-email");
            editDateField.value = targetRow.getAttribute("data-date");
            editStatusField.value = targetRow.getAttribute("data-status");
            editNotesField.value = targetRow.getAttribute("data-notes");

            editModal.show();
        }
    }

    // Modal Delete Binding
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteIdField = document.getElementById('delete-id-field');
    const deleteDetailsTitle = document.getElementById('delete-details-title');
    const deleteDetailsCompany = document.getElementById('delete-details-company');

    document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const row = this.closest(".app-row");
            deleteIdField.value = row.getAttribute("data-id");
            deleteDetailsTitle.textContent = row.getAttribute("data-title");
            deleteDetailsCompany.textContent = row.getAttribute("data-company");

            deleteModal.show();
        });
    });
});
</script>

<?php
include 'footer.php';
?>
