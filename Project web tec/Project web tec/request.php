 <?php
session_start();
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['cities']) && is_array($_POST['cities'])) {
 
        if (count($_POST['cities']) > 10) {
            // Too many cities selected — redirect back with error
            $_SESSION['error'] = "You can select a maximum of 10 cities only.";
            header("Location: request.php");
            exit();
        }
 
        $_SESSION['selected_cities'] = $_POST['cities'];
        header("Location: showaqi.php");
        exit();
    } else {
        $_SESSION['selected_cities'] = [];
        header("Location: showaqi.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>City AQI Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
 
        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 400px;
           
        }
 
        h2 {
            margin-top: 0;
            text-align: center;
            color: #333;
        }
 
        .city-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
        }
 
        .city-item {
            display: flex;
            align-items: center;
        }
 
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
 
        input[type="submit"]:hover {
            background: #0056b3;
        }
        /* User menu styles */
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
}
.user-menu span {
    color: #333;
    font-weight: bold;
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
   <?php
$username = $_SESSION['uname'] ?? 'Guest';
?>
<div class="user-menu">
    <img src="Logo.png" alt="User Icon">
    <span><?php echo htmlspecialchars($username); ?></span>
    <a class="profile-link" href="profile.php?return=request.php">Profile</a>
    <a class="logout-link" href="logout.php">Logout</a>
</div>
 
<div class="form-box">
   
    <h2>Select Cities</h2>
     <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='error-message'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
    ?>
    <form action="request.php" method="POST">
    <div class="city-list">
 
        <?php
            $cities = [
                "Dhaka", "Delhi", "Beijing", "Sydney", "Karachi",
                "Jakarta", "London", "Berlin", "Rome", "New York",
                "Mexico City", "Istanbul", "Toronto", "Los Angeles",
                "Paris", "Tokyo", "Cairo", "Bangkok", "Moscow", "Bucharest"
            ];
 
            foreach ($cities as $city) {
                echo "<div class='city-item'><input type='checkbox' name='cities[]' value='$city' onclick='return checkCityLimit(this)'> $city</div>";
 
            }
        ?>
    </div>
    <input type="submit" value="Submit">
</form>
<script>
function checkCityLimit(checkbox) {
  const selectedCities = document.querySelectorAll('input[name="cities[]"]:checked');
  // If trying to check this box and already 10 are selected, block it:
  if (checkbox.checked === false && selectedCities.length >= 10) {
    alert("You can select a maximum of 10 cities.");
    return false; // Prevent the checkbox from being checked
  }
  return true; // Allow change
}
</script>
 
</div>
</body>
</html>