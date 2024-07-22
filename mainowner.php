<?php
session_start();
include('db.php');

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_SESSION['id'];
$username = '';

if (isset($_SESSION['username'])) {
    // Username is already stored in the session
    $username = $_SESSION['username'];
} else {
    // Fetch username from the database
    $query = $con->prepare("SELECT username FROM form WHERE id = ?");
    if ($query) {
        $query->bind_param("i", $id);
        $query->execute();
        $query->bind_result($username);
        $query->fetch();
        $query->close();
        // Store the username in the session
        $_SESSION['username'] = $username;
    } else {
        echo "Error: " . $con->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['text'])) {
        $text = $_POST['text'];

        if (!empty($text) && !empty($id)) {
            $query = $con->prepare("INSERT INTO feedback (id, text) VALUES (?, ?)");
            if ($query) {
                $query->bind_param("is", $id, $text);

                if ($query->execute()) {
                    header("Location: " . $_SERVER['REQUEST_URI'] . "?feedback=success");
                    exit();
                } else {
                    echo "Error: " . $query->error;
                }
                $query->close();
            } else {
                echo "Error: " . $con->error;
            }
        } else {
            echo "<script type='text/javascript'>alert('Please write some feedback.')</script>";
        }
    }

    if (isset($_POST['order_action']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $action = $_POST['order_action'];

        if ($action == 'ready') {
            $query = $con->prepare("UPDATE `order` SET status = 'ready to serve' WHERE order_id = ?");
        } elseif ($action == 'completed') {
            $query = $con->prepare("UPDATE `order` SET status = 'completed' WHERE order_id = ?");
        }

        if ($query) {
            $query->bind_param("i", $order_id);
            $query->execute();
            $query->close();
        } else {
            echo "Error: " . $con->error;
        }

        // Return a JSON response indicating success
        echo json_encode(['status' => 'success']);
        exit();
    }
}

$todaysOrders = [];
$query = $con->prepare("SELECT order_id, itemids, status FROM `order` WHERE DATE(order_date) = CURDATE() AND status != 'completed'");
if ($query) {
    $query->execute();
    $query->bind_result($order_id, $itemids, $status);
    while ($query->fetch()) {
        $todaysOrders[] = ['order_id' => $order_id, 'itemids' => $itemids, 'status' => $status];
    }
    $query->close();
} else {
    echo "Error: " . $con->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Content</title>
    <link rel="stylesheet" type="text/css" href="formatting.css">
    <script>
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

            const orderSummaryElement = document.getElementById('order_summary_content');
            orderSummaryElement.innerHTML = orderSummaryHtml;

            const modal = document.getElementById('order-summary-modal');
            modal.style.display = "block";
        }

        function closeOrderSummary() {
            const modal = document.getElementById('order-summary-modal');
            modal.style.display = "none";
        }

        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (getUrlParameter('feedback') === 'success') {
                alert('Successfully registered feedback!');
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('feedback');
                    window.history.replaceState(null, null, url.toString());
                }
            }
        });

        function updateOrderStatus(orderId, action) {
            const formData = new FormData();
            formData.append('order_action', action);
            formData.append('order_id', orderId);

            fetch('mainowner.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const orderBox = document.getElementById('order-' + orderId);
                    if (action === 'completed') {
                        orderBox.remove();
                    } else if (action === 'ready') {
                        const statusElement = orderBox.querySelector('.status');
                        statusElement.textContent = 'Ready to serve';
                    }
                } else {
                    alert('Failed to update order status.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <section id="first_page">
        <p>
            <a href="#first_page">HOME</a> |
            <a href="https://public.tableau.com/shared/6HZ8RXRGB?:display_count=n&:origin=viz_share_link">DASHBOARD</a>| 
            <a href="#your_section">CUSTOMER ORDERS</a> |
            <a href="#about_us">ABOUT US</a>   
            <span id="user_info"></span>
        </p>
        <h1>Deliciousness brought closer</h1>
        <h2 id="curb">Curb your cravings with the core essence of MNNIT</h2>
    </section>
    <div class="profile-block">
        <h3>Welcome,</h3>
        <form action="logout.php" method="post">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>
    
    <section id="your_section">
        <h2>Order Details</h2>
        <div id="order_details" class="order-details">
            <?php if (!empty($todaysOrders)) : ?>
                <?php foreach ($todaysOrders as $order) : ?>
                    <div class="order-box" id="order-<?php echo $order['order_id']; ?>">
                        <p>Order ID: <?php echo $order['order_id']; ?></p>
                        <p>Items: <?php echo $order['itemids']; ?></p>
                        <p class="status">Status: <?php echo ucfirst($order['status']); ?></p>
                        <button type="button" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'ready')">Order Ready</button>
                        <button type="button" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'completed')">Order Completed</button>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p id="order_status">You don't have any pending orders...</p>
            <?php endif; ?>
        </div>
    </section>
    <section id="about_us">
        <h2>About Us</h2>
        <div class="person-container">
            <div class="person">
                <img src="aphoto.jpg" alt="Atharva Antapurkar">
                <h3>Atharva Antapurkar</h3>
                <p>I'm a student of Electronics and Communication Engineering in MNNIT. I'm very passionate to solve the daily life problems and the pain point the customer and small buisness face. To tackle this problem I have tried to bring this solution for canteen owners.</p>
                <div class="contact-info">
                    <p>Email: antapurkar28@gmail.com</p>
                </div>
            </div>
            <div class="person">
                <img src="awap.jpg" alt="Anshika Awasthi">
                <h3>Anshika Awasthi</h3>
                <p>I'm a student of Electronics and Communication Engineering in MNNIT. Seeing the everyday queues and long wating time in canteen is the real ppain point for customer like us. Through this pproject we have tried to make a contribution in solving this problem. </p>
                <div class="contact-info">
                    <p>Email: anshikaawasthi175@gmail.com </p>
                </div>
            </div>
        </div>
        <div class="feedback-container">
            <h2>Feedback</h2>
            <form action="mainowner.php" method="post">
                <div>
                    <textarea name="text" placeholder="Write your feedback here..." required></textarea>
                </div>
                <div>
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
    </section>
    <div id="order-summary-modal" style="display:none;">
        <div class="modal-content">
            <span onclick="closeOrderSummary()" style="cursor:pointer;">&times;</span>
            <div id="order_summary_content"></div>
        </div>
    </div>
</body>
</html>
