<?php
include 'components/connect.php';

if (isset($_GET['get_id'])) {
   $get_id = $_GET['get_id'];
} else {
   $get_id = '';
   header('location:all_products.php');
}

if(isset($_POST['delete_review'])){

   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $delete_images = $conn->prepare("DELETE FROM review_images WHERE review_id = ?");
$delete_images->execute([$delete_id]);

// Then delete associated videos
$delete_videos = $conn->prepare("DELETE FROM review_videos WHERE review_id = ?");
$delete_videos->execute([$delete_id]);

// Now delete the review itself
$delete_review = $conn->prepare("DELETE FROM reviews WHERE id = ?");
$delete_review->execute([$delete_id]);

// Check if the review was deleted successfully
if ($delete_review->rowCount() > 0) {
    // Review deleted successfully
    $success_msg[] = 'Review deleted!';
} else {
    // No rows deleted, likely because the review ID was not found
    $warning_msg[] = 'Review already deleted!';
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Product</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


</head>

<body>

   <?php include 'components/header.php'; ?>


   <!-- view products section starts  -->

   <section class="view-product">

      <div class="heading">
         <h1>Product details</h1> <a href="all_products.php" class="inline-option-btn" style="margin-top: 0;">All Products</a>
      </div>

      <?php
      $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_product->execute([$get_id]);
      if ($select_product->rowCount() > 0) {
         while ($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)) {

            $total_ratings = 0;
            $rating_1 = 0;
            $rating_2 = 0;
            $rating_3 = 0;
            $rating_4 = 0;
            $rating_5 = 0;

            $select_ratings = $conn->prepare("SELECT * FROM `reviews` WHERE product_id = ?");
            $select_ratings->execute([$fetch_product['id']]);
            $total_reivews = $select_ratings->rowCount();
            while ($fetch_rating = $select_ratings->fetch(PDO::FETCH_ASSOC)) {
               $total_ratings += $fetch_rating['rating'];
               if ($fetch_rating['rating'] == 1) {
                  $rating_1 += $fetch_rating['rating'];
               }
               if ($fetch_rating['rating'] == 2) {
                  $rating_2 += $fetch_rating['rating'];
               }
               if ($fetch_rating['rating'] == 3) {
                  $rating_3 += $fetch_rating['rating'];
               }
               if ($fetch_rating['rating'] == 4) {
                  $rating_4 += $fetch_rating['rating'];
               }
               if ($fetch_rating['rating'] == 5) {
                  $rating_5 += $fetch_rating['rating'];
               }
            }

            if ($total_reivews != 0) {
               $average = round($total_ratings / $total_reivews, 1);
            } else {
               $average = 0;
            }

      ?>
            <div class="row">
               <div class="col">
                  <img src="uploaded_files/<?= htmlspecialchars($fetch_product['image']); ?>" alt="" class="image">
                  <h3 class="title"><?= htmlspecialchars($fetch_product['title']); ?></h3>
               </div>
               <div class="col">
                  <div class="flex">
                     <div class="total-reviews">
                        <h3><?= $average; ?><i class="fas fa-star"></i></h3>
                        <p><?= $total_reivews; ?> reviews</p>
                     </div>
                     <div class="total-ratings">
                        <p>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <span><?= $rating_5; ?></span>
                        </p>
                        <p>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <span><?= $rating_4; ?></span>
                        </p>
                        <p>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <span><?= $rating_3; ?></span>
                        </p>
                        <p>
                           <i class="fas fa-star"></i>
                           <i class="fas fa-star"></i>
                           <span><?= $rating_2; ?></span>
                        </p>
                        <p>
                           <i class="fas fa-star"></i>
                           <span><?= $rating_1; ?></span>
                        </p>
                     </div>
                  </div>
               </div>
            </div>
      <?php
         }
      } else {
         echo '<p class="empty">Product is missing!</p>';
      }
      ?>

   </section>

   <!-- view products section ends -->

   <!-- reviews section starts  -->

   <section class="reviews-container">

      <div class="heading">
         <h1>User's reviews</h1> <a href="add_review.php?get_id=<?= htmlspecialchars($get_id); ?>" class="inline-btn" style="margin-top: 0;">Add review</a>
      </div>

      <div class="box-container">

         <?php
         $select_reviews = $conn->prepare("SELECT * FROM `reviews` WHERE product_id = ?");
         $select_reviews->execute([$get_id]);
         if ($select_reviews->rowCount() > 0) {
            while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="box" <?php if ($fetch_review['user_id'] == $user_id) {
                                    echo 'style="order:-1;"';
                                 }; ?>>
                  <?php
                  $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                  $select_user->execute([$fetch_review['user_id']]);
                  while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                     <div class="user">
                        <?php if ($fetch_user['image'] != '') { ?>
                           <img src="uploaded_files/<?= htmlspecialchars($fetch_user['image']); ?>" alt="">
                        <?php } else { ?>
                           <h3><?= htmlspecialchars(substr($fetch_user['name'], 0, 1)); ?></h3>
                        <?php }; ?>
                        <div>
                           <p><?= htmlspecialchars($fetch_user['name']); ?></p>
                           <span><?= htmlspecialchars($fetch_review['date']); ?></span>
                        </div>
                     </div>
                  <?php }; ?>
                  <div class="ratings">
                     <?php if ($fetch_review['rating'] == 1) { ?>
                        <p style="background:var(--red);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
                     <?php }; ?>
                     <?php if ($fetch_review['rating'] == 2) { ?>
                        <p style="background:var(--orange);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
                     <?php }; ?>
                     <?php if ($fetch_review['rating'] == 3) { ?>
                        <p style="background:var(--orange);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
                     <?php }; ?>
                     <?php if ($fetch_review['rating'] == 4) { ?>
                        <p style="background:var(--green);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
                     <?php }; ?>
                     <?php if ($fetch_review['rating'] == 5) { ?>
                        <p style="background:var(--green);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
                     <?php }; ?>
                  </div>
                  <h3 class="title"><?= htmlspecialchars($fetch_review['title']); ?></h3>
                  <?php if ($fetch_review['description'] != '') { ?>
                     <p class="description"><?= htmlspecialchars($fetch_review['description']); ?></p>
                  <?php }; ?>
                  <div class="review-media">
                     <?php
                     $review_id = $fetch_review['id'];
                     $select_images = $conn->prepare("SELECT * FROM `review_images` WHERE review_id = ?");
                     $select_images->execute([$review_id]);
                     $images = [];
                     while ($fetch_image = $select_images->fetch(PDO::FETCH_ASSOC)) {
                        $images[] = $fetch_image['image_path'];
                     }
                     if (!empty($images)) {
                        foreach ($images as $image) {
                     ?>
                           <div class="media-container" data-type="image" data-src="uploaded_files/<?= htmlspecialchars($image); ?>">
                              <img src="uploaded_files/<?= htmlspecialchars($image); ?>" alt="Review Image">
                           </div>
                        <?php
                        }
                     }

                     $select_videos = $conn->prepare("SELECT * FROM `review_videos` WHERE review_id = ?");
                     $select_videos->execute([$review_id]);
                     $videos = [];
                     while ($fetch_video = $select_videos->fetch(PDO::FETCH_ASSOC)) {
                        $videos[] = $fetch_video['video_path'];
                     }
                     if (!empty($videos)) {
                        foreach ($videos as $video) {
                        ?>
                           <div class="media-container" data-type="video" data-src="uploaded_files/<?= htmlspecialchars($video); ?>">
                              <video>
                                 <source src="uploaded_files/<?= htmlspecialchars($video); ?>" type="video/mp4">
                                 Your browser does not support the video tag.
                              </video>
                              <i class="fas fa-play-circle play-icon"></i>
                           </div>
                     <?php
                        }
                     }
                     ?>
                  </div>
                  <?php if ($fetch_review['user_id'] == $user_id) { ?>
                     <form action="" method="post" class="flex-btn">
                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($fetch_review['id']); ?>">
                        <a href="update_review.php?get_id=<?= htmlspecialchars($fetch_review['id']); ?>" class="inline-option-btn">Edit review</a>
                        <input type="submit" value="Delete review" class="inline-delete-btn" name="delete_review" onclick="return confirm('Delete this review?');">
                     </form>
                  <?php }; ?>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">No reviews added yet!</p>';
         }
         ?>

      </div>
   </section>
   <!-- reviews section ends -->
   <div id="myModal" class="modal">
      <span class="close">&times;</span>
      <img class="modal-content" id="modal-img">
      <video class="modal-content" id="modal-video" controls></video>
   </div>
   <!-- sweetalert cdn link  -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
   <!-- custom js file link  -->
   <script src="js/script.js"></script>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    document.querySelectorAll('.media-container').forEach(item => {
        item.addEventListener('click', event => {
            modal.style.display = "block";
            var mediaType = item.getAttribute('data-type');
            var mediaSrc = item.getAttribute('data-src');
            if (mediaType === 'image') {
                document.getElementById("modal-img").src = mediaSrc;
                document.getElementById("modal-img").style.display = "block";
                document.getElementById("modal-video").style.display = "none";
            } else if (mediaType === 'video') {
                document.getElementById("modal-video").src = mediaSrc;
                document.getElementById("modal-img").style.display = "none";
                document.getElementById("modal-video").style.display = "block";
                document.getElementById("modal-video").play();
            }
        });
    });

    span.onclick = function() {
        modal.style.display = "none";
        document.getElementById("modal-img").src = "";
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.getElementById("modal-img").src = "";
            document.getElementById("modal-video").pause();
            document.getElementById("modal-video").src = "";
        }
    }

    document.querySelectorAll('.remove-btn').forEach(removeBtn => {
    removeBtn.addEventListener('click', event => {
        const mediaBox = event.target.closest('.media-box');
        if (mediaBox) {
            mediaBox.remove();
            const mediaContainer = document.getElementById('uploaded-media');
            if (mediaContainer.querySelectorAll('.media-box').length === 0) {
                document.getElementById('add-more-btn').style.display = 'none'; // Hide the "Add more" button if no media is present
            }
        }
    });
});

});


  </script>
   <?php include 'components/alerts.php'; ?>
</body>

</html>