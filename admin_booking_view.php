<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Admin access control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied.');
}

// Get reservation
$res_id = (int)($_GET['res_id'] ?? 0);
if (!$res_id) die('Reservation ID missing.');

// Handle status update if POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['new_status'] ?? '';
    if (in_array($newStatus, ['completed', 'cancelled'])) {
        $upd = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $upd->bind_param('si', $newStatus, $res_id);
        $upd->execute();
        header("Location: admin_booking_view.php?res_id=" . $res_id . "&success=1");
        exit;
    }
}

// Fetch booking
$stmt = $conn->prepare("
    SELECT r.*, c.car_type, c.car_image, u.user_name, 
           u.first_name, u.last_name, u.phone_number, u.user_email, 
           u.address, u.country, u.city, u.pincode
    FROM reservations r
    JOIN cars c ON r.car_id = c.car_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.reservation_id = ?
");
$stmt->bind_param('i', $res_id);
$stmt->execute();
$res = $stmt->get_result();
$booking = $res->fetch_assoc();

if (!$booking) die('Booking not found.');

function statusBadge($status) {
    return match($status) {
        'active'    => '<span class="badge bg-primary">Upcoming</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        default     => '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Booking View</title>
  <?php include('assets/includes/header_link.php') ?>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .section-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 20px; color: #333; }
    .list-group-item { font-size: 1rem; padding: 0.8rem 1.2rem; }
    .badge { font-size: 0.9rem; padding: 0.6em 0.8em; }
    .btn-back, .btn-action { font-size: 1rem; padding: 0.6rem 1.2rem; border-radius: 8px; }
    .action-buttons { gap: 10px; display: flex; justify-content: center; margin-top: 30px; }
  </style>
</head>

<body>
<div class="main-wrapper">

  <!-- Header -->
  <?php include('assets/includes/header.php') ?>
  <!-- /Header -->

  <!-- Breadcrumb -->
  <div class="breadcrumb-bar">
    <div class="container">
      <div class="row align-items-center text-center">
        <div class="col-md-12">
          <h2 class="breadcrumb-title">Booking Details</h2>
        </div>
      </div>
    </div>
  </div>
  <!-- /Breadcrumb -->

  <!-- Page Content -->
  <div class="content py-5">
    <div class="container">

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">Booking updated successfully!</div>
      <?php endif; ?>

      <div class="card p-5">
        <div class="row g-5 align-items-stretch">

          <!-- Car Info -->
          <div class="col-lg-6 d-flex flex-column">
            <h3 class="section-title">Car Information</h3>
            <div class="flex-grow-1 d-flex flex-column">
              <img src="assets/img/cars/<?= htmlspecialchars($booking['car_image']) ?>" class="img-fluid rounded mb-4" alt="Car Image">
              <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Car:</strong> <?= htmlspecialchars($booking['car_type']) ?></li>
                <li class="list-group-item"><strong>Pickup:</strong> <?= htmlspecialchars($booking['pickup_location']) ?> (<?= date('d M Y', strtotime($booking['start_date'])) ?>)</li>
                <li class="list-group-item"><strong>Return:</strong> <?= htmlspecialchars($booking['return_location']) ?> (<?= date('d M Y', strtotime($booking['end_date'])) ?>)</li>
              </ul>
            </div>
          </div>

          <!-- Customer Info -->
          <div class="col-lg-6 d-flex flex-column">
            <h3 class="section-title">Customer Information</h3>
            <div class="flex-grow-1 d-flex flex-column">
              <ul class="list-group mb-3">
                <li class="list-group-item"><strong>User:</strong> <?= htmlspecialchars($booking['user_name']) ?></li>
                <li class="list-group-item"><strong>Driver:</strong> <?= htmlspecialchars($booking['first_name']) ?> <?= htmlspecialchars($booking['last_name']) ?></li>
                <li class="list-group-item"><strong>Mobile:</strong> <?= htmlspecialchars($booking['phone_number']) ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($booking['user_email']) ?></li>
                <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($booking['address']) ?>, <?= htmlspecialchars($booking['city']) ?>, <?= htmlspecialchars($booking['country']) ?> (<?= htmlspecialchars($booking['pincode']) ?>)</li>
              </ul>
            </div>
          </div>

        </div>

        <hr class="my-5">

        <!-- Booking Summary -->
        <div class="row g-5">
          <div class="col-lg-6 d-flex flex-column">
            <h3 class="section-title">Booking Summary</h3>
            <ul class="list-group flex-grow-1 mb-3">
              <li class="list-group-item"><strong>Protection Fee:</strong> $20.00</li>
              <li class="list-group-item"><strong>Deposit:</strong> $500.00</li>
              <li class="list-group-item"><strong>Tax:</strong> 20%</li>
              <li class="list-group-item"><strong>Total:</strong> <span class="text-primary">$<?= number_format($booking['total_price'], 2) ?></span></li>
              <li class="list-group-item">
                <strong>Status:</strong> <?= statusBadge($booking['status']) ?>
              </li>
            </ul>
          </div>

          <!-- Additional Info -->
          <div class="col-lg-6 d-flex flex-column">
            <h3 class="section-title">Additional Information</h3>
            <div class="flex-grow-1">
              <p><?= !empty($booking['additional_info']) ? nl2br(htmlspecialchars($booking['additional_info'])) : '<em>No additional info provided.</em>' ?></p>
            </div>
          </div>

        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button type="button" class="btn btn-success btn-action" onclick="confirmAction('completed')">Mark as Completed</button>
          <button type="button" class="btn btn-danger btn-action" onclick="confirmAction('cancelled')">Cancel Booking</button>
          <a href="admin_dashboard.php" class="btn btn-secondary btn-action">← Back to Dashboard</a>
        </div>

        <!-- Hidden Form for Status Change -->
        <form id="actionForm" method="post" style="display:none;">
          <input type="hidden" name="new_status" id="new_status">
        </form>

      </div>

    </div>
  </div>
  <!-- /Page Content -->

  <!-- Footer -->
  <?php include('assets/includes/footer.php') ?>
  <?php include('assets/includes/footer_link.php') ?>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function confirmAction(status) {
      let actionText = (status === 'completed') ? 'complete' : 'cancel';

      Swal.fire({
        title: 'Are you sure?',
        text: "You are about to " + actionText + " this booking.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'completed' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, ' + actionText + ' it!',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('new_status').value = status;
          document.getElementById('actionForm').submit();
        }
      });
    }
  </script>

</div>
</body>
</html>
