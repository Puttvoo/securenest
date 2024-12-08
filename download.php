<?php
session_start();
include 'config.php';

function decrypt_file($sourcePath, $encryptionKey) {
    $fileHandle = fopen($sourcePath, 'rb');
    if (!$fileHandle) {
        return false;
    }

    $iv = fread($fileHandle, 16);
    $decryptedContents = '';

    while (!feof($fileHandle)) {
        $chunk = fread($fileHandle, 4096 + 16);
        $decryptedChunk = openssl_decrypt($chunk, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        if ($decryptedChunk === false) {
            fclose($fileHandle);
            return false;
        }
        $decryptedContents .= $decryptedChunk;
    }

    fclose($fileHandle);
    return $decryptedContents;
}


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : null;

if ($file_id === null || $file_id <= 0) {
    echo "File ID is missing or invalid.";
    exit();
}


$sql = "SELECT * FROM files WHERE file_id = ? AND uploaded_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "File not found or you do not have permission to share this file.";
    exit();
}

$file = $result->fetch_assoc();
$filePath = $file['path'];
$fileName = $file['name'];
$fileType = $file['type'];

$encryptionKey = 'your-encryption-key-here';
$decryptedContents = decrypt_file($filePath, $encryptionKey);

if ($decryptedContents === false) {
    echo "Failed to decrypt file chunk.";
    exit();
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($decryptedContents));
echo $decryptedContents;

$stmt->close();
$conn->close();

