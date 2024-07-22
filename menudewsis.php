<?php
    session_start();
    include('db.php');
    $username = '';
    if (!isset($_SESSION['id'])) {
        // If no user is logged in, redirect to the login page
        header('Location: index.php');
        exit();
    }

    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        
        // Fetch the username from the form table
        $query = $con->prepare("SELECT username FROM form WHERE id = ?");
        if ($query) {
            $query->bind_param("i", $id);
            $query->execute();
            $query->bind_result($username);
            $query->fetch();
            $query->close();
        } else {
            echo "Error: " . $con->error;
        }
    }
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
    <button id="order-summary-button" onclick="showOrderSummary()">Order Summary</button>
    <div id="order-summary-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeOrderSummary()">&times;</span>
            <div id="order-summary"></div>
            <button id="checkout-button" onclick="checkout()">Checkout</button>
        </div>
    </div>
    <div class="profile-block">
        <h3>Welcome, <?php echo htmlspecialchars($username); ?></h3>
        <form action="logout.php" method="post">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>

    <script>
        const foodItems = [
            { id: 21, name: 'Cappuccino', price: 60 },
            { id: 22, name: 'Tea', price: 10 },
            { id: 23, name: 'Masala Coke', price: 60 },
            { id: 24, name: 'Oreo Shake', price: 75 },
            { id: 25, name: 'French Fries', price: 60 },
            { id: 26, name: 'Veg Burger', price: 70 },
            { id: 27, name: 'Cheese Burger', price: 80 },
            { id: 28, name: 'Veg Cheese Sandwich', price: 60 },
            { id: 29, name: 'Aloo Paratha', price: 45 },
            { id: 210, name: 'Paneer Paratha', price: 60 },
            { id: 211, name: 'Idli(2pc) Sambhar', price: 50 },
            { id: 212, name: 'Masala Dosa', price: 70 },
            { id: 213, name: 'Red Sauce Pasta', price: 130 },
            { id: 214, name: 'Veg Hakka Noodles', price: 90 },
            { id: 215, name: 'Veg Spring Rolls', price: 80 },
            { id: 216, name: 'Chilli Potato', price: 80 },
            { id: 217, name: 'Paneer Fried Rice', price: 110 },
            { id: 218, name: 'Veggie Delight Pizza', price: 170 },
            { id: 219, name: 'Peppy Paneer Pizza', price: 180 },
            { id: 220, name: 'Farmhouse Pizza', price: 210 },
            { id: 221, name: 'Brownie with Ice Cream', price: 80 }
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

        function showOrderSummary() {
            const selectedItems = [];
            let orderSummaryHtml = "<h2>Order Summary</h2><ul>";
            let totalCost = 0;

            document.querySelectorAll('.food-item input[type="checkbox"]:checked').forEach(checkbox => {
                const item = checkbox.closest('.food-item');
                const id = item.getAttribute('data-id');
                const name = item.querySelector('span').innerText.split(' - ')[0];
                const price = parseInt(item.querySelector('span').innerText.split(' - ₹')[1]);
                const quantity = parseInt(item.querySelector('.quantity-controls input').value);
                const itemTotal = price * quantity;

                selectedItems.push({ id, name, price, quantity });

                orderSummaryHtml += `<li>${name} (₹${price}) x ${quantity} = ₹${itemTotal}</li>`;
                totalCost += itemTotal;
            });

            orderSummaryHtml += `</ul><h3>Total: ₹${totalCost}</h3>`;

            const orderSummaryElement = document.getElementById('order-summary');
            orderSummaryElement.innerHTML = orderSummaryHtml;

            const modal = document.getElementById('order-summary-modal');
            modal.style.display = "block";
        }

        function closeOrderSummary() {
            const modal = document.getElementById('order-summary-modal');
            modal.style.display = "none";
        }
        
        function checkout() {
            const selectedItems = [];
            let totalCost = 0;
            
            document.querySelectorAll('.food-item input[type="checkbox"]:checked').forEach(checkbox => {
                const item = checkbox.closest('.food-item');
                const id = item.getAttribute('data-id');
                const price = parseInt(item.querySelector('span').innerText.split(' - ₹')[1]);
                const quantity = parseInt(item.querySelector('.quantity-controls input').value);
                
                totalCost += price * quantity;

                selectedItems.push({ id: id, quantity: quantity });
            });

            // Prepare the order data
            const orderData = {
                items: selectedItems,
                bill: totalCost
            };

            console.log('Order Data:', orderData); // Debugging

            // Send the order data to the server
            fetch('placeorder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.text())
            .then(data => {
                console.log('Server Response:', data); // Debugging
                alert('Order placed successfully!');
                closeOrderSummary();

                // Reset the checkboxes and quantity controls
                document.querySelectorAll('.food-item input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                    const quantityControls = checkbox.nextElementSibling;
                    quantityControls.style.display = 'none';
                    quantityControls.querySelector('input').value = '1';
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error placing order.');
            });
        }

        
    </script>
</body>
</html>
