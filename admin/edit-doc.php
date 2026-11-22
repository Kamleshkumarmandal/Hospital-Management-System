<?php
session_start();

if (empty($_SESSION['user']) || ($_SESSION['usertype'] ?? '') !== 'a') {
    header('Location: ../login.php');
    exit;
}

include('../connection.php');
$error = '3';
$id = filter_input(INPUT_POST, 'id00', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id && $id > 0) {
    $name = trim($_POST['name'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $oldEmail = trim($_POST['oldemail'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['Tele'] ?? '');
    $specialty = filter_var($_POST['spec'] ?? null, FILTER_VALIDATE_INT);
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['cpassword'] ?? '');

    if ($password === '' || !hash_equals($password, $confirmPassword)) {
        $error = '2';
    } elseif (strlen($password) < 10 || $specialty === false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '3';
    } else {
        $check = $database->prepare('SELECT docid FROM doctor WHERE docemail = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing && (int) $existing['docid'] !== $id) {
            $error = '1';
        } else {
            try {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $updateDoctor = $database->prepare('UPDATE doctor SET docemail = ?, docname = ?, docpassword = ?, docnic = ?, doctel = ?, specialties = ? WHERE docid = ?');
                $updateDoctor->bind_param('sssssii', $email, $name, $passwordHash, $nic, $phone, $specialty, $id);
                $updateDoctor->execute();
                $updateDoctor->close();

                $updateUser = $database->prepare('UPDATE webuser SET email = ? WHERE email = ?');
                $updateUser->bind_param('ss', $email, $oldEmail);
                $updateUser->execute();
                $updateUser->close();
                $error = '4';
            } catch (mysqli_sql_exception $exception) {
                error_log('Doctor account update failed: ' . $exception->getMessage());
                $error = '3';
            }
        }
    }
}

header('Location: doctors.php?action=edit&error=' . urlencode($error) . '&id=' . (int) $id);
exit;
?>