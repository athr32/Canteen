<?php
session_start();
include('db.php');

$username = '';
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['text'])) {
        $text = $_POST['text'];
        $id = $_SESSION['id'];

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
    } elseif (isset($_POST['canteen_id'], $_POST['rating'])) {
        $canteen_id = (int)$_POST['canteen_id'];
        $rating = (int)$_POST['rating'];
        $comment = $_POST['comment'] ?? '';
        $id = $_SESSION['id'];

        $query = $con->prepare("INSERT INTO ratings (canteen_id, id, rating, comment) VALUES (?, ?, ?, ?)");
        if ($query) {
            $query->bind_param("iiis", $canteen_id, $id, $rating, $comment);
            if ($query->execute()) {
                header("Location: " . $_SERVER['REQUEST_URI'] . "?rating=success");
                exit();
            } else {
                echo "Error: " . $query->error;
            }
            $query->close();
        } else {
            echo "Error: " . $con->error;
        }
    } elseif (isset($_POST['order_id'], $_POST['action'])) {
        $order_id = (int)$_POST['order_id'];
        $action = $_POST['action'];
        $id = $_SESSION['id'];

        if ($action == 'ready') {
            $query = $con->prepare("UPDATE `order` SET status = 'Ready' WHERE id = ? AND order_id = ?");
            $query->bind_param("ii", $id, $order_id);
        } elseif ($action == 'completed') {
            $query = $con->prepare("DELETE FROM `order` WHERE id = ? AND order_id = ?");
            $query->bind_param("ii", $id, $order_id);
        }

        if ($query) {
            if ($query->execute()) {
                echo "Success";
            } else {
                echo "Error: " . $query->error;
            }
            $query->close();
        } else {
            echo "Error: " . $con->error;
        }
        exit();
    }
}

function getAverageRating($canteen_id, $con) {
    $query = $con->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE canteen_id = ?");
    if ($query) {
        $query->bind_param("i", $canteen_id);
        $query->execute();
        $query->bind_result($avg_rating);
        $query->fetch();
        $query->close();
        return $avg_rating ? round($avg_rating, 2) : 0;
    } else {
        echo "Error: " . $con->error;
        return "Error fetching rating";
    }
}

function getUserOrders($order_id, $con) {
    $orders = [];
    $query = $con->prepare("SELECT order_id, itemids, status FROM `order` WHERE id = ?");
    if ($query) {
        $query->bind_param("i", $order_id);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $query->close();
    } else {
        echo "Error: " . $con->error;
    }
    return $orders;
}

$canteens = [
    2 => "Mr Dewsis",
    1 => "Cafe'96",
    3 => "Yammuna"
];

$user_orders = getUserOrders($id, $con);

// Filter out completed orders
$user_orders = array_filter($user_orders, function($order) {
    return $order['status'] !== 'completed';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Content</title>
    <link rel="stylesheet" type="text/css" href="formatting.css">
    <style>
        .order {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        .order-status {
            font-weight: bold;
        }
        .order-actions button {
            margin: 5px;
        }
        .order-ready-message {
            color: green;
            font-weight: bold;
        }
    </style>
    <script>
        function updateOrderStatus(orderId, action) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "mainuser.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.trim() === "Success") {  // Trim to remove any extra spaces or newlines
                        const orderElement = document.getElementById('order_' + orderId);
                        if (action === 'ready') {
                            orderElement.querySelector('.order-status').innerText = 'Status: Ready';
                            const readyMessage = document.createElement('p');
                            readyMessage.classList.add('order-ready-message');
                            readyMessage.innerText = 'Your order is ready!';
                            orderElement.appendChild(readyMessage);
                        } else if (action === 'complete') {
                            orderElement.remove();
                        }
                    } else {
                        alert("Error: " + xhr.responseText);
                    }
                }
            };
            xhr.send("order_id=" + orderId + "&action=" + action);
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

        function updateAverageRating(canteen_id, avg_rating) {
            const ratingBar = document.getElementById('average-rating-bar-' + canteen_id);
            const percentage = (avg_rating / 5) * 100;
            ratingBar.style.width = percentage + '%';
            ratingBar.innerText = 'Average Rating: ' + avg_rating.toFixed(2);
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
            if (getUrlParameter('rating') === 'success') {
                alert('Successfully registered rating!');
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('rating');
                    window.history.replaceState(null, null, url.toString());
                }
            }

            <?php foreach ($canteens as $canteen_id => $canteen_name): ?>
                const avg_rating_<?php echo $canteen_id; ?> = <?php echo getAverageRating($canteen_id, $con); ?>;
                updateAverageRating(<?php echo $canteen_id; ?>, avg_rating_<?php echo $canteen_id; ?>);
            <?php endforeach; ?>

            <?php foreach ($user_orders as $order): ?>
                <?php if ($order['status'] === 'Ready'): ?>
                    const orderElement = document.getElementById('order_<?php echo $order['id']; ?>');
                    const readyMessage = document.createElement('p');
                    readyMessage.classList.add('order-ready-message');
                    readyMessage.innerText = 'Your order is ready!';
                    orderElement.appendChild(readyMessage);
                <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>
</head>
<body>
    <section id="first_page">
        <p>
            <a href="#first_page">HOME</a> | 
            <a href="#menu_section">CANTEEN</a> |
            <a href="#your_section">YOUR ORDERS</a> |
            <a href="#about_us">ABOUT US</a>   
            <span id="user_info"></span>
        </p>
        <h1>Deliciousness brought closer</h1>
        <h2 id="curb">Curb your cravings with the core essence of MNNIT</h2>
    </section>
    <div class="profile-block">
        <h3>Welcome, <?php echo htmlspecialchars($username); ?></h3>
        <form action="logout.php" method="post">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>
    <section id="menu_section">
        <h2>Canteen</h2>
        <div class="tabs">
            <a href="menudewsis.php" class="tab" target="_blank">
                <img src="d.jpg" alt="Canteen 1">
                <span>Mr Dewsis</span>
            </a>
            <div class="rating-section" data-canteen-id="2">
                <h4>Rate Mr Dewsis</h4>
                <form class="rating-form" method="post">
                    <input type="hidden" name="canteen_id" value="2">
                    <div class="star-rating">
                        <input type="radio" name="rating" value="1" id="star-1-2"><label for="star-1-2">&#9733;</label>
                        <input type="radio" name="rating" value="2" id="star-2-2"><label for="star-2-2">&#9733;</label>
                        <input type="radio" name="rating" value="3" id="star-3-2"><label for="star-3-2">&#9733;</label>
                        <input type="radio" name="rating" value="4" id="star-4-2"><label for="star-4-2">&#9733;</label>
                        <input type="radio" name="rating" value="5" id="star-5-2"><label for="star-5-2">&#9733;</label>
                    </div>
                    <label for="comment">Comment:</label>
                    <textarea name="comment" rows="3"></textarea>
                    <button type="submit">Submit Rating</button>
                </form>
                <div class="average-rating-container">
                    <div class="average-rating-bar" id="average-rating-bar-2"></div>
                </div>
            </div>
            <a href="menucafe96.php" class="tab" target="_blank">
                <img src="cafe96.jpg" alt="Canteen 2">
                <span>Cafe'96</span>
            </a>
            <div class="rating-section" data-canteen-id="1">
                <h4>Rate Cafe'96</h4>
                <form class="rating-form" method="post">
                    <input type="hidden" name="canteen_id" value="1">
                    <div class="star-rating">
                        <input type="radio" name="rating" value="1" id="star-1-1"><label for="star-1-1">&#9733;</label>
                        <input type="radio" name="rating" value="2" id="star-2-1"><label for="star-2-1">&#9733;</label>
                        <input type="radio" name="rating" value="3" id="star-3-1"><label for="star-3-1">&#9733;</label>
                        <input type="radio" name="rating" value="4" id="star-4-1"><label for="star-4-1">&#9733;</label>
                        <input type="radio" name="rating" value="5" id="star-5-1"><label for="star-5-1">&#9733;</label>
                    </div>
                    <label for="comment">Comment:</label>
                    <textarea name="comment" rows="3"></textarea>
                    <button type="submit">Submit Rating</button>
                </form>
                <div class="average-rating-container">
                    <div class="average-rating-bar" id="average-rating-bar-1"></div>
                </div>
            </div>
            <a href="menuyammuna.php" class="tab" target="_blank">
                <img src="dy.jpg" alt="Canteen 3">
                <span>Yammuna</span>
            </a>
            <div class="rating-section" data-canteen-id="3">
                <h4>Rate Yammuna</h4>
                <form class="rating-form" method="post">
                    <input type="hidden" name="canteen_id" value="3">
                    <div class="star-rating">
                        <input type="radio" name="rating" value="1" id="star-1-3"><label for="star-1-3">&#9733;</label>
                        <input type="radio" name="rating" value="2" id="star-2-3"><label for="star-2-3">&#9733;</label>
                        <input type="radio" name="rating" value="3" id="star-3-3"><label for="star-3-3">&#9733;</label>
                        <input type="radio" name="rating" value="4" id="star-4-3"><label for="star-4-3">&#9733;</label>
                        <input type="radio" name="rating" value="5" id="star-5-3"><label for="star-5-3">&#9733;</label>
                    </div>
                    <label for="comment">Comment:</label>
                    <textarea name="comment" rows="3"></textarea>
                    <button type="submit">Submit Rating</button>
                </form>
                <div class="average-rating-container">
                    <div class="average-rating-bar" id="average-rating-bar-3"></div>
                </div>
            </div>
        </div>
    </section>
    

    <section id="your_section">
        <h2>Your orders</h2>
        <div id="orders">
            <?php foreach ($user_orders as $order): ?>
                <div class="order" id="order_">
                    <h3>Order <?php echo htmlspecialchars($order['order_id']); ?></h3>
                    <p>Items: <?php echo htmlspecialchars($order['itemids']); ?></p>
                    <p class="order-status">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                    <div class="order-actions">
                        <?php if ($order['status'] === 'Ready'): ?>
                            <button onclick="updateOrderStatus('<?php echo htmlspecialchars($order['id']); ?>', 'completed')">Mark as Complete</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div id="order-summary-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeOrderSummary()">&times;</span>
            <div id="order_summary_content"></div>
        </div>
    </div>
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
                <p>I'm a student of Electronics and Communication Engineering in MNNIT. Seeing the everyday queues and long wating time in canteen is the real ppain point for customer like us. Through this pproject we have tried to make a contribution in solving this problem.  </p>
                <div class="contact-info">
                    <p>Email: anshikaawasthi175@gmail.com </p>
                </div>
            </div>
        </div>
        <div class="feedback-container">
            <h2>Feedback</h2>
            <form action="mainuser.php" method="post">
                <div>
                    <textarea name="text" placeholder="Write your feedback here..." required></textarea>
                </div>
                <div>
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
    </section>
    
    
    <footer>
        <p>&copy; 2024</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php
            foreach ($canteens as $id => $name) {
                $avg_rating = getAverageRating($id, $con);
                echo "updateAverageRating($id, $avg_rating);";
            }
            ?>
        });
    </script>
</body>
</html>
