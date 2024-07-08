<?php
    session_start();

    include("db.php");

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if(!empty($password) && !empty($username) && !empty($name) && !empty($email)){
            $query = "INSERT INTO form (name, username, email, password) VALUES ('$name', '$username','$email', '$password')";
            
            if (mysqli_query($con, $query)) {
                echo "<script type='text/javascript'>alert('Successfully Registered');</script>";
            } 
            else {
                echo "Error: " . $query . "<br>" . mysqli_error($con);
            }
        }
        else {
            echo "<script type='text/javascript'>alert('Please enter some valid information!')</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Sign Up</h2>
        <form id="sign.php" method="post">
            <div>
                <input type="text" id="signup_name" name="name" placeholder="Name" required>
            </div>
            <div>
                <input type="text" id="signup_username" name="username" placeholder="Username" required>
            </div>
            <div>
                <input type="email" id="signup_email" name="email" placeholder="Email" required>
            </div>
            <div>
                <input type="password" id="signup_password" name="password" placeholder="Password" required>
            </div>
            <div>
            <div>
                <button type="submit">signup</button>
            </div>
        </form>
        <p>
            Already have an account? <a href="index.php">Login</a>
        </p>
    </div>
    <script src="signup.js"></script>
</body>
</html>
