<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_COOKIE['remember_user'])) {
    $_SESSION['student_id'] = $_COOKIE['remember_user'];
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>TrainTrack Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="login-card">
    <h2>TrainTrack Login</h2>
    <form action="login.php" method="POST">
      <label>Student ID</label>
      <input type="text" name="student_id" required>
      <label>Password</label>
      <input type="password" name="password" required>

      <div class="checkbox-row">
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Remember Me</label>
      </div>
      <button type="submit">Login</button>
    </form>

    <!-- Register link -->
    <p style="margin-top: 15px; text-align: center;">
      Don't have an account? <a href="register.php">Register here</a>
    </p>
    <a href="admin/admin_login.php">Go to Admin Panel</a>

  </div>
</div>
</body>
</html>
