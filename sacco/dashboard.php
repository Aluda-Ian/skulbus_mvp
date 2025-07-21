<?php
require_once '../includes/auth.php';
requireRole('sacco');

$user = getCurrentUser();

// Get statistics
$stats = [
    'total_vehicles' => getCount("SELECT COUNT(*) FROM vehicles WHERE sacco_id = ?", [$user['id']]),
    'total_drivers' => getCount("SELECT COUNT(*) FROM drivers WHERE sacco_id = ?", [$user['id']]),
    'active_trips' => getCount("SELECT COUNT(*) FROM trips WHERE sacco_id = ? AND status = 'not_started'", [$user['id']]),
    'total_bookings' => getCount("SELECT COUNT(*) FROM bookings b JOIN trips t ON b.trip_id = t.id WHERE t.sacco_id = ?", [$user['id']])
];

// Get recent trips
$trips = getRows("SELECT t.*, COUNT(b.id) as bookings_count
                  FROM trips t
                  LEFT JOIN bookings b ON t.id = b.trip_id
                  WHERE t.sacco_id = ?
                  GROUP BY t.id
                  ORDER BY t.departure_date DESC LIMIT 5", [$user['id']]);

// Get vehicles that need approval
$pending_vehicles = getRows("SELECT * FROM vehicles WHERE sacco_id = ? AND status = 'pending'", [$user['id']]);

// Get drivers that need verification
$pending_drivers = getRows("SELECT * FROM drivers WHERE sacco_id = ? AND verified = 0", [$user['id']]);

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
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-bus me-2"></i>Vehicles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drivers.php">
                            <i class="fas fa-user-tie me-2"></i>Drivers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trips.php">
                            <i class="fas fa-route me-2"></i>Trips
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-list me-2"></i>Bookings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?php echo $stats['total_vehicles']; ?></h3>
                <p><i class="fas fa-bus me-2"></i>Total Vehicles</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <h3><?php echo $stats['total_drivers']; ?></h3>
                <p><i class="fas fa-user-tie me-2"></i>Total Drivers</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                <h3><?php echo $stats['active_trips']; ?></h3>
                <p><i class="fas fa-route me-2"></i>Active Trips</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
                <h3><?php echo $stats['total_bookings']; ?></h3>
                <p><i class="fas fa-list me-2"></i>Total Bookings</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-route me-2"></i>Recent Trips</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($trips)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                            <p>No trips created yet.</p>
                            <a href="trips.php" class="btn btn-primary">Create Trip</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Route</th>
                                        <th>Date</th>
                                        <th>Bookings</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($trip['route_region']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($trip['departure_date'])); ?></td>
                                            <td><?php echo $trip['bookings_count']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $trip['status'] == 'not_started' ? 'warning' : 'success'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $trip['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="trip-details.php?id=<?php echo $trip['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
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
                            <p>All items approved!</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($pending_vehicles)): ?>
                            <h6>Vehicles Pending Approval</h6>
                            <?php foreach ($pending_vehicles as $vehicle): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-bus me-2"></i>
                                    <?php echo htmlspecialchars($vehicle['plate_number']); ?>
                                    <span class="badge bg-warning">Pending</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($pending_drivers)): ?>
                            <h6>Drivers Pending Verification</h6>
                            <?php foreach ($pending_drivers as $driver): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <?php echo htmlspecialchars($driver['name']); ?>
                                    <span class="badge bg-info">Pending</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>