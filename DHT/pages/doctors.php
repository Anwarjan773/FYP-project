<?php
include '../database/config.php';

// Fetch departments for filter dropdown
$departments = mysqli_query($conn, "SELECT * FROM department") or die(mysqli_error($conn));

// Fetch all doctors along with their department names and IDs
$doctors = mysqli_query($conn, "
  SELECT doctor.*, department.deptt_name, department.deptt_id
  FROM doctor 
  JOIN department ON doctor.deptt_id = department.deptt_id
") or die(mysqli_error($conn));
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Our Doctors | Doctors Hospital Timergara</title>
  <meta name="description" content="Meet our expert medical team at Doctors Hospital Timergara - Specialized healthcare professionals" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/doctors.css" />
<link rel="stylesheet" href="../assets/css/style.css">
</head>
 <body>


<?php include '../includes/navbar.php'; ?>

<section  class="page-hero  text-white py-5">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Our Medical Specialists</h1>
    <p class="lead mb-4">Experienced professionals dedicated to your health</p>
    <div class="d-flex justify-content-center gap-3">
      <a href="appointments.php" class="btn btn-light btn-lg px-4"><i class="fas fa-calendar-check me-2"></i>Book Appointment</a>
      <a href="departments.php" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-procedures me-2"></i>View Departments</a>
    </div>
  </div>
</section>

<!-- Filters -->
<section class="py-4 bg-light">
  <div class="container">
    <div class="row g-3 justify-content-center">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name or specialization...">
      </div>
      <div class="col-md-4">
        <select id="departmentFilter" class="form-select">
          <option value="">All Departments</option>
          <?php while ($dept = mysqli_fetch_assoc($departments)) : ?>
            <option value="<?= strtolower($dept['deptt_name']) ?>"><?= htmlspecialchars($dept['deptt_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
  </div>
</section>

<!-- Doctors Grid -->
<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-5">Meet Our Doctors</h2>
    <div class="row g-4" id="doctorsGrid">
      <?php if (mysqli_num_rows($doctors) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($doctors)) : ?>
          <div class="col-lg-4 col-md-6 doctor-card" data-name="<?= strtolower($row['d_name']) ?>" data-specialty="<?= strtolower($row['specialization']) ?>" data-department="<?= strtolower($row['deptt_name']) ?>">
            <div class="card h-100 shadow-sm border-0">
              <div class="position-relative">
                <img src="../assets/images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['d_name']) ?>" />
                <div class="card-badge bg-primary text-white"><?= htmlspecialchars($row['specialization']) ?></div>
              </div>
              <div class="card-body text-center">
                <h3 class="h5 card-title"><?= htmlspecialchars($row['d_name']) ?></h3>
                <p class="text-muted mb-1"><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($row['time']) ?></p>
                <p class="text-muted mb-2"><i class="fas fa-building me-1"></i> <?= htmlspecialchars($row['deptt_name']) ?></p>

                <!-- View Full Profile Modal Trigger -->
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal<?= $row['doctor_id'] ?>">
                  View Full Profile
                </button>
              </div>
            </div>
          </div>

          <!-- Doctor Modal -->
          <div class="modal fade" id="modal<?= $row['doctor_id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['doctor_id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="modalLabel<?= $row['doctor_id'] ?>">Doctor Profile</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                  <div class="col-md-4 text-center">
                    <img src="../assets/images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['d_name']) ?>" class="img-fluid rounded mb-4" />
                    <a href="appointments.php?doctor_id=<?= $row['doctor_id'] ?>&deptt_id=<?= $row['deptt_id'] ?>" class="btn btn-primary">
                      <i class="fas fa-calendar-check me-2"></i>Book Appointment
                    </a>
                  </div>
                  <div class="col-md-8">
                    <h3><?= htmlspecialchars($row['d_name']) ?></h3>
                    <p>
                      <span class="badge bg-primary"><?= htmlspecialchars($row['specialization']) ?></span>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['time']) ?></span>
                    </p>
                    <h5>About</h5>
                    <p>
                      <?= htmlspecialchars($row['d_name']) ?> is specialized in <?= htmlspecialchars($row['specialization']) ?>. Contact: <?= htmlspecialchars($row['d_contact']) ?>
                    </p>
                    <h5>Education</h5>
                    <p>Not specified</p>
                    <h5>Availability</h5>
                    <p><?= htmlspecialchars($row['time']) ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center text-danger">No doctors found.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/JS/doctors.js"></script>
</body>
</html>
