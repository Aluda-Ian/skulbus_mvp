<?php
require_once '../includes/auth.php';
requireRole('parent');

$user = getCurrentUser();
$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    header('Location: bookings.php');
    exit;
}

// Get booking details
$booking = getRow("SELECT b.*, t.cost_per_seat, t.route_region, t.departure_date, t.departure_time,
                          s.name as student_name, u.name as sacco_name
                   FROM bookings b
                   JOIN trips t ON b.trip_id = t.id
                   JOIN students s ON b.student_id = s.id
                   JOIN users u ON t.sacco_id = u.id
                   WHERE b.id = ? AND b.parent_id = ?", [$booking_id, $user['id']]);

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

// Check if payment already exists
$payment = getRow("SELECT * FROM payments WHERE booking_id = ?", [$booking_id]);

$message = '';
$success = '';

// Handle payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $amount = $booking['cost_per_seat'];
    
    try {
        // Simulate M-Pesa payment
        $transaction_ref = 'TXN' . time() . rand(1000, 9999);
        
        // Create payment record
        $query = "INSERT INTO payments (booking_id, amount, status, payment_method, transaction_ref, payment_date) 
                  VALUES (?, ?, 'completed', 'mpesa', ?, NOW())";
        executeQuery($query, [$booking_id, $amount, $transaction_ref]);
        
        // Update booking status
        executeQuery("UPDATE bookings SET status = 'confirmed' WHERE id = ?", [$booking_id]);
        
        // Add notification
        addNotification($user['id'], "Payment successful! Your booking has been confirmed.", 'success');
        
        $success = "Payment successful! Your booking has been confirmed.";
        
        // Refresh payment data
        $payment = getRow("SELECT * FROM payments WHERE booking_id = ?", [$booking_id]);
        
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-credit-card me-2"></i>Payment for Booking</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Booking Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Student:</strong></td>
                                    <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Route:</strong></td>
                                    <td><?php echo htmlspecialchars($booking['route_region']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['departure_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Time:</strong></td>
                                    <td><?php echo date('H:i', strtotime($booking['departure_time'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sacco:</strong></td>
                                    <td><?php echo htmlspecialchars($booking['sacco_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong>KES <?php echo number_format($booking['cost_per_seat']); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if ($payment && $payment['status'] == 'completed'): ?>
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle me-2"></i>Payment Completed</h6>
                                    <p><strong>Transaction Ref:</strong> <?php echo $payment['transaction_ref']; ?></p>
                                    <p><strong>Amount:</strong> KES <?php echo number_format($payment['amount']); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('M j, Y H:i', strtotime($payment['payment_date'])); ?></p>
                                </div>
                                <div class="text-center">
                                    <a href="bookings.php" class="btn btn-primary">View Bookings</a>
                                    <button class="btn btn-outline-primary" onclick="window.print()">Print Receipt</button>
                                </div>
                            <?php else: ?>
                                <h6>M-Pesa Payment</h6>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">M-Pesa Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="0712345678" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input type="text" class="form-control" 
                                               value="KES <?php echo number_format($booking['cost_per_seat']); ?>" readonly>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-mobile-alt me-2"></i>Pay with M-Pesa
                                    </button>
                                </form>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You will receive an M-Pesa prompt on your phone to complete the payment.
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>