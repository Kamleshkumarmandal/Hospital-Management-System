<?php
session_start();

if (empty($_SESSION['user']) || ($_SESSION['usertype'] ?? '') !== 'p') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booknow'])) {
    header('Location: schedule.php');
    exit;
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['booking_csrf']) || !hash_equals($_SESSION['booking_csrf'], $csrfToken)) {
    http_response_code(403);
    exit('This booking form has expired. Please go back and try again.');
}

include('../connection.php');
date_default_timezone_set('Asia/Kolkata');
$useremail = $_SESSION['user'];
$scheduleId = filter_input(INPUT_POST, 'scheduleid', FILTER_VALIDATE_INT);

if (!$scheduleId || $scheduleId < 1) {
    header('Location: schedule.php');
    exit;
}

$patientStmt = $database->prepare('SELECT pid FROM patient WHERE pemail = ? LIMIT 1');
$patientStmt->bind_param('s', $useremail);
$patientStmt->execute();
$patient = $patientStmt->get_result()->fetch_assoc();
$patientStmt->close();

$scheduleStmt = $database->prepare('SELECT scheduleid, scheduledate, nop FROM schedule WHERE scheduleid = ? LIMIT 1');
$scheduleStmt->bind_param('i', $scheduleId);
$scheduleStmt->execute();
$schedule = $scheduleStmt->get_result()->fetch_assoc();
$scheduleStmt->close();

if (!$patient || !$schedule || $schedule['scheduledate'] < date('Y-m-d')) {
    header('Location: schedule.php');
    exit;
}

$patientId = (int) $patient['pid'];
$duplicateStmt = $database->prepare('SELECT appoid FROM appointment WHERE pid = ? AND scheduleid = ? LIMIT 1');
$duplicateStmt->bind_param('ii', $patientId, $scheduleId);
$duplicateStmt->execute();
$alreadyBooked = $duplicateStmt->get_result()->num_rows > 0;
$duplicateStmt->close();

$countStmt = $database->prepare('SELECT COUNT(*) AS total FROM appointment WHERE scheduleid = ?');
$countStmt->bind_param('i', $scheduleId);
$countStmt->execute();
$countRow = $countStmt->get_result()->fetch_assoc();
$countStmt->close();

$capacity = (int) $schedule['nop'];
$bookedCount = (int) ($countRow['total'] ?? 0);
if ($alreadyBooked || ($capacity > 0 && $bookedCount >= $capacity)) {
    header('Location: schedule.php');
    exit;
}

$appointmentNumber = $bookedCount + 1;
$bookingDate = date('Y-m-d');
$insert = $database->prepare('INSERT INTO appointment (pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)');
$insert->bind_param('iiis', $patientId, $appointmentNumber, $scheduleId, $bookingDate);
$insert->execute();
$insert->close();

unset($_SESSION['booking_csrf']);
header('Location: appointment.php?action=booking-added&id=' . $appointmentNumber . '&titleget=none');
exit;
?>