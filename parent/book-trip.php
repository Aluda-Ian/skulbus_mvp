<?php
require_once '../includes/auth.php';
requireRole('parent');

$user = getCurrentUser();
$message = '';

// Get user's students
$students = getRows("SELECT s.*, sc.name as school_name FROM students s 
                     JOIN schools sc ON s.school_id = sc.id 
                     WHERE s.parent_id = ?", [$user['id']]);

// Get available trips
$trips = getRows("SELECT t.*, u.name as sacco_name, v.plate_number, d.name as driver_name
                  FROM trips t
                  JOIN users u ON t.sacco_id = u.id
                  JOIN vehicles v ON t.vehicle_id = v.id
                  JOIN drivers d ON t.driver_id = d.id
                  WHERE t.departure_date >= CURDATE() AND t.available_seats > 0
                  ORDER BY t.departure_date, t.departure_time");

// Handle booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trip_id = $_POST['trip_id'];
    $student_id = $_POST['student_id'];
    
    try {
        // Check if already booked
        $existing = getRow("SELECT id FROM bookings WHERE trip_id = ? AND student_id = ?", [$trip_id, $student_id]);
        if ($existing) {
            throw new Exception("This student is already booked for this trip.");
        }
        
        // Create booking
        $query = "INSERT INTO bookings (trip_id, student_id, parent_id, status) VALUES (?, ?, ?, 'pending')";
        executeQuery($query, [$trip_id, $student_id, $user['id']]);
        
        // Update available seats
        executeQuery("UPDATE trips SET available_seats = available_seats - 1 WHERE id = ?", [$trip_id]);
        
        // Get booking ID
        $booking_id = getRow("SELECT id FROM bookings WHERE trip_id = ? AND student_id = ? AND parent_id = ?", 
                           [$trip_id, $student_id, $user['id']])['id'];
        
        // Add notification
        addNotification($user['id'], "Booking created successfully. Please proceed to payment.", 'success');
        
        header("Location: payment.php?booking_id=$booking_id");
        exit;
        
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-nav">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-2"></i>My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="book-trip.php">
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plus me-2"></i>Book a Trip</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($students)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>You need to register students first before booking trips.
                            <a href="students.php" class="btn btn-sm btn-primary ms-2">Add Student</a>
                        </div>
                    <?php elseif (empty($trips)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No trips available at the moment. Please check back later.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($trips as $trip): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="trip-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($trip['route_region']); ?></h6>
                                            <span class="badge bg-primary">KES <?php echo number_format($trip['cost_per_seat']); ?></span>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Date</small>
                                                <div><?php echo date('M j, Y', strtotime($trip['departure_date'])); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Time</small>
                                                <div><?php echo date('H:i', strtotime($trip['departure_time'])); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Sacco</small>
                                                <div><?php echo htmlspecialchars($trip['sacco_name']); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Available Seats</small>
                                                <div><?php echo $trip['available_seats']; ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Vehicle</small>
                                            <div><?php echo htmlspecialchars($trip['plate_number']); ?> - <?php echo htmlspecialchars($trip['driver_name']); ?></div>
                                        </div>
                                        
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                                            <div class="mb-3">
                                                <select name="student_id" class="form-control form-control-sm" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach ($students as $student): ?>
                                                        <option value="<?php echo $student['id']; ?>">
                                                            <?php echo htmlspecialchars($student['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-1"></i>Book Trip
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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