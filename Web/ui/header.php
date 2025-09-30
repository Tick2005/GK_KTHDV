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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <style>
        /* Header */
.header {
  background: linear-gradient(90deg, #006666, #009999);
  padding: 15px 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

/* Logo */
.logo {
  width: 50px;
  height: auto;
  transition: transform 0.3s ease;
}
.logo:hover {
  transform: scale(1.1);
}

/* Header title */
.header h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
}

/* Navbar links */
.navbar-nav .nav-link {
  color: #fff !important;
  font-weight: 500;
  padding: 8px 15px;
  border-radius: 8px;
  transition: background-color 0.3s, transform 0.2s;
}
.navbar-nav .nav-link:hover {
  background-color: rgba(255,255,255,0.2);
  transform: translateY(-2px);
}

/* Navbar icons */
.navbar-nav .nav-link i {
  margin-right: 6px;
  font-size: 1.1rem;
}

/* Logout button in navbar */
.navbar-nav form .btn-link {
  padding: 8px 15px;
  border-radius: 8px;
  text-decoration: none;
  color: #fff !important;
  transition: background-color 0.3s, transform 0.2s;
}
.navbar-nav form .btn-link:hover {
  background-color: rgba(255,255,255,0.2);
  transform: translateY(-2px);
}

/* Navbar toggler (mobile) */
.navbar-toggler {
  border: none;
  background: rgba(255,255,255,0.2);
  border-radius: 5px;
  transition: background 0.3s;
}
.navbar-toggler:hover {
  background: rgba(255,255,255,0.35);
}

/* Toggler icon */
.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive adjustments */
@media (max-width: 767px) {
  .navbar-collapse {
    background: linear-gradient(180deg, #006666, #009999);
    padding: 15px;
    border-radius: 8px;
  }
  .navbar-nav .nav-link, .navbar-nav form .btn-link {
    margin-bottom: 5px;
    text-align: center;
  }
}

    </style>
</head>
<body>
    <header class="header bg-primary text-white p-3">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <img src="./img/Logo-TDTU.png" alt="Logo TDTU" class="logo">
                <h2 class="m-0">iBanking TDTU</h2>
            </div>
            <nav class="navbar navbar-expand-lg">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Web/ui/payment.php"><i class="fas fa-money-bill-wave"></i> Payment</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Web/ui/transactions.php"><i class="fas fa-history"></i> Transactions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Web/ui/profile.php"><i class="fas fa-user"></i> Profile</a>
                        </li>
                        <li class="nav-item">
                            <form action="/Web/ui/index.php" method="POST" style="margin: 0;">
                                <button type="submit" class="nav-link btn btn-link text-white"><i class="fas fa-sign-out-alt"></i> Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>