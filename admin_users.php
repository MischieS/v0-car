<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Initialize search and filter parameters
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Build the SQL query with filters
$where_clauses = [];
$params = [];
$types = '';

// Search by name or email
if (!empty($search)) {
    $where_clauses[] = "(user_name LIKE ? OR user_email LIKE ? OR phone_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

// Filter by role
if ($role_filter !== 'all') {
    $where_clauses[] = "user_role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

// Filter by date range
if (!empty($date_from)) {
    $where_clauses[] = "created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if (!empty($date_to)) {
    $where_clauses[] = "created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

// Combine WHERE clauses
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Sorting
$order_by = match($sort_by) {
    'name_asc' => 'user_name ASC',
    'name_desc' => 'user_name DESC',
    'email_asc' => 'user_email ASC',
    'email_desc' => 'user_email DESC',
    'oldest' => 'created_at ASC',
    default => 'created_at DESC' // newest first
};

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Adjust current page if out of bounds
$page = min($page, max(1, $total_pages));

// Calculate offset for pagination
$offset = ($page - 1) * $per_page;

// Main query with pagination
$sql = "
    SELECT user_id, user_name, user_email, user_profile_image, user_role, 
           phone_number, created_at
    FROM users
    $where_sql
    ORDER BY $order_by
    LIMIT $per_page OFFSET $offset
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();

// Build pagination links
function get_pagination_url($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin - Manage Users</title>
  <?php include('assets/includes/header_link.php'); ?>
  <!-- Add Flatpickr for date picker -->
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
          <h2 class="breadcrumb-title">Users</h2>
          <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                <a href="admin_bookings.php">
                  <img src="assets/img/icons/booking-icon.svg" alt="Icon">
                  <span>Bookings</span>
                </a>
              </li>
              <li>
                <a href="admin_cars.php">
                  <img src="assets/img/icons/car-rental-icon.svg" alt="Icon">
                  <span>Cars</span>
                </a>
              </li>
              <li>
                <a href="admin_users.php" class="active">
                  <img src="assets/img/icons/profile-icon.svg" alt="Icon">
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

  <div class="content dashboard-content">
    <div class="container">
      <!-- Header with Add User button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4">Users Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-plus-circle me-2"></i>Add New User
        </button>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <!-- Search and Filter Form -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="get" action="admin_users.php" class="row g-3">
            <!-- Search input -->
            <div class="col-md-4">
              <label class="form-label">Search</label>
              <div class="input-group">
                <input 
                  type="text" 
                  name="search" 
                  class="form-control" 
                  placeholder="Name, Email or Phone" 
                  value="<?= htmlspecialchars($search) ?>"
                >
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>

            <!-- Role filter -->
            <div class="col-md-2">
              <label class="form-label">Role</label>
              <select name="role" class="form-select">
                <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>All Roles</option>
                <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Administrators</option>
                <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Regular Users</option>
              </select>
            </div>

            <!-- Date range filter -->
            <div class="col-md-2">
              <label class="form-label">From Date</label>
              <input 
                type="text" 
                name="date_from" 
                class="form-control date-picker" 
                placeholder="From" 
                value="<?= htmlspecialchars($date_from) ?>"
              >
            </div>
            <div class="col-md-2">
              <label class="form-label">To Date</label>
              <input 
                type="text" 
                name="date_to" 
                class="form-control date-picker" 
                placeholder="To" 
                value="<?= htmlspecialchars($date_to) ?>"
              >
            </div>

            <!-- Sort options -->
            <div class="col-md-2">
              <label class="form-label">Sort By</label>
              <select name="sort_by" class="form-select">
                <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                <option value="email_asc" <?= $sort_by === 'email_asc' ? 'selected' : '' ?>>Email (A-Z)</option>
                <option value="email_desc" <?= $sort_by === 'email_desc' ? 'selected' : '' ?>>Email (Z-A)</option>
              </select>
            </div>

            <!-- Filter actions -->
            <div class="col-12 d-flex justify-content-end">
              <a href="admin_users.php" class="btn btn-secondary me-2">Reset</a>
              <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Results summary -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <span class="text-muted">Showing <?= min($total_records, ($page - 1) * $per_page + 1) ?>-<?= min($total_records, $page * $per_page) ?> of <?= $total_records ?> users</span>
        </div>
        <div>
          <?php if (!empty($search) || $role_filter !== 'all' || !empty($date_from) || !empty($date_to)): ?>
            <span class="badge bg-info">Filtered Results</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Users Table -->
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Profile</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Registered</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($users->num_rows === 0): ?>
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>No users found matching your criteria
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php while($user = $users->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $user['user_id'] ?></td>
                  <td>
                    <?php if ($user['user_profile_image']): ?>
                      <img src="<?= htmlspecialchars($user['user_profile_image']) ?>" alt="Profile" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                    <?php else: ?>
                      <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                        <span><?= strtoupper(substr($user['user_name'], 0, 1)) ?></span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($user['user_name']) ?></td>
                  <td><?= htmlspecialchars($user['user_email']) ?></td>
                  <td><?= htmlspecialchars($user['phone_number'] ?: 'N/A') ?></td>
                  <td>
                    <?php if ($user['user_role'] === 'admin'): ?>
                      <span class="badge bg-danger">Administrator</span>
                    <?php else: ?>
                      <span class="badge bg-info">Regular User</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                  <td class="text-center">
                    <a href="admin_user_edit.php?user_id=<?= urlencode($user['user_id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                      <button onclick="confirmDelete(<?= $user['user_id'] ?>)" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-outline-secondary" disabled title="You cannot delete your own account">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
          <ul class="pagination justify-content-center">
            <!-- Previous page link -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= get_pagination_url($page - 1) ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
            
            <!-- First page link (if not near the beginning) -->
            <?php if ($page > 3): ?>
              <li class="page-item">
                <a class="page-link" href="<?= get_pagination_url(1) ?>">1</a>
              </li>
              <?php if ($page > 4): ?>
                <li class="page-item disabled">
                  <span class="page-link">...</span>
                </li>
              <?php endif; ?>
            <?php endif; ?>
            
            <!-- Page number links -->
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
              <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= get_pagination_url($i) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            
            <!-- Last page link (if not near the end) -->
            <?php if ($page < $total_pages - 2): ?>
              <?php if ($page < $total_pages - 3): ?>
                <li class="page-item disabled">
                  <span class="page-link">...</span>
                </li>
              <?php endif; ?>
              <li class="page-item">
                <a class="page-link" href="<?= get_pagination_url($total_pages) ?>"><?= $total_pages ?></a>
              </li>
            <?php endif; ?>
            
            <!-- Next page link -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= get_pagination_url($page + 1) ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="admin_user_action.php" method="post">
          <input type="hidden" name="action" value="add">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="user_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="user_email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">User Role <span class="text-danger">*</span></label>
                <select name="user_role" class="form-select" required>
                  <option value="user">Regular User</option>
                  <option value="admin">Administrator</option>
                </select>
              </div>
              <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Postal Code</label>
                <input type="text" name="pincode" class="form-control">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create User</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<?php include('assets/includes/footer_link.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Delete confirmation
function confirmDelete(userId) {
  Swal.fire({
    title: 'Delete this user?',
    text: "This action is irreversible!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Delete',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'admin_user_action.php?action=delete&user_id=' + userId;
    }
  })
}

// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
  flatpickr('.date-picker', {
    dateFormat: 'Y-m-d',
    allowInput: true
  });
});
</script>

</body>
</html>
