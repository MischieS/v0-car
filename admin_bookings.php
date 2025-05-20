<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Initialize filter variables
$whereClause = "";
$params = [];
$paramTypes = "";

// Handle filters
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filter'])) {
    $filters = [];
    
    // Status filter
    if (!empty($_GET['status'])) {
        $filters[] = "r.status = ?";
        $params[] = $_GET['status'];
        $paramTypes .= "s";
    }
    
    // User filter
    if (!empty($_GET['user'])) {
        $filters[] = "u.user_name LIKE ?";
        $params[] = "%" . $_GET['user'] . "%";
        $paramTypes .= "s";
    }
    
    // Car type filter
    if (!empty($_GET['car_type'])) {
        $filters[] = "c.car_type LIKE ?";
        $params[] = "%" . $_GET['car_type'] . "%";
        $paramTypes .= "s";
    }
    
    // Date range filter
    if (!empty($_GET['start_date'])) {
        $filters[] = "r.start_date >= ?";
        $params[] = $_GET['start_date'];
        $paramTypes .= "s";
    }
    
    if (!empty($_GET['end_date'])) {
        $filters[] = "r.end_date <= ?";
        $params[] = $_GET['end_date'];
        $paramTypes .= "s";
    }
    
    // Build WHERE clause
    if (!empty($filters)) {
        $whereClause = " WHERE " . implode(" AND ", $filters);
    }
}

// Fetch all users for the filter dropdown
$userStmt = $conn->prepare("SELECT user_id, user_name FROM users ORDER BY user_name");
$userStmt->execute();
$userResult = $userStmt->get_result();

// Fetch all car types for the filter dropdown
$carStmt = $conn->prepare("SELECT DISTINCT car_type FROM cars ORDER BY car_type");
$carStmt->execute();
$carResult = $carStmt->get_result();

// Fetch all bookings with filters
$query = "
  SELECT 
    r.reservation_id,
    u.user_name,
    u.phone_number,
    c.car_type,
    r.pickup_location,
    r.start_date,
    r.end_date,
    r.status,
    r.created_at
  FROM reservations r
  JOIN cars c ON r.car_id = c.car_id
  JOIN users u ON r.user_id = u.user_id
  $whereClause
  ORDER BY r.created_at DESC
";

$bkStmt = $conn->prepare($query);

// Bind parameters if there are any
if (!empty($params)) {
    $bkStmt->bind_param($paramTypes, ...$params);
}

$bkStmt->execute();
$bkRes = $bkStmt->get_result();

// Helper function for badge
function statusBadge($status) {
    return match($status) {
        'active'    => '<span class="badge bg-primary">Active</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        default     => '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Bookings – Admin</title>
  <?php include('assets/includes/header_link.php'); ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>
<div class="main-wrapper">

  <?php include('assets/includes/header.php'); ?>

  <!-- Breadscrumb Section -->
  <div class="breadcrumb-bar">
    <div class="container">
      <div class="row align-items-center text-center">
        <div class="col-md-12 col-12">
          <h2 class="breadcrumb-title">Bookings</h2>
          <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Bookings</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <!-- /Breadscrumb Section -->

  <!-- Dashboard Menu -->
  <div class="dashboard-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="dashboard-menu">
            <ul class="nav justify-content-center">
              <li>
                <a href="admin_dashboard.php">
                  <img src="assets/img/icons/dashboard-icon.svg" alt="Icon">
                  <span>Dashboard</span>
                </a>
              </li>
              <li>
                <a href="admin_bookings.php" class="active">
                  <img src="assets/img/icons/booking-icon.svg" alt="Icon">
                  <span>Bookings</span>
                </a>
              </li>
              <li>
                <a href="admin_cars.php">
                  <img src="assets/img/icons/car-icon.svg" alt="Icon">
                  <span>Cars</span>
                </a>
              </li>
              <li>
                <a href="admin_users.php">
                  <img src="assets/img/icons/user-icon.svg" alt="Icon">
                  <span>Users</span>
                </a>
              </li>
              <li>
                <a href="user_settings.php">
                  <img src="assets/img/icons/settings-icon.svg" alt="Icon">
                  <span>Settings</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Dashboard Menu -->

  <div class="content dashboard-content py-5">
    <div class="container">
      <h4 class="text-center mb-4">All Bookings</h4>
      
      <!-- Filter Section -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" <?= isset($_GET['status']) && $_GET['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="user" class="form-label">User</label>
              <select name="user" id="user" class="form-select">
                <option value="">All Users</option>
                <?php while ($user = $userResult->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($user['user_name']) ?>" <?= isset($_GET['user']) && $_GET['user'] === $user['user_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['user_name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="car_type" class="form-label">Car Type</label>
              <select name="car_type" id="car_type" class="form-select">
                <option value="">All Car Types</option>
                <?php while ($car = $carResult->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($car['car_type']) ?>" <?= isset($_GET['car_type']) && $_GET['car_type'] === $car['car_type'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($car['car_type']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="start_date" class="form-label">From Date</label>
              <input type="date" class="form-control datepicker" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
            </div>
            
            <div class="col-md-3">
              <label for="end_date" class="form-label">To Date</label>
              <input type="date" class="form-control datepicker" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
            </div>
            
            <div class="col-md-6 d-flex align-items-end">
              <button type="submit" name="filter" value="1" class="btn btn-primary me-2">
                <i class="fas fa-filter me-1"></i> Filter
              </button>
              <a href="admin_bookings.php" class="btn btn-secondary">
                <i class="fas fa-redo me-1"></i> Reset
              </a>
            </div>
          </form>
        </div>
      </div>
      <!-- /Filter Section -->

      <!-- Results Count -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0"><strong><?= $bkRes->num_rows ?></strong> bookings found</p>
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-download me-1"></i> Export
          </button>
          <ul class="dropdown-menu" aria-labelledby="exportDropdown">
            <li><a class="dropdown-item" href="#"><i class="far fa-file-excel me-1"></i> Excel</a></li>
            <li><a class="dropdown-item" href="#"><i class="far fa-file-pdf me-1"></i> PDF</a></li>
            <li><a class="dropdown-item" href="#"><i class="far fa-file-csv me-1"></i> CSV</a></li>
          </ul>
        </div>
      </div>
      <!-- /Results Count -->

      <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-primary">
            <tr>
              <th>Reservation ID</th>
              <th>User</th>
              <th>Car</th>
              <th>Mobile</th>
              <th>Pickup Location</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($bkRes->num_rows > 0): ?>
            <?php while($b = $bkRes->fetch_assoc()): ?>
              <tr>
                <td>#<?= htmlspecialchars($b['reservation_id']) ?></td>
                <td><?= htmlspecialchars($b['user_name']) ?></td>
                <td><?= htmlspecialchars($b['car_type']) ?></td>
                <td><?= htmlspecialchars($b['phone_number']) ?></td>
                <td><?= htmlspecialchars($b['pickup_location']) ?></td>
                <td><?= date('d M Y', strtotime($b['start_date'])) ?></td>
                <td><?= date('d M Y', strtotime($b['end_date'])) ?></td>
                <td><?= statusBadge($b['status']) ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="admin_booking_view.php?res_id=<?= urlencode($b['reservation_id']) ?>" 
                       class="btn btn-outline-primary btn-sm rounded-pill">
                      View
                    </a>

                    <?php if ($b['status'] == 'active'): ?>
                      <button onclick="confirmAction('complete', <?= $b['reservation_id'] ?>)"
                              class="btn btn-outline-success btn-sm rounded-pill">
                        Complete
                      </button>
                      <button onclick="confirmAction('cancel', <?= $b['reservation_id'] ?>)"
                              class="btn btn-outline-danger btn-sm rounded-pill">
                        Cancel
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                  <i class="fas fa-search fa-3x text-muted mb-3"></i>
                  <h5>No bookings found</h5>
                  <p class="text-muted">Try adjusting your filters or create a new booking</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <?php include('assets/includes/footer.php') ?>
</div>

<?php include('assets/includes/footer_link.php') ?>

<script>
// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
  flatpickr(".datepicker", {
    dateFormat: "Y-m-d",
    allowInput: true
  });
});

// SweetAlert2 confirmation for cancel or complete
function confirmAction(action, resId) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You want to " + action + " this booking!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: action === 'cancel' ? '#dc3545' : '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, ' + action
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'admin_booking_action.php?action=' + action + '&res_id=' + resId;
    }
  });
}
</script>

</body>
</html>
