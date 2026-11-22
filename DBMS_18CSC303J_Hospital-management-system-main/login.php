<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
        
    <title>Login</title>

    
    
</head>
<body>
    <?php
$secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

date_default_timezone_set('Asia/Kolkata');
$_SESSION['date'] = date('Y-m-d');
include('connection.php');

$error = '<label for="promter" class="form-label">&nbsp;</label>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['useremail'] ?? '');
    $password = (string) ($_POST['userpassword'] ?? '');

    $userStmt = $database->prepare('SELECT usertype FROM webuser WHERE email = ? LIMIT 1');
    $userStmt->bind_param('s', $email);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    // Table and column names are selected only from this fixed allow-list.
    $accountTables = [
        'p' => ['patient', 'pemail', 'ppassword', 'patient/index.php'],
        'd' => ['doctor', 'docemail', 'docpassword', 'doctor/index.php'],
        'a' => ['admin', 'aemail', 'apassword', 'admin/index.php'],
    ];

    $authenticated = false;
    if ($userRow && isset($accountTables[$userRow['usertype']]) && $email !== '' && $password !== '') {
        [$table, $emailColumn, $passwordColumn, $redirect] = $accountTables[$userRow['usertype']];
        $lookup = $database->prepare("SELECT {$passwordColumn} AS stored_password FROM {$table} WHERE {$emailColumn} = ? LIMIT 1");
        $lookup->bind_param('s', $email);
        $lookup->execute();
        $account = $lookup->get_result()->fetch_assoc();
        $lookup->close();

        if ($account) {
            $storedPassword = (string) $account['stored_password'];
            $passwordInfo = password_get_info($storedPassword);
            $isModernHash = isset($passwordInfo['algoName']) && $passwordInfo['algoName'] !== 'unknown';
            $passwordMatches = $isModernHash
                ? password_verify($password, $storedPassword)
                : hash_equals($storedPassword, $password);

            if ($passwordMatches) {
                // Upgrade legacy plaintext demo credentials after successful login.
                if (!$isModernHash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upgrade = $database->prepare("UPDATE {$table} SET {$passwordColumn} = ? WHERE {$emailColumn} = ?");
                    $upgrade->bind_param('ss', $newHash, $email);
                    $upgrade->execute();
                    $upgrade->close();
                }

                session_regenerate_id(true);
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = $userRow['usertype'];
                header('Location: ' . $redirect);
                exit;
            }
        }
    }

    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid email or password.</label>';
}
?>





    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
        </table>

    </div>
</center>
</body>
</html>