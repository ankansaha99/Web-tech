 <?php
session_start();
 
$username = $_SESSION['uname'] ?? null;
 
if (!$username) {
    // Not logged in, redirect or show message
    header("Location: index.html");
    exit();
}
 
// Connect to DB
$con = mysqli_connect("localhost", "root", "", "AQI");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
 
// Get user-specific background color cookie
$cookieKey = 'bgcolor_' . md5($username);
$bgColor = $_COOKIE[$cookieKey] ?? '#ffffff';
 
// Escape username
$username_safe = mysqli_real_escape_string($con, $username);
 
// Fetch user details
$sql_user = "SELECT Name, Email, DOB, Country, Gender, Opinion, AgreeTC FROM User WHERE Name = '$username_safe' LIMIT 1";
$result_user = mysqli_query($con, $sql_user);
 
if (!$result_user || mysqli_num_rows($result_user) == 0) {
    $user = null;
} else {
    $user = mysqli_fetch_assoc($result_user);
}
 
// Get selected cities from session for AQI table
if (isset($_SESSION['selected_cities']) && is_array($_SESSION['selected_cities']) && count($_SESSION['selected_cities']) > 0) {
    $cities = $_SESSION['selected_cities'];
} else {
    echo "No cities selected.";
    exit();
}
 
$escaped_cities = array_map(function($city) use ($con) {
    return "'" . mysqli_real_escape_string($con, $city) . "'";
}, $cities);
$city_list = implode(',', $escaped_cities);
 
$sql_cities = "SELECT City, Country, AQI FROM Info WHERE City IN ($city_list)";
$result_cities = mysqli_query($con, $sql_cities);
if (!$result_cities) {
    die("Query failed: " . mysqli_error($con));
}
 
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Selected City AQI & User Profile</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 40px;
        background: <?= htmlspecialchars($bgColor) ?>;
    }
    h2 {
        text-align: center;
        margin-bottom: 30px;
    }
    table {
        margin: 20px auto 50px auto;
        border-collapse: collapse;
        width: 50%;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 18px;
        border: 1px solid #ddd;
        text-align: center;
    }
    th {
        background: black;
        color: white;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
    /* Profile styles */
    .profile-info {
        max-width: 400px;
        margin: 0 auto 50px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background: #f9f9f9;
    }
    .profile-info dt {
        font-weight: bold;
        margin-top: 10px;
    }
    .profile-info dd {
        margin: 0 0 10px 0;
    }
    /* Top-right user menu */
    .user-menu {
        position: absolute;
        top: 20px;
        right: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-menu img {
        width: 20px;
        height: 20px;
        vertical-align: middle;
        margin-right: 5px;
    }
    .user-menu span {
        color: #333;
        font-weight: bold;
        vertical-align: middle;
    }
    .user-menu a {
        text-decoration: none;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
    }
    .user-menu a.profile-link {
        background-color: #007BFF;
    }
    .user-menu a.logout-link {
        background-color: #333;
    }
</style>
</head>
<body>
 
<!-- Top right user menu -->
<div class="user-menu">
    <img src="Logo.jpg" alt="User Icon">
    <span><?php echo htmlspecialchars($username); ?></span>
    <a class="profile-link" href="profile.php?return=showaqi.php">Profile</a>
    <a class="logout-link" href="logout.php">Logout</a>
</div>
 
 
 
<!-- AQI Table -->
<h2>Air Quality Index (AQI) of Selected Cities</h2>
<table>
    <thead>
        <tr>
            <th>City</th>
            <th>Country</th>
            <th>AQI</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $displayed = 0;
        if (mysqli_num_rows($result_cities) > 0) {
            while ($row = mysqli_fetch_assoc($result_cities)) {
                echo "<tr>
                    <td>" . htmlspecialchars($row['City']) . "</td>
                    <td>" . htmlspecialchars($row['Country']) . "</td>
                    <td>" . htmlspecialchars($row['AQI']) . "</td>
                </tr>";
                $displayed++;
            }
        }
 
        // Fill remaining rows to 10
        for ($i = $displayed; $i < 10; $i++) {
            echo "<tr><td>&nbsp;</td><td></td><td></td></tr>";
        }
        ?>
       
    </tbody>
</table>
 

 
</body>
</html>
 
<?php mysqli_close($con); ?>