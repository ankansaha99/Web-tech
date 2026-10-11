<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'AQI');

// Error redirection helper function
function redirectWithError($msg) {
    $_SESSION['error'] = $msg;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle login request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $uname = trim($_POST['uname'] ?? '');
    $upass = $_POST['upass'] ?? '';

    // Validate input
    if (empty($uname)) {
        redirectWithError("Username is required");
    }

    if (empty($upass)) {
        redirectWithError("Password is required");
    }

    // Database connection
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$con) {
        redirectWithError("Database connection failed");
    }

    // Prepared statement for login
    $sql = "SELECT id, Name, Password FROM User WHERE Name = ? LIMIT 1";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        redirectWithError("SQL error");
    }

    mysqli_stmt_bind_param($stmt, "s", $uname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($upass, $row['Password'])) {
            // Login success
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['uname'] = $row['Name'];
            session_regenerate_id(true);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            redirectWithError("Invalid password");
        }
    } else {
        redirectWithError("User not found");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}

// Handle logout request
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_SESSION['uname']) ? 'User Profile' : 'Login'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .profile-info {
            line-height: 1.6;
        }
        .profile-info p {
            padding: 8px;
            margin: 5px 0;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .profile-info strong {
            display: inline-block;
            width: 120px;
        }
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-submit {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .error {
            color: #dc3545;
            padding: 10px;
            margin-bottom: 15px;
            background: #f8d7da;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['uname'])): ?>
        <?php
        // User is logged in - show profile
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$con) {
            die("Database connection failed");
        }

        $sql = "SELECT Name, Email, DOB, Country, Gender, Opinion, AgreeTC FROM User WHERE id = ?";
        $stmt = mysqli_stmt_init($con);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("SQL error");
        }

        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($con);
        ?>
        
        <div class="container">
            <div class="profile-header">
                <h2>User Profile</h2>
                <a href="?logout" class="btn btn-danger">Logout</a>
            </div>
            
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['Name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['DOB']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($user['Country']); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['Gender']); ?></p>
                <p><strong>Opinion:</strong> <?php echo nl2br(htmlspecialchars($user['Opinion'])); ?></p>
                <p><strong>Agreed to Terms:</strong> <?php echo $user['AgreeTC'] ? 'Yes' : 'No'; ?></p>
            </div>
        </div>
    <?php else: ?>
        <!-- User is not logged in - show login form -->
        <div class="login-form">
            <h2 style="text-align: center;">Login</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="uname">Username</label>
                    <input type="text" id="uname" name="uname" required>
                </div>
                <div class="form-group">
                    <label for="upass">Password</label>
                    <input type="password" id="upass" name="upass" required>
                </div>
                <div class="form-submit">
                    <button type="submit" name="login" class="btn">Login</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>