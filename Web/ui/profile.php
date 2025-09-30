<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
/* Body & container */
body {
    background: linear-gradient(135deg, #f0f4f8 0%, #e9ecef 100%);
    font-family: "Segoe UI", Tahoma, sans-serif;
    min-height: 100vh;
}
.profile-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    padding: 30px;
    background: #fff;
    animation: fadeIn 0.5s ease-in-out;
}

/* Flex row for forms */
.profile-forms {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}
.profile-forms > form {
    flex: 1 1 300px; /* mỗi form chiếm tối thiểu 300px */
}

/* Avatar */
.icon-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #28a745;
    margin-bottom: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.icon-preview:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Labels & buttons */
.form-label i {
    margin-right: 8px;
    color: #28a745;
}
.btn-success {
    border-radius: 50px;
    font-weight: 500;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

/* Alerts */
.alert {
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-forms {
        flex-direction: column;
    }
}

/* Fade in animation */
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.95);}
    to {opacity: 1; transform: scale(1);}
}

    </style>
</head>
<body>
    <main>
        <div class="container mt-4">
            <a href="/Web/ui/payment.php" class="btn btn-secondary mb-3">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <div class="card profile-card mx-auto col-md-10">
                <div class="text-center">
                    <img id="user-icon" src="" alt="User Icon" class="icon-preview"> <div class="icon-container"> <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : ''; ?>"> </div>
                    <h2 class="mb-4">User Profile</h2>
                    <div id="profile-icon-alert" class="d-none mt-2"></div>
                </div>
                <div class="profile-forms">
                            <form id="profile-info-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">💾 Update Profile</button>
                    <div id="profile-info-alert" class="alert d-none mt-2"></div>
                </form>

                <hr>

                <form id="profile-password-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label for="old-password">Old Password</label>
                        <input type="password" id="old-password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">💾 Update Password</button>
                    <div id="profile-pass-alert" class="alert d-none mt-2"></div>
                </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="../ui/js/api.js"></script>
    <script src="../ui/js/update_icon.js"></script>
    <script src="../ui/js/update_profile.js"></script>
    <script src="../ui/js/update_pass.js"></script>
</body>
</html>
