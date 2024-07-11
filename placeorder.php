<?php
session_start();

include("db.php");
if (!isset($_SESSION['id'])) {
    // If no user is logged in, redirect to the login page
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $itemids = $data['itemids'];
    $id = $_SESSION['id']; // Get user id from session

    if (!empty($itemids) && !empty($id)) {
        // Use prepared statements to avoid SQL injection
        $query = $con->prepare("INSERT INTO `order` (id, itemids) VALUES (?, ?)");
        if ($query) {
            $query->bind_param("is", $id, $itemids);

            if ($query->execute()) {
                echo "Successfully registered your order!";
            } else {
                echo "Error: " . $query->error;
            }
            $query->close();
        } else {
            echo "Error: " . $con->error;
        }
    } else {
        echo "Please place the order";
    }
}
?>
