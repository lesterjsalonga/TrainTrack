<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user['student_id'];

            // Set cookie if "Remember Me" is checked
            if (!empty($_POST['remember'])) {
                setcookie("remember_user", $user['student_id'], time() + (86400 * 7), "/"); // 7 days
            } else {
                setcookie("remember_user", "", time() - 3600, "/"); // Clear cookie if unchecked
            }

            header("Location: dashboard.php");
            exit;
        }
    }

    echo "<script>alert('Invalid login'); window.location='index.php';</script>";
}
?>
