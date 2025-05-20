<?php
session_start();
require 'config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    // Basic validation
    if (empty($name) || empty($student_id) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if student ID or email already exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ? OR email = ?");
        $stmt->bind_param("ss", $student_id, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Student ID or Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $student_id, $name, $email, $hashed_password);
            if ($stmt->execute()) {
                header("Location: index.php?registered=success");
                exit;
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>TrainTrack Registration</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="register-card">
    <h2>Register for TrainTrack</h2>

    <?php if (isset($error)) : ?>
      <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
      <label>Full Name</label>
      <input type="text" name="name" required>

      <label>Student ID</label>
      <input type="text" name="student_id" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit">Register</button>
    </form>

    <p style="margin-top: 15px; text-align: center;">
      Already have an account? <a href="index.php">Login here</a>
    </p>
  </div>
</div>
</body>
</html>
