<?php

session_start();
include 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$sql = "SELECT firstname, lastname, username, profile_picture, bio FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();
} else {
    echo "No user found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            margin: 0;
            background-color: gray;
            height: 100vh;
        }
        .container {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
        }
        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .profile-name {
            font-size: 24px;
            margin: 10px 0;
        }
        .profile-username {
            color: #666;
        }
        .profile-bio {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <aside class="side-bar">
        <div class="side-parent">
            <ul>
                <a href="home.php"><li>Home</li></a>
                <a href="files.php"><li>Files</li></a>
                <a href="share.php"><li>Share</li></a>
                <a href="logout.php"><li>Logout</li></a>
            </ul>
        </div>
    </aside>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                <h1 class="profile-name"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h1>
                <p class="profile-username"><?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            <div class="profile-bio">
                <h2>User Bio</h2>
                <p><?php echo htmlspecialchars($user['bio'] ?? 'No bio available'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>


<?php
$conn->close();
?>
