<?php
// filepath: c:\xampp\htdocs\MyTailorSuite\settings.php

// Database connection
$conn = new mysqli('localhost', 'root', '', 'tailor_suite');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $userId = $_POST['user_id'];
    $targetDir = "uploads/profile_pics/";
    $targetFile = $targetDir . basename($_FILES['profile_pic']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is an image
    $check = getimagesize($_FILES['profile_pic']['tmp_name']);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (limit to 2MB)
    if ($_FILES['profile_pic']['size'] > 2000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            // Update profile_pic in the database
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $targetFile, $userId);
            if ($stmt->execute()) {
                echo "Profile picture uploaded successfully.";
            } else {
                echo "Error updating profile picture: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $userId = $_POST['user_id'];
    $theme = $_POST['theme'];
    $notificationsEnabled = isset($_POST['notifications_enabled']) ? 1 : 0;
    $language = $_POST['language'];

    // Update settings in the database
    $stmt = $conn->prepare("INSERT INTO settings (user_id, theme, notifications_enabled, language)
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE theme = VALUES(theme), notifications_enabled = VALUES(notifications_enabled), language = VALUES(language)");
    $stmt->bind_param("isis", $userId, $theme, $notificationsEnabled, $language);
    if ($stmt->execute()) {
        echo "Settings updated successfully.";
    } else {
        echo "Error updating settings: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>