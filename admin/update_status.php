<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resume_id = $_POST['resume_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE resumes SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $resume_id);
    $stmt->execute();
    header("Location: dashboard.php");
}
