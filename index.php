<?php
require_once 'includes/auth.php';

// Redirect based on user role
if (isLoggedIn()) {
    $user = getCurrentUser();
    switch ($user['role']) {
        case 'parent':
            header('Location: parent/dashboard.php');
            break;
        case 'sacco':
            header('Location: sacco/dashboard.php');
            break;
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary">
                    <i class="fas fa-bus me-3"></i>SkulBus
                </h1>
                <p class="lead">Safe, Reliable, and Convenient School Transport for Kenyan Students</p>
            </div>
            
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h4>For Parents</h4>
                            <p>Book safe transport for your children, track journeys, and make secure payments online.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-bus fa-3x text-success mb-3"></i>
                            <h4>For Saccos</h4>
                            <p>Manage your fleet, register trips, and connect with students efficiently.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-school fa-3x text-warning mb-3"></i>
                            <h4>For Schools</h4>
                            <p>Monitor student transport, ensure safety compliance, and generate reports.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="login.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a href="register.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>SkulBus</h5>
                <p>Making school transport safer and more efficient for Kenyan students.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; 2024 SkulBus. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>