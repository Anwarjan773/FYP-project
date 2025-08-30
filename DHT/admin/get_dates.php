<?php
include '../database/config.php';

if (isset($_GET['doctor_id']) && is_numeric($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']);
    $stmt = $conn->prepare("SELECT DISTINCT appointment_date FROM appointment WHERE doctor_id = ? ORDER BY appointment_date DESC");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['appointment_date'];
    }

    header('Content-Type: application/json');
    echo json_encode($dates);
}
?>
