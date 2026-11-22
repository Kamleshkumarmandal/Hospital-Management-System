<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
$_SESSION['date'] = date('Y-m-d');

if (empty($_SESSION['personal']) || !is_array($_SESSION['personal'])) {
    header('Location: signup.php');
    exit;
}

include('connection.php');
$error = '<label for="promter" class="form-label">&nbsp;</label>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personal = $_SESSION['personal'];
    $fname = trim($personal['fname'] ?? '');
    $lname = trim($personal['lname'] ?? '');
    $name = trim($fname . ' ' . $lname);
    $address = trim($personal['address'] ?? '');
    $nic = trim($personal['nic'] ?? '');
    $dob = $personal['dob'] ?? '';
    $email = trim($_POST['newemail'] ?? '');
    $tele = trim($_POST['tele'] ?? '');
    $newpassword = (string) ($_POST['newpassword'] ?? '');
    $cpassword = (string) ($_POST['cpassword'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '<label class="form-label" style="color:#ff3e3e;text-align:center;">Enter a valid email address.</label>';
    } elseif (strlen($newpassword) < 10) {
        $error = '<label class="form-label" style="color:#ff3e3e;text-align:center;">Use a password with at least 10 characters.</label>';
    } elseif (!hash_equals($newpassword, $cpassword)) {
        $error = '<label class="form-label" style="color:#ff3e3e;text-align:center;">Passwords do not match.</label>';
    } else {
        $check = $database->prepare('SELECT email FROM webuser WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = '<label class="form-label" style="color:#ff3e3e;text-align:center;">An account already exists for this email address.</label>';
        } else {
            $passwordHash = password_hash($newpassword, PASSWORD_DEFAULT);
            try {
                $patientInsert = $database->prepare('INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $patientInsert->bind_param('sssssss', $email, $name, $passwordHash, $address, $nic, $dob, $tele);
                $patientInsert->execute();
                $patientInsert->close();

                $userType = 'p';
                $webUserInsert = $database->prepare('INSERT INTO webuser (email, usertype) VALUES (?, ?)');
                $webUserInsert->bind_param('ss', $email, $userType);
                $webUserInsert->execute();
                $webUserInsert->close();

                session_regenerate_id(true);
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = 'p';
                $_SESSION['username'] = $fname;
                unset($_SESSION['personal']);
                header('Location: patient/index.php');
                exit;
            } catch (mysqli_sql_exception $exception) {
                // The legacy SQL dump uses MyISAM; compensate manually if the
                // second insert fails because MyISAM does not support transactions.
                $cleanup = $database->prepare('DELETE FROM patient WHERE pemail = ?');
                $cleanup->bind_param('s', $email);
                $cleanup->execute();
                $cleanup->close();
                error_log('Patient account creation failed: ' . $exception->getMessage());
                $error = '<label class="form-label" style="color:#ff3e3e;text-align:center;">Could not create the account. Please try again.</label>';
            }
        }
    }
} else {
    $error = '<label for="promter" class="form-label">&nbsp;</label>';
}
?>


    <center>
    <div class="container">
        <table border="0" style="width: 69%;">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">It's Okey, Now Create User Account.</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="newemail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                </td>
                
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="tele" class="form-label">Mobile Number: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="tel" name="tele" class="input-text"  placeholder="ex: 0712345678" pattern="[0]{1}[0-9]{9}" >
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="newpassword" class="form-label">Create New Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="newpassword" class="input-text" placeholder="New Password" minlength="10" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="cpassword" class="form-label">Conform Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" minlength="10" required>
                </td>
            </tr>
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
</body>
</html>