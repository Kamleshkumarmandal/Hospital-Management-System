<?php
session_start();

if (empty($_SESSION['user']) || ($_SESSION['usertype'] ?? '') !== 'a') {
    header('Location: ../login.php');
    exit;
}

include('../connection.php');
$error = '3';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $specialty = filter_var($_POST['spec'] ?? null, FILTER_VALIDATE_INT);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['Tele'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['cpassword'] ?? '');

    if ($password !== '' && strlen($password) >= 10 && hash_equals($password, $confirmPassword) && $specialty !== false && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check = $database->prepare('SELECT email FROM webuser WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = '1';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $doctorInsert = $database->prepare('INSERT INTO doctor (docemail, docname, docpassword, docnic, doctel, specialties) VALUES (?, ?, ?, ?, ?, ?)');
                $doctorInsert->bind_param('sssssi', $email, $name, $passwordHash, $nic, $phone, $specialty);
                $doctorInsert->execute();
                $doctorInsert->close();

                $userType = 'd';
                $userInsert = $database->prepare('INSERT INTO webuser (email, usertype) VALUES (?, ?)');
                $userInsert->bind_param('ss', $email, $userType);
                $userInsert->execute();
                $userInsert->close();
                $error = '4';
            } catch (mysqli_sql_exception $exception) {
                // Existing SQL dump uses MyISAM; clean up manually if needed.
                $cleanup = $database->prepare('DELETE FROM doctor WHERE docemail = ?');
                $cleanup->bind_param('s', $email);
                $cleanup->execute();
                $cleanup->close();
                error_log('Doctor account creation failed: ' . $exception->getMessage());
                $error = '3';
            }
        }
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = '2';
    }
}

header('Location: doctors.php?action=add&error=' . urlencode($error));
exit;
?>