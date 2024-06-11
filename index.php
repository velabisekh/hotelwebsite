<?php
include 'connect.php';

// Initialize arrays for messages
$success_msg = [];
$warning_msg = [];

// Function to create a unique user ID
function create_unique_id() {
    return uniqid('user_', true);
}

// Check if user ID cookie is set
if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = create_unique_id();
    setcookie('user_id', $user_id, time() + 60*60*24*30, '/');
    header('Location: index.php');
    exit(); // Stop script execution after redirection
}

// Function to check if rooms are available for booking
function check_room_availability($conn, $check_in) {
    $total_rooms = 0;
    $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
    $check_bookings->execute([$check_in]);
    while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
        $total_rooms += $fetch_bookings['rooms'];
    }
    return $total_rooms;
}

// Process form submission for checking room availability
if(isset($_POST['check'])){
    $check_in = $_POST['check_in'];
    $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);

    $total_rooms = check_room_availability($conn, $check_in);

    if($total_rooms >= 30){
        $warning_msg[] = 'Rooms are not available';
    } else {
        $success_msg[] = 'Rooms are available';
    }
}

// Process form submission for making a reservation
if(isset($_POST['book'])){
    // Sanitize input data
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $rooms = filter_var($_POST['rooms'], FILTER_SANITIZE_STRING);
    $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_STRING);
    $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_STRING);
    $adults = filter_var($_POST['adults'], FILTER_SANITIZE_STRING);
    $childs = filter_var($_POST['childs'], FILTER_SANITIZE_STRING);

    // Check room availability
    $total_rooms = check_room_availability($conn, $check_in);

    if($total_rooms >= 30){
        $warning_msg[] = 'Rooms are not available';
    } else {
        // Check if the booking already exists
        $verify_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND rooms = ? AND check_in = ? AND check_out = ? AND adults = ? AND childs = ?");
        $verify_bookings->execute([$user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);

        if($verify_bookings->rowCount() > 0){
            $warning_msg[] = 'Room booked already';
        } else {
            // Insert new booking
            $book_room = $conn->prepare("INSERT INTO `bookings`(user_id, name, email, number, rooms, check_in, check_out, adults, childs) VALUES(?,?,?,?,?,?,?,?,?)");
            $book_room->execute([$user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);
            $success_msg[] = 'Room booked successfully';
        }
    }
}

// Process form submission for sending a message
if(isset($_POST['send'])){
    // Sanitize input data
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

    // Check if the message already exists
    $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
    $verify_message->execute([$name, $email, $number, $message]);

    if($verify_message->rowCount() > 0){
        $warning_msg[] = 'Message sent already';
    } else {
        // Insert new message
        $insert_message = $conn->prepare("INSERT INTO `messages`(name, email, number, message) VALUES(?,?,?,?)");
        $insert_message->execute([$name, $email, $number, $message]);
        $success_msg[] = 'Message sent successfully';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Hotel BeachParadise</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style1.css">

</head>
<body>

<?php include 'user_header.php'; ?>
   

<!-- home section starts  -->

<section class="home" id="home">

   <div class="swiper home-slider">

      <div class="swiper-wrapper">

         <div class="box swiper-slide">
            <img src="images/home-img-1.jpg" alt="">
            <div class="flex">
               <h3>luxurious rooms</h3>
               <a href="#availability" class="btn">check availability</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-2.jpg" alt="">
            <div class="flex">
               <h3>foods and drinks</h3>
               <a href="#reservation" class="btn">make a reservation</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-3.jpeg" alt="">
            <div class="flex">
               <h3>luxurious halls</h3>
               <a href="#contact" class="btn">contact us</a>
            </div>
         </div>

      </div>

      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>

   </div>

</section>

<!-- home section ends -->

<!-- availability section starts  -->

<section class="availability" id="availability">

   <form action="" method="post">
      <div class="flex">
         <div class="box">
            <p>check in <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>check out <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>adults <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1">1 adult</option>
               <option value="2">2 adults</option>
               <option value="3">3 adults</option>
               <option value="4">4 adults</option>
               <option value="5">5 adults</option>
               <option value="6">6 adults</option>
            </select>
         </div>
         <div class="box">
            <p>childs <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="-">0 child</option>
               <option value="1">1 child</option>
               <option value="2">2 childs</option>
               <option value="3">3 childs</option>
               <option value="4">4 childs</option>
               <option value="5">5 childs</option>
               <option value="6">6 childs</option>
            </select>
         </div>
         <div class="box">
            <p>rooms <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1">1 room</option>
               <option value="2">2 rooms</option>
               <option value="3">3 rooms</option>
               <option value="4">4 rooms</option>
               <option value="5">5 rooms</option>
               <option value="6">6 rooms</option>
            </select>
         </div>
      </div>
      <input type="submit" value="check availability" name="check" class="btn">
   </form>

</section>

<!-- availability section ends -->

<!-- about section starts  -->

<section class="about" id="about">

   <div class="row">
      <div class="image">
         <img src="images/about-img-1.jpg" alt="">
      </div>
      <div class="content">
         <h3>best staff</h3>
         <p> All Services Are Provided To Exceed Your Expectations. Book Your Stay At Hotel BeachParadise In Chennai. Enjoy The Spacious Rooms & Great Services.</p>
         <a href="#reservation" class="btn">make a reservation</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="images/about-img-2.jpg" alt="">
      </div>
      <div class="content">
         <h3>best foods</h3>
         <p>Offering choice delicacies from across the world on a platter, we invite you to anchor at the Jetty and relish varied cuisines that
             reflect the various cultures that including authentic local tamilnadu flavours, 
            pan Indian, pan European, British favorites, a special range of seafood choices and so much more.</p>
         <a href="#contact" class="btn">contact us</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="images/about-img-3.jpg" alt="">
      </div>
      <div class="content">
         <h3>swimming pool</h3>
         <p>Set swimming pool in our hotel, with a natural well on a side, the swimming pool beckons for a lazy swim, 
            a refreshing lap or just a nice snooze under the sun.</p>
         <a href="#availability" class="btn">check availability</a>
      </div>
   </div>

</section>

<!-- about section ends -->

<!-- services section starts  -->

<section class="services">

   <div class="box-container">

      <div class="box">
         <img src="images/icon-1.png" alt="">
         <h3>food & drinks</h3>
         <p>celebrate your moment with our tasty foods and drinks.</p>
      </div>

      <div class="box">
         <img src="images/icon-2.png" alt="">
         <h3>outdoor dining</h3>
         <p>In our bid to deliver authentic, undiluted experiences, 
            we have introduced outdoor dining experience. 
            And at the same time it gives you the opportunity to dabble in the local delicacies.</p>
      </div>

      <div class="box">
         <img src="images/icon-3.png" alt="">
         <h3>beach view</h3>
         <p>
            Life's a beach, find your wave. Sun, sand, and waves.
            Let the waves hit your feet and the sand be your seat. 
            Waves don't wait, catch them while you can.  </p>
      </div>

      <div class="box">
         <img src="images/icon-4.png" alt="">
         <h3>decorations</h3>
         <p>Enjoy your celebration with our decorations</p>
      </div>

      <div class="box">
         <img src="images/icon-5.png" alt="">
         <h3>swimming pool</h3>
         <p>the swimming pool beckons for a lazy swim, 
            a refreshing lap or just a nice snooze under the sun.</p>
      </div>

      <div class="box">
         <img src="images/icon-6.png" alt="">
         <h3>resort beach</h3>
         <p> our hotel is located at the seaside, with access to a private beach. 
            Enjoy your holiday with beach.</p>
      </div>

   </div>

</section>

<!-- services section ends -->

<!-- reservation section starts  -->

<section class="reservation" id="reservation">

   <form action="" method="post">
      <h3>make a reservation</h3>
      <div class="flex">
         <div class="box">
            <p>your name <span>*</span></p>
            <input type="text" name="name" maxlength="50" required class="input">
         </div>
         <div class="box">
            <p>your email <span>*</span></p>
            <input type="email" name="email" maxlength="50" required class="input">
         </div>
         <div class="box">
            <p>your number <span>*</span></p>
            <input type="number" name="number" maxlength="10" required class="input">
         </div>
         <div class="box">
            <p>rooms <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1" selected>1 room</option>
               <option value="2">2 rooms</option>
               <option value="3">3 rooms</option>
               <option value="4">4 rooms</option>
               <option value="5">5 rooms</option>
               <option value="6">6 rooms</option>
            </select>
         </div>
         <div class="box">
            <p>check in <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>check out <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>adults <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1" selected>1 adult</option>
               <option value="2">2 adults</option>
               <option value="3">3 adults</option>
               <option value="4">4 adults</option>
               <option value="5">5 adults</option>
               <option value="6">6 adults</option>
            </select>
         </div>
         <div class="box">
            <p>childs <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="0" selected>0 child</option>
               <option value="1">1 child</option>
               <option value="2">2 childs</option>
               <option value="3">3 childs</option>
               <option value="4">4 childs</option>
               <option value="5">5 childs</option>
               <option value="6">6 childs</option>
            </select>
         </div>
      </div>
      <input type="submit" value="book now" name="book" class="btn">
   </form>

</section>

<!-- reservation section ends -->

<!-- gallery section starts  -->

<section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/gallary-img-1.jpg" class="swiper-slide" alt="">
         <img src="images/gallary-img-2.jpg" class="swiper-slide" alt="">
         <img src="images/gallary-img-3.jpg" class="swiper-slide" alt="">
         <img src="images/gallary-img-4.jpg" class="swiper-slide" alt="">
         <img src="images/gallary-img-5.jpg" class="swiper-slide" alt="">
         <img src="images/gallary-img-6.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- gallery section ends -->

<!-- contact section starts  -->

<section class="contact" id="contact">

   <div class="row">

   <form action="" method="post">
         <h3>send us message</h3>
         <input type="text" name="name" required maxlength="50" placeholder="enter your name" class="box">
         <input type="email" name="email" required maxlength="50" placeholder="enter your email" class="box">
         <input type="number" name="number" required maxlength="10" placeholder="enter your number" class="box">
         <textarea name="msg" class="box" required maxlength="1000" placeholder="enter your message" cols="30" rows="10"></textarea>
         <input type="submit" value="send message" name="send" class="btn">
      </form>

      <div class="faq">
         <h3 class="title">frequently asked questions</h3>
         <div class="box active">
            <h3>how to cancel?</h3>
            <p>contact our hotel and cancel your booking</p>
         </div>
         <div class="box">
            <h3>is there any vacancy?</h3>
            <a href="#availability"> clich here to check availability</a>
         </div>
         <div class="box">
            <h3>what are payment methods?</h3>
            <p>we accept cash on delivery, UPI payment, credit card payment</p>
         </div>
         <div class="box">
            <h3>how many branches?</h3>
            <p>we have 1 branch <br>
               1. Besent Nagar, Chennai
            </p>
         </div>
      </div>

   </div>

</section>

<!-- contact section ends -->

<?php include 'footer.php'; ?>








<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'message.php'; ?>

</body>
</html>