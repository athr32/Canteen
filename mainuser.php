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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $text = $_POST['text'];
    $id = $_SESSION['id']; // Get user id from session

    if (!empty($text) && !empty($id)) {
        // Use prepared statements to avoid SQL injection
        $query = $con->prepare("INSERT INTO feedback (id, text) VALUES (?, ?)");
        if ($query) {
            $query->bind_param("is", $id, $text);

            if ($query->execute()) {
                // Pass a query parameter to indicate success
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

        // Function to get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Check if feedback parameter is set to success
        document.addEventListener("DOMContentLoaded", function() {
            if (getUrlParameter('feedback') === 'success') {
                alert('Successfully registered feedback!');
                // Remove the feedback parameter from the URL without refreshing the page
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('feedback');
                    window.history.replaceState(null, null, url.toString());
                }
            }
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
            <a href="menucafe96.php" class="tab" target="_blank">
                <img src="cafe96.jpg" alt="Canteen 2">
                <span>Cafe'96</span>
            </a>
            <a href="menuyammuna.php" class="tab" target="_blank">
                <img src="dy.jpg" alt="Canteen 3">
                <span>Yammuna</span>
            </a>
        </div>
    </section>
    <section id="your_section">
        <h2>Order Details</h2>
        <div id="order_details" class="order-details">
            <p id="order_status">You don't have any pending order...</p>
        </div>
        <div id="order_summary_section" class="order-summary-section">
            <h3>Your Order Summary</h3>
            <div id="order_summary_content"></div>
        </div>
    </section>
    <section id="about_us">
        <h2>About Us</h2>
        <div class="person-container">
            <div class="person">
                <img src="aphoto.jpg" alt="Atharva Antapurkar">
                <h3>Atharva Antapurkar</h3>
                <p>Person 1 is a seasoned chef with over 10 years of experience in the culinary industry. Their passion for cooking and dedication to quality ensures that every meal is a delightful experience.</p>
                <div class="contact-info">
                    <p>Email: antapurkar28@gmail.com</p>
                </div>
            </div>
            <div class="person">
                <img src="awap.jpg" alt="Anshika Awasthi">
                <h3>Anshika Awasthi</h3>
                <p>Person 2 is a food enthusiast and business manager who brings a wealth of knowledge in restaurant management and customer service. Their expertise helps in creating a seamless dining experience.</p>
                <div class="contact-info">
                    <p>Email: anshikaawasthi175@gmail.com </p>
                </div>
            </div>
        </div>
        <div class="feedback-container">
            <h2>Feedback</h2>
            <form action="main.php" method="post">
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
