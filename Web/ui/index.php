<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style> 
/* Card login */
.card-login {
  display: flex;
  min-width: 500px;
  max-width: 900px;
  border-radius: 20px;
  overflow: hidden;
  background: linear-gradient(to right, #006666, #ffffff);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-login:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
}

/* Left side */
.left {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  color: #ffffff;
  transition: background 0.3s ease;
  min-height: 100%;
}

/* Logo and title inside left */
.left .logo {
  width: 120px;
  margin-bottom: 15px;
  transition: transform 0.3s ease;
}
.left .logo:hover {
  transform: scale(1.1);
}
.left .title {
  font-size: 1.8rem;
  font-weight: 700;
  text-align: center;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
}

/* Right side (form) */
.right {
  padding: 30px;
  flex: 1;
}
.right form .form-group label {
  font-weight: 500;
  font-size: 0.95rem;
    color: #ffffff;
}
.right form .form-control {
  border-radius: 10px;
  border: 1px solid #d1d5db;
  padding: 12px 16px;
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s, transform 0.2s;
}
.right form .form-control:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  transform: translateY(-1px);
}
.right form .form-control:hover {
  border-color: #93c5fd;
}
.right form button {
  border-radius: 50px;
  padding: 12px 0;
  font-weight: 600;
  background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%);
  color: #fff;
  border: none;
  transition: transform 0.2s, box-shadow 0.2s, background 0.3s;
}

.right form button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  background: linear-gradient(90deg, #218838 0%, #1c7430 100%);
}


/* Responsive */
@media (max-width: 768px) {
  .card-login {
    flex-direction: column;
    min-width: auto;
  }
  .left, .right {
    padding: 20px;
  }
  .left {
    border-radius: 20px 20px 0 0;
  }
  .right {
    border-radius: 0 0 20px 20px;
  }
}


    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card-login shadow-lg p-4 login-container">
            <div class="row align-items-center">
                <div class="col-md-6 left">
                    <div class="logo-title">
                        <img src="./img/Logo-TDTU.png" alt="Logo TDTU" class="logo">
                        <h2 class="title">iBanking TDTU</h2>
                    </div>
                </div>
                <div class="col-md-6 right">
                    <form id="login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username" autocomplete="username">
                        </div>
                        <div class="form-group mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password" autocomplete="current-password">
                        </div>
                        <div id="error-alert" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../ui/js/api.js"></script>
    <script src="../ui/js/login.js"></script>
</body>
</html>