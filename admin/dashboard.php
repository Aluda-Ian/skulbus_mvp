<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Get statistics
$stats = [
    'total_users' => getCount("SELECT COUNT(*) FROM users"),
    'total_trips' => getCount("SELECT COUNT(*) FROM trips"),
    'total_bookings' => getCount("SELECT COUNT(*) FROM bookings"),
    'pending_approvals' => getCount("SELECT COUNT(*) FROM vehicles WHERE status = 'pending'") + 
                          getCount("SELECT COUNT(*) FROM drivers WHERE verified = 0")
];

// Get recent activity
$recent_bookings = getRows("SELECT b.*, s.name as student_name, t.route_region, u.name as parent_name
                           FROM bookings b
                           JOIN students s ON b.student_id = s.id
                           JOIN trips t ON b.trip_id = t.id
                           JOIN users u ON b.parent_id = u.id
                           ORDER BY b.booking_date DESC LIMIT 10");

// Get pending approvals
$pending_vehicles = getRows("SELECT v.*, u.name as sacco_name FROM vehicles v 
                            JOIN users u ON v.sacco_id = u.id 
                            WHERE v.status = 'pending' LIMIT 5");

$pending_drivers = getRows("SELECT d.*, u.name as sacco_name FROM drivers d 
                           JOIN users u ON d.sacco_id = u.id 
                           WHERE d.verified = 0 LIMIT 5");

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-nav">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="approvals.php">
                            <i class="fas fa-check-circle me-2"></i>Approvals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p><i class="fas fa-users me-2"></i>Total Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <h3><?php echo $stats['total_trips']; ?></h3>
                <p><i class="fas fa-route me-2"></i>Total Trips</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                <h3><?php echo $stats['total_bookings']; ?></h3>
                <p><i class="fas fa-list me-2"></i>Total Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #dc3545, #e74c3c);">
                <h3><?php echo $stats['pending_approvals']; ?></h3>
                <p><i class="fas fa-clock me-2"></i>Pending Approvals</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <p>No bookings yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Parent</th>
                                        <th>Route</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['parent_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['route_region']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Pending Approvals</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_vehicles) && empty($pending_drivers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p>No pending approvals!</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($pending_vehicles)): ?>
                            <h6>Vehicles</h6>
                            <?php foreach ($pending_vehicles as $vehicle): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-bus me-2"></i>
                                    <?php echo htmlspecialchars($vehicle['plate_number']); ?>
                                    <br><small><?php echo htmlspecialchars($vehicle['sacco_name']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($pending_drivers)): ?>
                            <h6>Drivers</h6>
                            <?php foreach ($pending_drivers as $driver): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <?php echo htmlspecialchars($driver['name']); ?>
                                    <br><small><?php echo htmlspecialchars($driver['sacco_name']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <a href="approvals.php" class="btn btn-primary">View All Approvals</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>