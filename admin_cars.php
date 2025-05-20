<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/backend/db_connect.php';
if (!($conn instanceof mysqli)) die('Database connection failed.');

function fetchSafe($conn, $sql) {
    $res = $conn->query($sql);
    if (!$res) die("SQL Error: " . $conn->error);
    return $res->fetch_all(MYSQLI_ASSOC);
}

$locations  = fetchSafe($conn, "SELECT location_id, location_name FROM locations ORDER BY location_name");
$brands     = fetchSafe($conn, "SELECT id, brand_name FROM car_brands ORDER BY brand_name");
$categories = fetchSafe($conn, "SELECT id, category_name FROM car_categories ORDER BY category_name");
$fuelTypes  = fetchSafe($conn, "SELECT id, fuel_name FROM fuel_types ORDER BY fuel_name");
$gearTypes  = fetchSafe($conn, "SELECT id, gear_name FROM gear_types ORDER BY gear_name");

$where = [];
$params = [];
$types = '';

if (!empty($_GET['brand_id'])) {
    $where[] = 'c.brand_id = ?';
    $params[] = (int) $_GET['brand_id'];
    $types .= 'i';
}
if (!empty($_GET['location_id'])) {
    $where[] = 'c.location_id = ?';
    $params[] = (int) $_GET['location_id'];
    $types .= 'i';
}

$sql = "
    SELECT c.*, l.location_name, cb.brand_name, cm.model_name, cc.category_name,
           ft.fuel_name, gt.gear_name
    FROM cars c
    LEFT JOIN locations l ON c.location_id = l.location_id
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    LEFT JOIN car_models cm ON c.model_id = cm.id
    LEFT JOIN car_categories cc ON c.category_id = cc.id
    LEFT JOIN fuel_types ft ON c.fuel_type_id = ft.id
    LEFT JOIN gear_types gt ON c.gear_type_id = gt.id
";

if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY c.car_id DESC';

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$cars = $stmt->get_result();

include 'assets/includes/header_link.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin — Manage Cars</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include 'assets/includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
  <div class="container">
    <div class="row align-items-center text-center">
      <div class="col-md-12 col-12">
        <h2 class="breadcrumb-title">Cars</h2>
        <nav aria-label="breadcrumb" class="page-breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cars</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Dashboard Nav -->
<div class="dashboard-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-12">
        <div class="dashboard-menu">
          <ul class="nav justify-content-center">
            <li><a href="admin_dashboard.php"><img src="assets/img/icons/dashboard-icon.svg"><span>Dashboard</span></a></li>
            <li><a href="admin_bookings.php"><img src="assets/img/icons/booking-icon.svg"><span>Bookings</span></a></li>
            <li><a href="admin_cars.php" class="active"><img src="assets/img/icons/payment-icon.svg"><span>Cars</span></a></li>
            <li><a href="admin_users.php"><img src="assets/img/icons/payment-icon.svg"><span>Users</span></a></li>
            <li><a href="user_settings.php"><img src="assets/img/icons/settings-icon.svg"><span>Settings</span></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="container mt-4">
  <h2 class="text-center mb-4">Manage Cars</h2>

  <!-- Filter -->
  <form class="row g-2 mb-4" method="get">
    <div class="col-md-3">
      <select name="brand_id" class="form-select">
        <option value="">All Brands</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['id'] ?>" <?= $_GET['brand_id'] ?? '' == $b['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['brand_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="location_id" class="form-select">
        <option value="">All Locations</option>
        <?php foreach ($locations as $l): ?>
          <option value="<?= $l['location_id'] ?>" <?= $_GET['location_id'] ?? '' == $l['location_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($l['location_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3"><button class="btn btn-outline-primary w-100">Filter</button></div>
    <div class="col-md-3"><a href="admin_cars.php" class="btn btn-outline-secondary w-100">Reset</a></div>
  </form>

  <!-- Add Button -->
  <div class="mb-3 text-end">
    <button class="btn btn-primary" onclick="openAddModal()">
      <i class="bi bi-plus-lg"></i> Add New Car
    </button>
  </div>

  <!-- Car Table -->
  <div class="table-responsive mb-5">
    <table class="table table-hover text-center align-middle">
      <thead class="table-primary">
        <tr>
          <th>ID</th><th>Brand</th><th>Model</th><th>Price</th><th>Fuel</th><th>Gear</th>
          <th>Year</th><th>Class</th><th>Location</th><th>Image</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($car = $cars->fetch_assoc()): ?>
        <tr>
          <td><?= $car['car_id'] ?></td>
          <td><?= htmlspecialchars($car['brand_name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($car['model_name'] ?? '-') ?></td>
          <td>$<?= number_format($car['car_price_perday'], 2) ?></td>
          <td><?= htmlspecialchars($car['fuel_name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($car['gear_name'] ?? '-') ?></td>
          <td><?= $car['year'] ?></td>
          <td><?= htmlspecialchars($car['car_class']) ?></td>
          <td><?= htmlspecialchars($car['location_name']) ?></td>
          <td>
            <?php if ($car['car_image']): ?>
              <img src="assets/img/cars/<?= htmlspecialchars($car['car_image']) ?>" height="50" class="rounded">
            <?php else: ?>
              <span class="text-muted">No Image</span>
            <?php endif; ?>
          </td>
          <td>
            <?php $carJson = htmlspecialchars(json_encode($car), ENT_QUOTES); ?>
            <button class="btn btn-sm btn-outline-warning" onclick='openEditModal(<?= $carJson ?>)'><i class="bi bi-pencil-fill"></i></button>
            <a href="backend/process_car.php?action=delete&id=<?= $car['car_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this car?');"><i class="bi bi-trash-fill"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="carModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="backend/process_car.php" enctype="multipart/form-data" class="modal-content" id="carForm">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalTitle">Add Car</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="car_id" id="carId">
        <div class="row">
          <div class="col-md-6 mb-3"><label>Car Type</label><input type="text" name="car_type" id="carType" class="form-control" required></div>
          <div class="col-md-6 mb-3">
            <label>Brand</label>
            <select name="brand_id" id="carBrand" class="form-select" required>
              <option value="">Select brand</option>
              <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Model</label>
            <select name="model_id" id="carModel" class="form-select" required><option value="">Select model</option></select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Category</label>
            <select name="category_id" id="carCategory" class="form-select" required>
              <option value="">Select category</option>
              <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Fuel Type</label>
            <select name="fuel_type_id" id="carFuel" class="form-select" required>
              <option value="">Select fuel</option>
              <?php foreach ($fuelTypes as $f): ?><option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['fuel_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Gear Type</label>
            <select name="gear_type_id" id="carGear" class="form-select" required>
              <option value="">Select gear</option>
              <?php foreach ($gearTypes as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['gear_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3"><label>Price/Day</label><input type="number" step="0.01" name="car_price_perday" id="carPrice" class="form-control" required></div>
          <div class="col-md-4 mb-3">
            <label>Car Class</label>
            <select name="car_class" id="carClass" class="form-select" required>
              <option value="">Select class</option>
              <option value="Economy">Economy</option>
              <option value="Standard">Standard</option>
              <option value="Premium">Premium</option>
            </select>
          </div>
          <div class="col-md-4 mb-3"><label>Year</label><input type="number" name="year" id="carYear" class="form-control" min="1900" max="<?= date('Y') ?>" required></div>
          <div class="col-md-4 mb-3">
            <label>Location</label>
            <select name="location_id" id="carLocation" class="form-select" required>
              <option value="">Select location</option>
              <?php foreach ($locations as $loc): ?><option value="<?= $loc['location_id'] ?>"><?= htmlspecialchars($loc['location_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-12 mb-3">
            <label>Car Images</label>
            <input type="file" name="car_images[]" id="carImages" class="form-control" accept="image/*" multiple required>
          </div>
          <div id="previewGallery" class="d-flex flex-wrap gap-2 mb-3"></div>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('carImages').addEventListener('change', function (e) {
  const previewGallery = document.getElementById('previewGallery');
  previewGallery.innerHTML = '';
  Array.from(e.target.files).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function (event) {
      const img = document.createElement('img');
      img.src = event.target.result;
      img.className = 'rounded border';
      img.style.height = '80px';
      img.style.objectFit = 'cover';
      img.style.marginRight = '6px';
      previewGallery.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});

document.getElementById('carBrand').addEventListener('change', function () {
  const brandId = this.value;
  const modelSelect = document.getElementById('carModel');
  modelSelect.innerHTML = '<option>Loading...</option>';
  fetch('backend/get_models.php?brand_id=' + brandId)
    .then(res => res.json())
    .then(data => {
      modelSelect.innerHTML = '<option value="">Select model</option>';
      data.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.textContent = m.model_name;
        modelSelect.appendChild(opt);
      });
    });
});

function openAddModal() {
  document.getElementById('carForm').reset();
  document.getElementById('modalTitle').textContent = 'Add Car';
  document.getElementById('formAction').value = 'add';
  document.getElementById('carId').value = '';
  document.getElementById('previewGallery').innerHTML = '';
  new bootstrap.Modal(document.getElementById('carModal')).show();
}

function openEditModal(car) {
  document.getElementById('modalTitle').textContent = 'Edit Car';
  document.getElementById('formAction').value = 'edit';
  document.getElementById('carId').value = car.car_id;
  document.getElementById('carBrand').value = car.brand_id;
  document.getElementById('carCategory').value = car.category_id;
  document.getElementById('carFuel').value = car.fuel_type_id;
  document.getElementById('carGear').value = car.gear_type_id;
  document.getElementById('carPrice').value = car.car_price_perday;
  document.getElementById('carYear').value = car.year;
  document.getElementById('carLocation').value = car.location_id;
  document.getElementById('carType').value = car.car_type;
  document.getElementById('carClass').value = car.car_class;

  fetch('backend/get_models.php?brand_id=' + car.brand_id)
    .then(res => res.json())
    .then(data => {
      const modelSelect = document.getElementById('carModel');
      modelSelect.innerHTML = '<option value="">Select model</option>';
      data.forEach(model => {
        const opt = document.createElement('option');
        opt.value = model.id;
        opt.textContent = model.model_name;
        if (model.id == car.model_id) opt.selected = true;
        modelSelect.appendChild(opt);
      });
    });

  document.getElementById('carImages').value = '';
  document.getElementById('previewGallery').innerHTML = '';
  new bootstrap.Modal(document.getElementById('carModal')).show();
}

<script>
document.getElementById('carImages').addEventListener('change', function (e) {
  const previewGallery = document.getElementById('previewGallery');
  previewGallery.innerHTML = ''; // clear previous

  const files = e.target.files;
  if (!files.length) return;

  Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = function (event) {
      const img = document.createElement('img');
      img.src = event.target.result;
      img.className = 'rounded border';
      img.style.height = '80px';
      img.style.objectFit = 'cover';
      img.style.marginRight = '6px';
      previewGallery.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});
</script>

</script>

<?php include 'assets/includes/footer.php'; ?>
<?php include 'assets/includes/footer_link.php'; ?>
</body>
</html>
