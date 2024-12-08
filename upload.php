<?php
session_start();
include 'config.php';

function generate_iv($length = 16) {
    return openssl_random_pseudo_bytes($length);
}

function encrypt_file($sourcePath, $destinationPath, $encryptionKey) {
    $iv = generate_iv();
    $fileHandle = fopen($sourcePath, 'rb');
    $outputHandle = fopen($destinationPath, 'wb');

    if ($fileHandle && $outputHandle) {
        fwrite($outputHandle, $iv);

        while (!feof($fileHandle)) {
            $chunk = fread($fileHandle, 4096);
            $encryptedChunk = openssl_encrypt($chunk, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
            fwrite($outputHandle, $encryptedChunk);
        }

        fclose($fileHandle);
        fclose($outputHandle);
    } else {
        if ($fileHandle) fclose($fileHandle);
        if ($outputHandle) fclose($outputHandle);
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowedFileTypes = [
        "jpg", "jpeg", "png", "gif",
        "mp3", "wav", "flac",
        "mp4", "avi", "mov", "wmv",
        "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt"
    ];

    if (!in_array($fileType, $allowedFileTypes)) {
        $uploadOk = 0;
        $error_message = "Sorry, only certain file formats are allowed.";
    }

    if (file_exists($target_file)) {
        $uploadOk = 0;
        $error_message = "Sorry, file already exists.";
    }

    if ($_FILES["fileToUpload"]["size"] > 1000000000) {
        $uploadOk = 0;
        $error_message = "Sorry, your file is too large.";
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $encrypted_file = $target_file . '.enc';
            $encryptionKey = 'your-encryption-key-here';

            if (encrypt_file($target_file, $encrypted_file, $encryptionKey)) {
                unlink($target_file);

                $stmt = $conn->prepare("INSERT INTO files (name, path, type, size, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $name, $path, $type, $size, $uploaded_by);

                $name = basename($_FILES["fileToUpload"]["name"]);
                $path = $encrypted_file;
                $type = $fileType;
                $size = $_FILES["fileToUpload"]["size"];
                $uploaded_by = $_SESSION['user_id'];

                if ($stmt->execute()) {
                    header("Location: home.php");
                    exit();
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }

                $stmt->close();
            } else {
                $error_message = "Sorry, there was an error encrypting your file.";
            }
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <style>
        /* Popup styling */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f44336;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .popup button {
            background: white;
            color: #f44336;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <script>
        function showPopup(message) {
            const popup = document.getElementById('errorPopup');
            popup.querySelector('p').textContent = message;
            popup.style.display = 'block';
        }

        function closePopup() {
            document.getElementById('errorPopup').style.display = 'none';
        }

        window.onload = function() {
            <?php if (isset($error_message)): ?>
                showPopup("<?php echo $error_message; ?>");
            <?php endif; ?>
        };
    </script>
</head>
<body>

    <!-- Popup Element -->
    <div id="errorPopup" class="popup">
        <p></p>
        <button onclick="closePopup()">Close</button>
    </div>

    <!-- File upload form -->
    <form action="" method="post" enctype="multipart/form-data">
        <label>Select file to upload:</label>
        <input type="file" name="fileToUpload" id="fileToUpload">
        <button type="submit" name="submit">Upload</button>
    </form>

</body>
</html>
