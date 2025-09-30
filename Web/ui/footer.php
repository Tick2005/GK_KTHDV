<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style> 
/* Body bao bọc main + footer */
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh; /* Chiều cao tối thiểu 100% viewport */
  margin: 0;
}

/* Main chiếm không gian còn lại */
main {
  flex: 1;
}

/* Footer */
footer.footer {
  background-color: #ffffff;
  color: #495057;
  padding: 15px 30px;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  font-size: 0.9rem;
  font-weight: 500;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Hover footer */
footer.footer:hover {
  background-color: #f8f9fa;
  box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.1);
}

footer.footer div {
  margin: 0;
}

@media (max-width: 768px) {
  footer.footer {
    flex-direction: column;
    justify-content: center;
    text-align: center;
    padding: 15px 20px;
  }
  footer.footer div {
    margin-bottom: 5px;
  }
}


    </style>
</head>
<body>
<footer class="footer bg-white text-dark p-3 d-flex align-items-center justify-content-end border-top">
    <div class="mb-2 mb-md-0">
      &copy; 2025 GK_KTHDV. All rights reserved.
    </div>
  </footer>
</body>
</html>