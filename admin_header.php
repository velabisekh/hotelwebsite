<?php

include 'connect.php';

?>


<link rel="stylesheet" href="../css/admin.css">


<header class="header">

   <section class="flex">

      <a href="dashboard.php" class="logo">AdminPanel.</a>

      <nav class="navbar">
         <a href="dashboard.php">home</a>
         <a href="bookings.php">bookings</a>
         <a href="admins.php">admins</a>
         <a href="messages.php">messages</a>
         <a href="register.php">register</a>
         <a href="login.php">login</a>
         <a href="logout.php" class="delete-btn">logout</a>
      </nav>

      <div id="menu-btn" class="fas fa-bars"></div>

   </section>

</header>