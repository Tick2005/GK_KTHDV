<?php
session_start();
include 'db.php';

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    session_destroy();
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php?error=Invalid CSRF token");
    exit;
}