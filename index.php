<?php
// index.php
// Landing page containing open/active applications.
// Applications older than 14 days are marked red (attention / check in on).
// Applications with 'Declined' or 'Ghosted' are NOT listed here.

require_once 'auth.php';
require_once 'db.php';
require_once 'crypto.php';

require_login();

// Retrieve all user applications
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$all_apps = $stmt->fetchAll();

$open_apps = [];

foreach ($all_apps as $app) {
    // Decrypt all application fields
    $decrypted_app = [
        'id' => $app['id'],
        'job_title' => decrypt_field($app['job_title']),
        'company_name' => decrypt_field($app['company_name']),
        'contact_person' => decrypt_field($app['contact_person']),
        'email' => decrypt_field($app['email']),
        'date_of_contact' => decrypt_field($app['date_of_contact']),
        'status' => decrypt_field($app['status']),
        'notes' => decrypt_field($app['notes']),
        'created_at' => $app['created_at'],
    ];

    // Filter out 'Declined' and 'Ghosted' statuses
    $status_lower = strtolower(trim($decrypted_app['status']));
    if ($status_lower !== 'declined' && $status_lower !== 'ghosted') {
        // Calculate age
        $date_contact = $decrypted_app['date_of_contact'];
        $attention = false;

        $contact_timestamp = strtotime($date_contact);
        if ($contact_timestamp !== false) {
            $days_diff = (time() - $contact_timestamp) / (60 * 60 * 24);
            if ($days_diff > 14) {
                $attention = true;
            }
        }

        $decrypted_app['attention'] = $attention;
        $open_apps[] = $decrypted_app;
    }
}

// Prepare badge styling for statuses
function get_status_badge_class($status) {
    switch (strtolower(trim($status))) {
        case 'open':
            return 'bg-success';
        case 'interview':
            return 'bg-info text-dark';
        case 'offer':
            return 'bg-warning text-dark';
        case 'job pool':
            return 'bg-secondary';
        default:
            return 'bg-primary';
    }
}

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="h2 fw-bold mb-1">Active Job Applications</h1>
        <p class="text-muted">Here are all your ongoing opportunities. Applications older than 14 days are flagged in red to check in on.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Application
        </a>
    </div>
</div>

<div class="row">
    <?php if (empty($open_apps)): ?>
        <div class="col-12 text-center py-5">
            <div class="card p-5">
                <div class="card-body">
                    <i class="bi bi-folder-x display-1 text-muted mb-3"></i>
                    <h3>No active applications found</h3>
                    <p class="text-muted">You are either caught up, or have not added any job application yet.</p>
                    <a href="add.php" class="btn btn-primary mt-2">Get Started</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($open_apps as $app): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 <?php echo $app['attention'] ? 'app-card-attention' : 'app-card-normal'; ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?php echo get_status_badge_class($app['status']); ?>">
                                <?php echo htmlspecialchars($app['status']); ?>
                            </span>
                            <?php if ($app['attention']): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-clock-history me-1"></i> Overdue (>14d)
                                </span>
                            <?php endif; ?>
                        </div>

                        <h4 class="card-title fw-bold mb-1 text-dark"><?php echo htmlspecialchars($app['job_title']); ?></h4>
                        <h5 class="text-secondary h6 mb-3"><?php echo htmlspecialchars($app['company_name']); ?></h5>

                        <div class="small text-muted mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar-event me-2"></i>
                                <span>Contacted: <strong><?php echo htmlspecialchars($app['date_of_contact']); ?></strong></span>
                            </div>
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-person me-2"></i>
                                <span>Contact: <?php echo htmlspecialchars($app['contact_person'] ?: 'N/A'); ?></span>
                            </div>
                            <?php if (!empty($app['email'])): ?>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-envelope me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($app['email']); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($app['notes'])): ?>
                            <div class="p-3 bg-light rounded-3 small mb-4 flex-grow-1" style="max-height: 120px; overflow-y: auto;">
                                <strong class="d-block mb-1 text-muted">Notes:</strong>
                                <?php echo nl2br(htmlspecialchars($app['notes'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="flex-grow-1"></div>
                        <?php endif; ?>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">ID: #<?php echo $app['id']; ?></span>
                            <a href="list.php?edit_id=<?php echo $app['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Edit / Update
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include 'footer.php';
?>
