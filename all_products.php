<?php

include 'components/connect.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>All products</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include 'components/header.php'; ?>
<!-- header section ends -->

<!-- view all products section starts  -->

<section class="all-products">

   <div class="heading"><h1>All products</h1></div>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){

         $product_id = $fetch_product['id'];

         $count_reviews = $conn->prepare("SELECT * FROM `reviews` WHERE product_id = ?");
         $count_reviews->execute([$product_id]);
         $total_reviews = $count_reviews->rowCount();
   ?>
   <div class="box">
      <img src="uploaded_files/<?= $fetch_product['image']; ?>" alt="" class="image">
      <h3 class="title"><?= $fetch_product['title']; ?></h3>
      <p class="total-reviews"><i class="fas fa-star"></i> <span><?= $total_reviews; ?></span></p>
      <a href="view_product.php?get_id=<?= $product_id; ?>" class="inline-btn">view product</a>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

</section>

<!-- view all products section ends -->


<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/alerts.php'; ?>

</body>
</html>