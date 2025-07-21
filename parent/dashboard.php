<?php
require_once '../includes/auth.php';
requireRole('parent');

$user = getCurrentUser();

// Get user's students
$students = getRows("SELECT s.*, sc.name as school_name FROM students s 
                     JOIN schools sc ON s.school_id = sc.id 
                     WHERE s.parent_id = ?", [$user['id']]);

// Get recent bookings
$bookings = getRows("SELECT b.*, t.route_region, t.departure_date, t.departure_time, 
                            s.name as student_name, u.name as sacco_name, p.status as payment_status
                     FROM bookings b
                     JOIN trips t ON b.trip_id = t.id
                     JOIN students s ON b.student_id = s.id
                     JOIN users u ON t.sacco_id = u.id
                     LEFT JOIN payments p ON b.id = p.booking_id
                     WHERE b.parent_id = ? 
                     ORDER BY b.booking_date DESC LIMIT 10", [$user['id']]);

// Get statistics
$stats = [
    'total_students' => count($students),
    'active_bookings' => getCount("SELECT COUNT(*) FROM bookings WHERE parent_id = ? AND status = 'confirmed'", [$user['id']]),
    'pending_payments' => getCount("SELECT COUNT(*) FROM bookings b 
                                   LEFT JOIN payments p ON b.id = p.booking_id 
                                   WHERE b.parent_id = ? AND (p.status IS NULL OR p.status = 'pending')", [$user['id']]),
    'completed_trips' => getCount("SELECT COUNT(*) FROM bookings WHERE parent_id = ? AND status = 'completed'", [$user['id']])
];

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
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-2"></i>My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="book-trip.php">
                            <i class="fas fa-plus me-2"></i>Book Trip
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-list me-2"></i>My Bookings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p><i class="fas fa-users me-2"></i>Total Students</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <h3><?php echo $stats['active_bookings']; ?></h3>
                <p><i class="fas fa-check-circle me-2"></i>Active Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                <h3><?php echo $stats['pending_payments']; ?></h3>
                <p><i class="fas fa-credit-card me-2"></i>Pending Payments</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
                <h3><?php echo $stats['completed_trips']; ?></h3>
                <p><i class="fas fa-check-double me-2"></i>Completed Trips</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>My Students</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($students)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <p>No students registered yet.</p>
                            <a href="students.php" class="btn btn-primary">Add Student</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>School</th>
                                        <th>Region</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['school_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['destination_region']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="students.php" class="btn btn-outline-primary">Manage Students</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                            <p>No bookings yet.</p>
                            <a href="book-trip.php" class="btn btn-primary">Book Trip</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Route</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['route_region']); ?></td>
                                            <td><?php echo date('M j', strtotime($booking['departure_date'])); ?></td>
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
                        <div class="text-center">
                            <a href="bookings.php" class="btn btn-outline-primary">View All Bookings</a>
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