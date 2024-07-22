<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    if (!empty($email) && !empty($password) && !empty($user_type)) {
        $query = "SELECT * FROM form WHERE email='$email' AND user_type='$user_type' LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);

                if ($user_data['password'] == $password) {
                    $_SESSION['id'] = $user_data['id']; // Store user id in session
                    if ($user_type == "user") {
                        header("Location: mainuser.php");
                    } else if ($user_type == "owner") {
                        header("Location: mainowner.php");
                    }
                    die;
                } else {
                    echo "<script type='text/javascript'>alert('Wrong password');</script>";
                }
            } else {
                echo "<script type='text/javascript'>alert('Email or User Type not found');</script>";
            }
        } else {
            echo "Error: " . mysqli_error($con);
        }
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
        <form action="index.php" method="post">
            <div>
                <input type="email" id="login_email" name="email" placeholder="Email" required>
            </div>
            <div>
                <input type="password" id="login_password" name="password" placeholder="Password" required>
            </div>
            <div>
                <select id="login_user_type" name="user_type" required>
                    <option value="" disabled selected>Select User Type</option>
                    <option value="user">Customer</option>
                    <option value="owner">Owner</option>
                </select>
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
