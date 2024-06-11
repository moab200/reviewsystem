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

        $title = $_POST['title'];
        $title = filter_var($title, FILTER_SANITIZE_STRING);
        $description = $_POST['description'];
        $description = filter_var($description, FILTER_SANITIZE_STRING);
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

        // Update review details
        $update_review = $conn->prepare("UPDATE `reviews` SET rating = ?, title = ?, description = ? WHERE id = ?");
        $update_review->execute([$rating, $title, $description, $get_id]);

        // Delete existing images and videos
        $delete_images = $conn->prepare("DELETE FROM `review_images` WHERE review_id = ?");
        $delete_images->execute([$get_id]);
        $delete_videos = $conn->prepare("DELETE FROM `review_videos` WHERE review_id = ?");
        $delete_videos->execute([$get_id]);

        // Insert new images
        foreach ($uploaded_images as $image) {
            $add_image = $conn->prepare("INSERT INTO `review_images` (review_id, image_path) VALUES (?, ?)");
            $add_image->execute([$get_id, $image]);
        }

        // Insert new videos
        foreach ($uploaded_videos as $video) {
            $add_video = $conn->prepare("INSERT INTO `review_videos` (review_id, video_path) VALUES (?, ?)");
            $add_video->execute([$get_id, $video]);
        }

        $success_msg[] = 'Review updated!';

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
    <title>Update Review</title>

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<!-- Header section starts -->
<?php include 'components/header.php'; ?>
<!-- Header section ends -->

<!-- Update reviews section starts -->
<section class="account-form">

    <?php
        $select_review = $conn->prepare("SELECT * FROM `reviews` WHERE id = ? LIMIT 1");
        $select_review->execute([$get_id]);
        if($select_review->rowCount() > 0){
            while($fetch_review = $select_review->fetch(PDO::FETCH_ASSOC)){
    ?>
    <form action="" method="post" enctype="multipart/form-data">
        <h3>Edit Your Review</h3>
        <p class="placeholder">Review Title <span>*</span></p>
        <input type="text" name="title" required maxlength="50" placeholder="Enter review title" class="box" value="<?= $fetch_review['title']; ?>">
        <p class="placeholder">Review Description</p>
        <textarea name="description" class="box" placeholder="Enter review description" maxlength="1000" cols="30" rows="10"><?= $fetch_review['description']; ?></textarea>
        <p class="placeholder">Review Rating <span>*</span></p>
        <select name="rating" class="box" required>
            <option value="<?= $fetch_review['rating']; ?>"><?= $fetch_review['rating']; ?></option>
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
        <input type="submit" value="Update Review" name="submit" class="btn">
        <a href="view_product.php?get_id=<?= $fetch_review['product_id']; ?>" class="option-btn">Go back</a>
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">Something went wrong!</p>';
        }
    ?>

</section>
<!-- Update reviews section ends -->

<!-- SweetAlert CDN link -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

<?php include 'components/alerts.php'; ?>

</body>
</html>
