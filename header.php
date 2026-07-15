<?php
// header.php
// Beautiful modern layout header styled with Bootstrap 5.

require_once 'auth.php';
require_login(); // Force auth for pages that load this header

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobTracker - Track Your Career Applications</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom modern styling overrides -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif;
            color: #333333;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.04);
        }
        .nav-link.active {
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);
            background-color: #ffffff;
        }
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
            border-radius: 8px;
        }
        .app-card-attention {
            border-left: 5px solid #dc3545 !important;
            background-color: #fff8f8;
        }
        .app-card-normal {
            border-left: 5px solid #0d6efd !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-briefcase-fill me-2 text-primary"></i>
            <span>JobTracker</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door-fill me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'add.php') ? 'active' : ''; ?>" href="add.php">
                        <i class="bi bi-plus-circle-fill me-1"></i> New Application
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'list.php') ? 'active' : ''; ?>" href="list.php">
                        <i class="bi bi-search me-1"></i> All Applications
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 text-white-50">
                    <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Log Out
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4 my-2">
