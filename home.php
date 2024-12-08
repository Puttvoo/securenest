<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT firstname, lastname, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$profile_pic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default-profile.png'; 
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            display: flex;
            flex-direction: row;
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
        .side-bar .side-parent ul {
            list-style-type: none;
            padding: 0;
            width: 100%;
        }
        .side-bar .side-parent a {
            text-decoration: none;
            color: #fff;
            display: block;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            text-align: left;
        }
        .side-bar .side-parent a:hover {
            background-color: #575757;
        }
        .container {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
            display: flex;
            flex-direction: column;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .preview {
            margin-top: 20px;
        }
        .preview img, .preview video, .preview audio {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            width: 150px;
        }
        .profile-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}
    </style>
</head>
<body>
    <aside class="side-bar">
        <div class="profile">
            
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
            <h3><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h3>
        </div>
        <div class="side-parent">
            <ul>
                <a href="files.php"><li>Files</li></a>
                <a href="share.php"><li>Share</li></a>
                <a href="logout.php"><li>Logout</li></a>
            </ul>
        </div>
    </aside>

    <div class="container">
        <h1>File Storage</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Select file to upload:
            <input type="file" name="fileToUpload" id="fileToUpload" required>
            <input type="submit" value="Upload File" name="submit">
        </form>

        <div class="preview" id="preview" style="display:none;">
            <h2>File Preview</h2>
            <img id="previewImage" src="#" alt="Image Preview" style="display: none;">
            <video id="previewVideo" controls style="display: none;"></video>
            <audio id="previewAudio" controls style="display: none;"></audio>
            <p id="previewText"></p>
        </div>

        <h2>Uploaded Files</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Path</th>
                    <th>Preview</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM files WHERE uploaded_by = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_path = htmlspecialchars($row["path"]);
                        $file_type = htmlspecialchars($row["type"]);
                        $file_name = htmlspecialchars($row["name"]);

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["file_id"]) . "</td>";
                        echo "<td>" . $file_name . "</td>";
                        echo "<td>" . $file_path . "</td>";
                        echo "<td>";

                        if (in_array($file_type, ["jpg", "jpeg", "png", "gif"])) {
                            echo "<img src='$file_path' alt='Preview' style='max-width: 100px; max-height: 100px;'>";
                        } elseif (in_array($file_type, ["mp4", "avi", "mov", "wmv"])) {
                            echo "<video src='$file_path' controls style='max-width: 100px; max-height: 100px;'></video>";
                        } elseif (in_array($file_type, ["mp3", "wav", "flac"])) {
                            echo "<audio src='$file_path' controls style='max-width: 100px;'></audio>";
                        } else {
                            echo "No preview available";
                        }

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No files found.</td></tr>";
                }
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('fileToUpload').addEventListener('change', function() {
            const file = this.files[0];
            const previewImage = document.getElementById('previewImage');
            const previewVideo = document.getElementById('previewVideo');
            const previewAudio = document.getElementById('previewAudio');
            const previewText = document.getElementById('previewText');
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const fileType = file.type.split('/')[0];

                    previewImage.style.display = 'none';
                    previewVideo.style.display = 'none';
                    previewAudio.style.display = 'none';
                    previewText.style.display = 'none';

                    if (fileType === 'image') {
                        previewImage.style.display = 'block';
                        previewImage.src = e.target.result;
                    } else if (fileType === 'video') {
                        previewVideo.style.display = 'block';
                        previewVideo.src = e.target.result;
                    } else if (fileType === 'audio') {
                        previewAudio.style.display = 'block';
                        previewAudio.src = e.target.result;
                    } else {
                        previewText.style.display = 'block';
                        previewText.textContent = file.name;
                    }
                }
                reader.readAsDataURL(file);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
