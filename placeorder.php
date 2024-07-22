<?php
session_start();
include("db.php");

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $items = $data['items'];
    $bill = $data['bill'];
    $id = $_SESSION['id'];

    // Debugging output
    error_log("Items: " . print_r($items, true));
    error_log("Bill: " . $bill);
    error_log("User ID: " . $id);

    if (!empty($items) && !empty($id) && !empty($bill)) {
        // Determine canteen_id based on the first character of the first item ID
        $firstItemId = $items[0]['id'];
        $canteen_id = (int)substr($firstItemId, 0, 1);

        // Generate a comma-separated string of item IDs, repeating IDs according to their quantities
        $itemids = [];
        foreach ($items as $item) {
            $item_id = $item['id'];
            $quantity = $item['quantity'];
            for ($i = 0; $i < $quantity; $i++) {
                $itemids[] = $item_id;
            }
        }
        $itemids_string = implode(',', $itemids);

        // Use prepared statements to avoid SQL injection
        $query = $con->prepare("INSERT INTO `order` (id, itemids, canteen_id, bill) VALUES (?, ?, ?, ?)");
        if ($query) {
            $query->bind_param("isii", $id, $itemids_string, $canteen_id, $bill);

            if ($query->execute()) {
                echo "Successfully registered your order!";

                // Update the items_tally table
                foreach ($items as $item) {
                    $item_id = $item['id'];
                    $quantity = $item['quantity'];

                    $updateQuery = $con->prepare("UPDATE items_tally SET count = count + ? WHERE item_id = ?");
                    if ($updateQuery) {
                        $updateQuery->bind_param("ii", $quantity, $item_id);
                        if (!$updateQuery->execute()) {
                            error_log("Update Query Error: " . $updateQuery->error);
                        }
                        $updateQuery->close();
                    } else {
                        error_log("Prepare Statement Error: " . $con->error);
                    }
                }

            } else {
                echo "Error: " . $query->error;
                error_log("Query Error: " . $query->error);
            }
            $query->close();
        } else {
            echo "Error: " . $con->error;
            error_log("Prepare Statement Error: " . $con->error);
        }
    } else {
        echo "Please place the order";
        error_log("Validation Error: Missing items, id, or bill");
    }
}
?>
