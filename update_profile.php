<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

require 'config.php';

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash new password if provided
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $password, $student_id);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $student_id);
    }

    if ($stmt->execute()) {
        $_SESSION['upload_msg'] = 'Profile updated successfully.';
    } else {
        $_SESSION['upload_msg'] = 'Failed to update profile.';
    }

    header('Location: dashboard.php');
    exit;
}
?>
