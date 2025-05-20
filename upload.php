<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

require 'config.php';

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $file = $_FILES['resume'];
    $allowed_extensions = ['pdf', 'docx'];
    $upload_dir = 'uploads/';

    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Check extension
    if (!in_array($file_ext, $allowed_extensions)) {
        die("Invalid file type. Only PDF and DOCX files are allowed.");
    }

    // Generate unique file name
    $new_file_name = $student_id . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    // Move file
    if (move_uploaded_file($file_tmp, $destination)) {
        // Insert new resume record
        $stmt = $conn->prepare("INSERT INTO resumes (student_id, file_name, status, remark) VALUES (?, ?, 'pending', '')");
        $stmt->bind_param("ss", $student_id, $new_file_name);
        if ($stmt->execute()) {
            header("Location: dashboard.php?success=1");
            exit;
        } else {
            echo "Database error: " . $stmt->error;
        }
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "Invalid request.";
}
?>
