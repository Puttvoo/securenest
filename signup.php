<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $_FILES['profile_picture'];

    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if (!file_exists('uploads/profile_pictures')) {
            mkdir('uploads/profile_pictures', 0777, true);
        }
        $target_dir = "uploads/profile_pictures/";
        $target_file = $target_dir . uniqid() . "_" . basename($profile_picture["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($profile_picture["tmp_name"] != '') {
            $check = getimagesize($profile_picture["tmp_name"]);
            if ($check === false) {
                $error = "File is not an image.";
                $uploadOk = 0;
            }
            if ($profile_picture["size"] > 2000000) {
                $error = "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            if (!in_array($fileType, ["jpg", "png", "jpeg", "gif"])) {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }
            if ($uploadOk == 0) {
                $error = "Sorry, your file was not uploaded.";
            } else {
                if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, password, profile_picture) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $firstname, $lastname, $username, $hashed_password, $target_file);

                    if ($stmt->execute()) {
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $error = "Profile picture is required.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png"/>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: gray;
    background-size: cover;
    background-position: center;
}
    </style>
</head>
<body>
    <div class="signup-box">
        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <h1>Sign Up</h1>
            <?php
            if (isset($error)) {
                echo "<p style='color:red;'>$error</p>";
            }
            ?>
            <div class="input-box">
                <input type="text" name="firstname" placeholder="Firstname" required>
            </div>
            <div class="input-box">
                <input type="text" name="lastname" placeholder="Lastname" required>
            </div>
            <div class="input-box">
                <input type="username" name="username" placeholder="username" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="input-box">
                <input type="file" name="profile_picture" required>
            </div>
            <button type="submit" class="btn">Submit</button>
            <p class="pr">By clicking the Sign Up button, you agree to our <br>
                <a class="aa" href="#">Terms and Conditions</a> and <a class="bb" href="#">Policy and Agreement</a>
            </p>
            <p class="para1">Already have an Account?<a class="cc" href="index.php">Login</a></p>
        </form>
    </div>
</body>
</html>
