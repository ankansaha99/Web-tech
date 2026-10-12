<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is the confirmation step (user clicked confirm button)
    if (!isset($_POST['confirm'])) {
        // First submission: save inputs to session
        $_SESSION['uname']   = $_POST['uname']   ?? 'Not Provided';
        $_SESSION['umail']   = $_POST['umail']   ?? 'Not Provided';
        $_SESSION['dob']     = $_POST['dob']     ?? 'Not Provided';
        $_SESSION['country'] = $_POST['country'] ?? 'Not Provided';
        $_SESSION['gen']     = $_POST['gen']     ?? 'Not Provided';
        $_SESSION['color']   = $_POST['color']   ?? '#ffffff';
        $_SESSION['opinion'] = $_POST['opinion'] ?? 'Not Provided';
        $_SESSION['agree']   = isset($_POST['tc']) ? 'Yes' : 'No';
        $_SESSION['password'] = $_POST['upass'] ?? '';

        // Set cookie for user's favorite color
        $user_cookie_key = 'bgcolor_' . md5($_SESSION['uname']);
        setcookie($user_cookie_key, $_SESSION['color'], time() + (86400 * 30));

        // Show confirmation page
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Confirm Your Information</title>
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    background-color: rgb(23, 199, 199);
                    margin: 0; padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .container {
                    background-color: #fff;
                    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
                    border-radius: 8px;
                    max-width: 450px;
                    width: 90%;
                    padding: 30px 40px;
                }
                h2 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 25px;
                    font-weight: 700;
                }
                .info-row {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #555;
                }
                .info-label {
                    font-weight: 600;
                    color: #222;
                }
                .info-value {
                    margin-left: 6px;
                }
                .btn-group {
                    display: flex;
                    justify-content: center;
                    gap: 15px;
                    margin-top: 30px;
                }
                button, .btn-link {
                    padding: 12px 25px;
                    font-size: 16px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    text-decoration: none;
                    color: #fff;
                    user-select: none;
                }
                button[type="submit"] {
                    background-color: rgb(9, 73, 141);
                }
                button[type="submit"]:hover {
                    background-color: #0056b3;
                }
                .btn-link {
                    background-color: #6c757d;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
                .btn-link:hover {
                    background-color: #5a6268;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Please Confirm Your Information</h2>
                <div class="info-row"><span class="info-label">Name:</span><span class="info-value">' . htmlspecialchars($_SESSION['uname']) . '</span></div>
                <div class="info-row"><span class="info-label">Email:</span><span class="info-value">' . htmlspecialchars($_SESSION['umail']) . '</span></div>
                <div class="info-row"><span class="info-label">Date of Birth:</span><span class="info-value">' . htmlspecialchars($_SESSION['dob']) . '</span></div>
                <div class="info-row"><span class="info-label">Country:</span><span class="info-value">' . htmlspecialchars($_SESSION['country']) . '</span></div>
                <div class="info-row"><span class="info-label">Gender:</span><span class="info-value">' . htmlspecialchars($_SESSION['gen']) . '</span></div>
                <div class="info-row"><span class="info-label">Favorite Color:</span><span class="info-value" style="color:' . htmlspecialchars($_SESSION['color']) . '; font-weight:bold;">' . htmlspecialchars($_SESSION['color']) . '</span></div>
                <div class="info-row"><span class="info-label">Opinion:</span><div class="info-value" style="white-space: pre-wrap;">' . nl2br(htmlspecialchars($_SESSION['opinion'])) . '</div></div>
                <div class="info-row"><span class="info-label">Agreed to Terms:</span><span class="info-value">' . $_SESSION['agree'] . '</span></div>
                
                <form method="POST" style="margin-top: 20px;">
                    <div class="btn-group">
                        <button type="submit" name="confirm">Confirm</button>
                        <a href="index.html" class="btn-link">Back</a>
                    </div>
                </form>
            </div>
        </body>
        </html>';
        exit;
    } else {
        // Confirmation step: Insert into database

        // Retrieve session data
        $name     = $_SESSION['uname'] ?? '';
        $email    = $_SESSION['umail'] ?? '';
        $dob      = $_SESSION['dob'] ?? '';
        $country  = $_SESSION['country'] ?? '';
        $gender   = $_SESSION['gen'] ?? '';
        $color    = $_SESSION['color'] ?? '#ffffff';
        $opinion  = $_SESSION['opinion'] ?? '';
        $agree    = $_SESSION['agree'] ?? 'No';
        $password = $_SESSION['password'] ?? '';

        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Connect to MySQL (adjust credentials and port as needed)
        $con = mysqli_connect("localhost:3307", "root", "", "aqi");
        if (!$con) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Sanitize data for SQL query
        $name = mysqli_real_escape_string($con, $name);
        $email = mysqli_real_escape_string($con, $email);
        $dob = mysqli_real_escape_string($con, $dob);
        $country = mysqli_real_escape_string($con, $country);
        $gender = mysqli_real_escape_string($con, $gender);
        $opinion = mysqli_real_escape_string($con, $opinion);
        $agree = mysqli_real_escape_string($con, $agree);
        $password_hash = mysqli_real_escape_string($con, $password_hash);

        // Insert into database
        $sql = "INSERT INTO User (Name, Email, DOB, Country, Gender, Opinion, AgreeTC, Password)
                VALUES ('$name', '$email', '$dob', '$country', '$gender', '$opinion', '$agree', '$password_hash')";

        if (mysqli_query($con, $sql)) {
            // Get user's bgcolor cookie, default white
            $user_cookie_key = 'bgcolor_' . md5($email);
            $bgcolor = $_COOKIE[$user_cookie_key] ?? '#ffffff';

            // Show success page
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>Registration Completed</title>
                <style>
                    body {
                        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                        background-color: rgb(13, 131, 170);
                        margin: 0; padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    .container {
                        background-color: ' . htmlspecialchars($bgcolor) . ';
                        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
                        border-radius: 8px;
                        max-width: 450px;
                        width: 90%;
                        padding: 30px 40px;
                        text-align: center;
                        color: #333;
                    }
                    h2 {
                        margin-bottom: 20px;
                        font-weight: 700;
                    }
                    p {
                        font-size: 18px;
                        margin-bottom: 30px;
                    }
                    a button {
                        padding: 12px 30px;
                        font-size: 16px;
                        background-color: #007BFF;
                        color: #fff;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        transition: background-color 0.3s ease;
                    }
                    a button:hover {
                        background-color: #0056b3;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2>Registration Completed</h2>
                    <p>Thank you, ' . htmlspecialchars($name) . '! Your information has been saved.</p>
                    <a href="index.html"><button>Go to Home</button></a>
                </div>
            </body>
            </html>';
        } else {
            echo "Failed to insert data: " . mysqli_error($con);
        }

        mysqli_close($con);
        session_destroy(); // clear session after insert
        exit;
    }
} else {
    echo "No data submitted.";
}
?>