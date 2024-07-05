<?php
    session_start();

    include("db.php");
    $postData = file_get_contents("php://input");
    $request = json_decode($postData, true);

    $id = $request['id'];
    $itemids = $request['itemids'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO orders (id, itemids) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $item_ids);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Order placed successfully!", "order_id" => $stmt->insert_id));
    } else {
        echo json_encode(array("error" => $stmt->error));
    }

    // Close the connection
    $stmt->close();
    $conn->close();
?>

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu</title>
    <link rel="stylesheet" href="menuformatting.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('d1.jpg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
        }
    </style>
    <script src="menu.js" defer></script>

</head>
<body>
    <div id="food-menu"></div>
    <button id="checkout-button" onclick="checkout()">Checkout</button>
    <div id="order-summary"></div>

    <script>
        const foodItems = [
            { "id": 21, name: 'Cappuccino', price: '60' },
            { "id": 22, name: 'Tea', price: '10' },
            { "id": 23, name: 'Masala Coke', price: '60' },
            { "id": 24, name: 'Oreo Shake', price: '75' },
            { "id": 25, name: 'French Fries', price: '60' },
            { "id": 26, name: 'Veg Burger', price: '70' },
            { "id": 27, name: 'Cheese Burger', price: '80' },
            { "id": 28, name: 'Veg Cheese Sandwich', price: '60' },
            { "id": 29, name: 'Aloo Paratha', price: '45' },
            { "id": 210, name: 'Paneer Paratha', price: '60' },
            { "id": 211, name: 'Idli(2pc) Sambhar', price: '50' },
            { "id": 212, name: 'Masala Dosa', price: '70' },
            { "id": 213, name: 'Red Sauce Pasta', price: '130' },
            { "id": 214, name: 'Veg Hakka Noodles', price: '90' },
            { "id": 215, name: 'Veg Spring Rolls', price: '80' },
            { "id": 216, name: 'Chilli Potato', price: '80' },
            { "id": 217, name: 'Paneer Fried Rice', price: '110' },
            { "id": 218, name: 'Veggie Delight Pizza', price: '170' },
            { "id": 219, name: 'Peppy Paneer Pizza', price: '180' },
            { "id": 220, name: 'Farmhouse Pizza', price: '210' },
            { "id": 221, name: 'Brownie with Ice Cream', price: '80' }
        ];

        const foodMenu = document.getElementById('food-menu');
        foodItems.forEach(item => {
            const html = `
                <div class="food-item" data-id="${item.id}">
                    <span>${item.name} - ₹${item.price}</span>
                    <input type="checkbox" onclick="toggleQuantityControls(this)">
                    <div class="quantity-controls">
                        <button onclick="adjustQuantity(this, false)">-</button>
                        <input type="number" value="1" readonly>
                        <button onclick="adjustQuantity(this, true)">+</button>
                    </div>
                </div>
            `;
            foodMenu.insertAdjacentHTML('beforeend', html);
        });

        function toggleQuantityControls(checkbox) {
            const quantityControls = checkbox.nextElementSibling;
            if (checkbox.checked) {
                quantityControls.style.display = 'block';
            } else {
                quantityControls.style.display = 'none';
                quantityControls.querySelector('input').value = '1';
            }
        }

        function adjustQuantity(button, increment) {
            const input = button.parentElement.querySelector('input');
            let value = parseInt(input.value);
            value = increment ? value + 1 : value - 1;
            if (value < 1) value = 1;
            input.value = value;
        }

        function checkout() {
            const selectedItems = [];
            document.querySelectorAll('.food-item input[type="checkbox"]:checked').forEach(checkbox => {
                const item = checkbox.closest('.food-item');
                const id = item.getAttribute('data-id');
                const quantity = item.querySelector('.quantity-controls input').value;
                for (let i = 0; i < quantity; i++) {
                    selectedItems.push(id);
                }
            });

            // Prepare the order data
            const orderData = {
                id: 1, // Example foreign key value, replace with appropriate value
                item_ids: selectedItems.join(',')
            };

            // Send the order data to the server
            fetch('/place-order.php', { // Ensure this points to your PHP script
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                // Handle response from the server
                document.getElementById('order-summary').innerText = 'Order placed successfully!';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('order-summary').innerText = 'Error placing order.';
            });
        }
    </script>
</body>
</html>
