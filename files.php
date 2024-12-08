<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$file_type = isset($_GET['file_type']) ? $_GET['file_type'] : 'all';


$file_types = [
    'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
    'videos' => ['mp4', 'avi', 'mov', 'wmv'],
    'music' => ['mp3', 'wav', 'flac'],
    'images' => ['jpg', 'jpeg', 'png', 'gif'],
    'other' => []
];


$sql = "SELECT f.file_id, f.name, f.path, f.uploaded_at, f.uploaded_by, u.firstname, u.lastname
        FROM files f
        JOIN users u ON f.uploaded_by = u.id
        WHERE f.uploaded_by = ?";

if ($file_type !== 'all' && isset($file_types[$file_type])) {
    $placeholders = implode("', '", $file_types[$file_type]);
    $sql .= " AND f.type IN ('$placeholders')";
}

$sql .= " ORDER BY f.uploaded_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


if (!$result) {
    die("Error executing query: " . $conn->error);
}


$sql_user = "SELECT firstname, lastname, profile_picture FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$profile_pic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png"/>
    <style>

    </style>
</head>
<body>
<aside class="side-bar">
        <div class="profile">
            <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-picture">
            <h3><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h3>
        </div>
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
        <div class="files-container">
            <h1>Uploaded Files</h1>

            <form method="GET" action="">
                <label for="file_type">Filter by File Type:</label>
                <select name="file_type" id="file_type" onchange="this.form.submit()">
                    <option value="all" <?php if ($file_type == 'all') echo 'selected'; ?>>All Files</option>
                    <option value="documents" <?php if ($file_type == 'documents') echo 'selected'; ?>>Documents</option>
                    <option value="videos" <?php if ($file_type == 'videos') echo 'selected'; ?>>Videos</option>
                    <option value="music" <?php if ($file_type == 'music') echo 'selected'; ?>>Music</option>
                    <option value="images" <?php if ($file_type == 'images') echo 'selected'; ?>>Images</option>
                    <option value="other" <?php if ($file_type == 'other') echo 'selected'; ?>>Other Files</option>
                </select>
            </form>

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Uploader</th>
                            <th>Upload Date</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
                                <td><a href="download.php?file_id=<?php echo $row['file_id']; ?>">Download</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No files uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$stmt_user->close();
$conn->close();

