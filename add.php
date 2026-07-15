<?php
// add.php
// Second page containing form to capture new applications.
// Fields: Company name, Job Title, Person of contact, email, Date of Contact (default current), Notes, Status.

require_once 'auth.php';
require_once 'db.php';
require_once 'crypto.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = trim($_POST['job_title'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_of_contact = trim($_POST['date_of_contact'] ?? '');
    $status = trim($_POST['status'] ?? 'Open');
    $notes = trim($_POST['notes'] ?? '');

    // Default to current date if empty
    if (empty($date_of_contact)) {
        $date_of_contact = date('Y-m-d');
    }

    if (empty($job_title) || empty($company_name)) {
        $error = 'Job Title and Company Name are required fields.';
    } else {
        try {
            // Encrypt all captured fields for privacy/security
            $enc_job_title = encrypt_field($job_title);
            $enc_company_name = encrypt_field($company_name);
            $enc_contact_person = encrypt_field($contact_person);
            $enc_email = encrypt_field($email);
            $enc_date_of_contact = encrypt_field($date_of_contact);
            $enc_status = encrypt_field($status);
            $enc_notes = encrypt_field($notes);

            $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_title, company_name, contact_person, email, date_of_contact, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $enc_job_title,
                $enc_company_name,
                $enc_contact_person,
                $enc_email,
                $enc_date_of_contact,
                $enc_status,
                $enc_notes
            ]);

            $success = 'Job Application created successfully!';

            // Clear standard inputs for new entry
            $job_title = '';
            $company_name = '';
            $contact_person = '';
            $email = '';
            $date_of_contact = '';
            $notes = '';
        } catch (PDOException $e) {
            $error = 'Failed to save application: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card p-4">
            <div class="card-body">
                <h2 class="card-title fw-bold mb-1">Add Job Application</h2>
                <p class="text-muted mb-4">Enter the details of your new application below. All values will be encrypted before storage.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form action="add.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="job_title" name="job_title" placeholder="e.g. Software Engineer" value="<?php echo htmlspecialchars($job_title ?? ''); ?>" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="e.g. Acme Corp" value="<?php echo htmlspecialchars($company_name ?? ''); ?>" required autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="e.g. Jane Doe" value="<?php echo htmlspecialchars($contact_person ?? ''); ?>" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="e.g. recruiter@company.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_contact" class="form-label">Date of Contact</label>
                            <input type="date" class="form-control" id="date_of_contact" name="date_of_contact" value="<?php echo htmlspecialchars($date_of_contact ?? date('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Open" selected>Open</option>
                                <option value="Interview">Interview</option>
                                <option value="Offer">Offer</option>
                                <option value="Job pool">Job pool</option>
                                <option value="Declined">Declined</option>
                                <option value="Ghosted">Ghosted</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add specific details, interview stages, salary info, or next steps..."><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><span class="text-danger">*</span> Required fields</span>
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-file-earmark-plus-fill me-1"></i> Save Application
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
