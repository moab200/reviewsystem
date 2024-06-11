<?php

include 'components/connect.php';

if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    $get_id = '';
    header('location:all_products.php');
    exit; 
}

if (isset($_POST['submit'])) {

    if ($user_id != '') {

        $id = create_unique_id();
        $title = $_POST['title'];
        $title = filter_var($title, FILTER_SANITIZE_STRING);
        $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
        $rating = $_POST['rating'];
        $rating = filter_var($rating, FILTER_SANITIZE_STRING);

        $uploaded_images = [];
        $uploaded_videos = [];

        if (!empty($_FILES['media']['name'][0])) {
            foreach ($_FILES['media']['name'] as $key => $media_name) {
                if (!empty($media_name)) { 
                    $temp_name = $_FILES['media']['tmp_name'][$key];
                    $new_media_name = uniqid('', true) . '_' . $media_name;
                    $upload_path = 'uploaded_files/' . $new_media_name;
                    if (move_uploaded_file($temp_name, $upload_path)) {
                        $file_type = mime_content_type($upload_path);
                        if (strpos($file_type, 'image') !== false) {
                            $uploaded_images[] = $new_media_name;
                        } elseif (strpos($file_type, 'video') !== false) {
                            $uploaded_videos[] = $new_media_name;
                        }
                    } else {
                        echo "Failed to upload file: $media_name<br>";
                    }
                }
            }
        }
        $verify_review = $conn->prepare("SELECT * FROM `reviews` WHERE product_id = ? AND user_id = ?");
        $verify_review->execute([$get_id, $user_id]);

        if ($verify_review->rowCount() > 0) {
            $warning_msg[] = 'Your review already added!';
        } else {
            $add_review = $conn->prepare("INSERT INTO `reviews`(id, product_id, user_id, rating, title, description) VALUES(?,?,?,?,?,?)");
            $add_review->execute([$id, $get_id, $user_id, $rating, $title, $description]);

            

            foreach ($uploaded_images as $image) {
                $add_image = $conn->prepare("INSERT INTO `review_images`(review_id, image_path) VALUES(?,?)");
                $add_image->execute([$id, $image]);
            }

            foreach ($uploaded_videos as $video) {
                $add_video = $conn->prepare("INSERT INTO `review_videos`(review_id, video_path) VALUES(?,?)");
                $add_video->execute([$id, $video]);
            }

            $success_msg[] = 'Review added!';
        }

    } else {
        $warning_msg[] = 'Please login first!';
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add review</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<!-- header section starts  -->
<?php include 'components/header.php'; ?>
<!-- header section ends -->

<!-- add review section starts  -->

<section class="account-form">

    <form action="" method="post" enctype="multipart/form-data">
        <h3>Post your review</h3>
        <p class="placeholder">Review title <span>*</span></p>
        <input type="text" name="title" required maxlength="50" placeholder="Enter review title" class="box">
        <p class="placeholder">Review description</p>
        <textarea name="description" class="box" placeholder="Enter review description" maxlength="1000" cols="30" rows="10"></textarea>
        <p class="placeholder">Review rating <span>*</span></p>
        <select name="rating" class="box" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <div class="uploaded-media" id="uploaded-media"></div>
        <div class="media-upload">
            <p class="placeholder">Upload Images/Videos</p>
            <input type="file" name="media[]" class="box" accept="image/*,video/*" multiple onchange="previewMedia(event)">
        </div>
        <div class="add-more-btn" id="add-more-btn" style="display: none;" onclick="addMoreMedia()">+ Add more Pics/Videos</div>
        <input type="submit" value="Submit review" name="submit" class="btn">
        <a href="view_product.php?get_id=<?= $get_id; ?>" class="option-btn">Go back</a>
    </form>

</section>

<!-- add review section ends -->
<section class="reviews">
    <h2>Reviews</h2>
    <?php
    $review_query = $conn->prepare("SELECT * FROM `reviews` WHERE product_id = ?");
    $review_query->execute([$get_id]);
    $reviews = $review_query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reviews as $review) {
        echo '<div class="review">';
        echo '<h3>' . htmlspecialchars_decode($review['title'], ENT_QUOTES) . '</h3>';
        echo '<p>' . htmlspecialchars_decode($review['description'], ENT_QUOTES) . '</p>';
        echo '</div>';
    }
    ?>
</section>

<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/alerts.php'; ?>

</body>
</html>
