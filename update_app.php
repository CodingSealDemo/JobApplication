<?php
// update_app.php
// Secure endpoint to update an application. Ensures parameters are validated and updated records are encrypted.

require_once 'auth.php';
require_once 'db.php';
require_once 'crypto.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $job_title = trim($_POST['job_title'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_of_contact = trim($_POST['date_of_contact'] ?? '');
    $status = trim($_POST['status'] ?? 'Open');
    $notes = trim($_POST['notes'] ?? '');

    // Default current date if empty
    if (empty($date_of_contact)) {
        $date_of_contact = date('Y-m-d');
    }

    if ($id <= 0 || empty($job_title) || empty($company_name)) {
        // Validation error
        header("Location: list.php?error=validation");
        exit();
    }

    try {
        // Double check authorization: make sure application belongs to the logged-in user
        $check_stmt = $pdo->prepare("SELECT id FROM applications WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$id, $_SESSION['user_id']]);
        if (!$check_stmt->fetch()) {
            header("Location: list.php?error=unauthorized");
            exit();
        }

        // Encrypt the updated fields
        $enc_job_title = encrypt_field($job_title);
        $enc_company_name = encrypt_field($company_name);
        $enc_contact_person = encrypt_field($contact_person);
        $enc_email = encrypt_field($email);
        $enc_date_of_contact = encrypt_field($date_of_contact);
        $enc_status = encrypt_field($status);
        $enc_notes = encrypt_field($notes);

        $update_stmt = $pdo->prepare("
            UPDATE applications
            SET job_title = ?, company_name = ?, contact_person = ?, email = ?, date_of_contact = ?, status = ?, notes = ?
            WHERE id = ? AND user_id = ?
        ");

        $update_stmt->execute([
            $enc_job_title,
            $enc_company_name,
            $enc_contact_person,
            $enc_email,
            $enc_date_of_contact,
            $enc_status,
            $enc_notes,
            $id,
            $_SESSION['user_id']
        ]);

        header("Location: list.php?updated=1");
        exit();

    } catch (PDOException $e) {
        die("An error occurred while updating the application: " . $e->getMessage());
    }
} else {
    header("Location: list.php");
    exit();
}
