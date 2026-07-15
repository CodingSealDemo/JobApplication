<?php
// config.php
// Secure configuration options for the Job Tracker application.

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'job_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Registration Key required to sign up
define('REGISTRATION_KEY', 'APPLY-TRACK-2026');

// Secret Key and Method for AES-256-CBC field-level encryption.
// Keep this safe. In a production environment, this should live in an environment variable.
define('ENCRYPTION_KEY', 'd1f5d6778f6ea49a175df3f70df8ab88c039db118a7c29e7019808a54d58097b');
define('ENCRYPTION_METHOD', 'aes-256-cbc');
