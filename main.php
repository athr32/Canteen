<?php
    include('db.php');
    
?>
<?php
    session_start();
    include('db1.php');
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $feedback = $_POST['feedback'];
        
        if(!empty(!empty($feedback)){
            $query = "INSERT INTO form (feedback) VALUES ('$feedback')";
            
            if (mysqli_query($con, $query)) {
                echo "<script type='text/javascript'>alert('Successfully registered a feedback!');</script>";
            } 
            else {
                echo "Error: " . $query . "<br>" . mysqli_error($con);
            }
        }
        else {
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
</head>
<body>
    <section id="first_page">
        <p>
            <span class="a"></span>
            <a href="#first_page">HOME</a> | 
            <a href="#menu_section">CANTEEN</a> |
            <a href="#your_section">YOUR ORDERS</a> |
            <a href="#about_us">ABOUT US</a>   
            <span id="user_info"></span>
        </p>
        <h1>Deliciousness brought closer</h1>
        <h2 id="curb">Curb your cravings with the core essence of MNNIT</h2>
    </section>
    <section id="menu_section">
        <h2>Canteen</h2>
        <div class="tabs">
            <a href="menudewsis.php" class="tab" target="_blank">
                <img src="d.jpg" alt="Canteen 1">
                <span>Mr Dewsis</span>
            </a>
            <a href="menucafe96.html" class="tab" target="_blank">
                <img src="cafe96.jpg" alt="Canteen 2">
                <span>Cafe'96</span>
            </a>
            <a href="menuyammuna.html" class="tab" target="_blank">
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
        
        <script src="order.js"></script>
        
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
        
        <form action="main.php" method="post">
            <div class="feedback-box">
                <h3>Feedback</h3>
                <textarea name= "feedback" placeholder="Enter your feedback here..."></textarea>
                <br>
                <button type="submit">Submit</button>
            </div>
        </form>
    </section>
</body>
</html>
