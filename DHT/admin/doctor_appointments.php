<?php
include 'navber.php';
include '../database/config.php';

// Filters
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$appointment_date = isset($_GET['appointment_date']) ? $_GET['appointment_date'] : '';

// Fetch doctors and build doctor–department map
$doctorQuery = $conn->query("SELECT doctor_id, d_name, deptt_id FROM doctor");
$allDoctors = [];
$doctorDeptMap = [];

while ($doc = $doctorQuery->fetch_assoc()) {
    $allDoctors[] = $doc;
    $doctorDeptMap[$doc['doctor_id']] = $doc['deptt_id'];
}

// Fetch all departments
$departmentQuery = $conn->query("SELECT deptt_id, deptt_name FROM department");

// Build filtered SQL query
$sql = "
    SELECT 
        d.d_name AS doctor_name,
        dept.deptt_name AS department_name,
        a.patient_name,
        a.age,
        a.appointment_date,
        a.preferred_time
    FROM appointment a
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN department dept ON a.department_id = dept.deptt_id
    WHERE 1
";

$params = [];
$types = "";

if ($doctor_id > 0) {
    $sql .= " AND d.doctor_id = ?";
    $params[] = $doctor_id;
    $types .= "i";
}
if ($department_id > 0) {
    $sql .= " AND dept.deptt_id = ?";
    $params[] = $department_id;
    $types .= "i";
}
if (!empty($appointment_date)) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $appointment_date;
    $types .= "s";
}
$sql .= " ORDER BY a.appointment_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Appointments</title>
  <link rel="stylesheet" href="../assets/css/doctorAppointment.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Doctor Appointments</h2>
    <button onclick="window.print()" class="btn btn-success no-print">🖨️ Print</button>
  </div>

  <!-- Filter Form -->
  <form method="GET" class="row g-3 mb-4 no-print card-style p-4">
    <div class="col-md-4">
      <label>Doctor</label>
      <select name="doctor_id" id="doctorSelect" class="form-select" onchange="autoSelectDepartment(); fetchDatesForDoctor();">
        <option value="0">-- All Doctors --</option>
        <?php foreach ($allDoctors as $doc): ?>
          <option value="<?= $doc['doctor_id'] ?>" <?= $doc['doctor_id'] == $doctor_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($doc['d_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label>Department</label>
      <select name="department_id" id="departmentSelect" class="form-select">
        <option value="0">-- All Departments --</option>
        <?php while ($dept = $departmentQuery->fetch_assoc()): ?>
          <option value="<?= $dept['deptt_id'] ?>" <?= $dept['deptt_id'] == $department_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($dept['deptt_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label>Date</label>
      <select name="appointment_date" id="appointmentDate" class="form-select">
        <option value="">-- All Dates --</option>
        <?php if (!empty($appointment_date)): ?>
          <option value="<?= $appointment_date ?>" selected><?= $appointment_date ?></option>
        <?php endif; ?>
      </select>
    </div>

    <div class="col-12 d-flex justify-content-between">
      <button type="submit" class="btn btn-primary">🔍 Search</button>
      <a href="doctor_appointments.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Results -->
  <div class="card-style p-4">
  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-striped text-center align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Doctor</th>
          <th>Department</th>
          <th>Patient</th>
          <th>Age</th>
          <th>Date</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
            <td><?= htmlspecialchars($row['department_name']) ?></td>
            <td><?= htmlspecialchars($row['patient_name']) ?></td>
            <td><?= htmlspecialchars($row['age']) ?></td>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars($row['preferred_time']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info text-center">No appointments found for selected criteria.</div>
  <?php endif; ?>
  </div>
</div>

<script>
  var doctorDeptMap = {};
  <?php foreach ($allDoctors as $doc): ?>
    doctorDeptMap['<?= $doc['doctor_id'] ?>'] = '<?= $doc['deptt_id'] ?>';
  <?php endforeach; ?>

  function autoSelectDepartment() {
    var doctorId = document.getElementById('doctorSelect').value;
    var deptSelect = document.getElementById('departmentSelect');
    if (doctorDeptMap[doctorId]) {
      deptSelect.value = doctorDeptMap[doctorId];
    }
  }

  function fetchDatesForDoctor() {
    var doctorId = document.getElementById('doctorSelect').value;
    var dateDropdown = document.getElementById('appointmentDate');

    if (doctorId === "0") {
      dateDropdown.innerHTML = '<option value="">-- All Dates --</option>';
      return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_dates.php?doctor_id=" + doctorId, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        var dates = JSON.parse(xhr.responseText);
        var options = '<option value="">-- All Dates --</option>';
        for (var i = 0; i < dates.length; i++) {
          options += '<option value="' + dates[i] + '">' + dates[i] + '</option>';
        }
        dateDropdown.innerHTML = options;
      }
    };

    xhr.send();
  }

  window.onload = function () {
    var selectedDoctor = document.getElementById('doctorSelect').value;
    if (selectedDoctor > 0) {
      autoSelectDepartment();
      fetchDatesForDoctor();
    }
  };
</script>
</body>
</html>
