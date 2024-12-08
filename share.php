<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : null;


$sql = "SELECT file_id, name FROM files WHERE uploaded_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($file_id !== null && $file_id > 0) {
    $file_sql = "SELECT * FROM files WHERE file_id = ? AND uploaded_by = ?";
    $file_stmt = $conn->prepare($file_sql);
    $file_stmt->bind_param("ii", $file_id, $user_id);
    $file_stmt->execute();
    $result = $file_stmt->get_result();

    if ($result->num_rows == 0) {
        echo "File not found or you do not have permission to share this file.";
        exit();
    }

    $file = $result->fetch_assoc();


    $share_token = bin2hex(random_bytes(16));
    $share_link = "http://localhost/SecureNest/download.php?file_id=" . urlencode($file['file_id']) . "&token=" . $share_token;


    $update_sql = "UPDATE files SET share_token = ? WHERE file_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $share_token, $file['file_id']);
    $update_stmt->execute();

    $result = Builder::create()
        ->writer(new PngWriter())
        ->writerOptions([])
        ->data($share_link)
        ->encoding(new Encoding('UTF-8'))
        ->size(300)
        ->margin(10)
        ->build();

    $qr_code_base64 = base64_encode($result->getString());

    $file_stmt->close();
}


$sql = "SELECT firstname, lastname, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$profile_pic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default-profile.png';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share File</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: gray;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .side-bar {
            width: 200px;
            background-color: darkblue;
            color: #fff;
            position: fixed;
            height: 100vh;
            overflow: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .side-bar .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .side-bar .profile img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .side-bar .profile h3 {
            margin: 10px 0 0;
            color: #fff;
        }

        .side-bar ul {
            list-style-type: none;
            padding: 0;
            width: 100%;
        }

        .side-bar a {
            text-decoration: none;
            color: #fff;
            display: block;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            text-align: left;
        }

        .side-bar a:hover {
            background-color: #575757;
        }

        .share-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<aside class="side-bar">
    <div class="profile">
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
        <h3><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h3>
    </div>
    <ul>
        <a href="home.php"><li>Home</li></a>
        <a href="files.php"><li>Files</li></a>
        <a href="share.php"><li>Share</li></a>
        <a href="logout.php"><li>Logout</li></a>
    </ul>
</aside>

<div class="share-container">
    <h1>Share File</h1>
    <form method="POST" action="">
        <label for="file_id">Select a file to share:</label>
        <select name="file_id" id="file_id" required>
            <option value="">-- Select File --</option>
            <?php foreach ($files as $f): ?>
                <option value="<?php echo $f['file_id']; ?>" <?php echo isset($file_id) && $file_id == $f['file_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Generate Share Link</button>
    </form>

    <?php if (isset($qr_code_base64) && isset($file)): ?>
        <h2>Shareable Link</h2>
        <p><a href="<?php echo htmlspecialchars($share_link); ?>" target="_blank"><?php echo htmlspecialchars($share_link); ?></a></p>

        <h2>QR Code</h2>
        <img src="data:image/png;base64,<?php echo $qr_code_base64; ?>" alt="QR Code">
        <p>Scan the QR code or use the link above to share the file.</p>
    <?php elseif ($file_id): ?>
        <p>No QR code generated. Please select a valid file.</p>
    <?php endif; ?>
</div>
</body>
</html>
