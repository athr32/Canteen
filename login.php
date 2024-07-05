<?php
session_start();

include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM form WHERE email='$email' LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);

                if ($user_data['password'] == $password) {
                    header("Location: index.php");
                    die;
                } else {
                    echo "<script type='text/javascript'>alert('Wrong password');</script>";
                }
            } else {
                echo "<script type='text/javascript'>alert('Email not found');</script>";
            }
        } else {
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo "<script type='text/javascript'>alert('Please enter email and password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div>
                <input type="email" id="login_username" name="email" placeholder="Email" required>
            </div>
            <div>
                <input type="password" id="login_password" name="password" placeholder="Password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
        <p>
            Don't have an account? <a href="sign.php">Sign Up</a>
        </p>
    </div>
    <script src="login.js"></script>
</body>
</html>
