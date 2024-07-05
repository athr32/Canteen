<?php
  $con = mysqli_connect("localhost", "root", "1234","register",3307);

  if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
  }
?>