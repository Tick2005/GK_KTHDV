<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<footer class="footer bg-white text-dark p-3 d-flex align-items-center justify-content-end border-top">
    <div class="mb-2 mb-md-0">
      &copy; 2025 GK_KTHDV. All rights reserved.
    </div>
  </footer>

</body>
</html>