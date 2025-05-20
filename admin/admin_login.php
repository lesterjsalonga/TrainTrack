<?php
session_start();
require '../config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['remember_admin'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $_COOKIE['remember_admin'];
    header("Location: dashboard.php");
    exit;
}

$error = '';
$remembered_username = $_COOKIE['remember_admin'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];

            if (!empty($_POST['remember'])) {
                setcookie("remember_admin", $admin['username'], time() + (86400 * 7), "/");
            } else {
                setcookie("remember_admin", "", time() - 3600, "/");
            }

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login - TrainTrack</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

  /* Reset */
  * {
    box-sizing: border-box;
  }

  body, html {
    margin: 0; padding: 0;
    height: 100%;
    font-family: 'Inter', sans-serif;
    background: #e6f0fa; /* very light blue */
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .container {
    background: #ffffff;
    padding: 45px 40px;
    border-radius: 20px;
    box-shadow: 0 12px 28px rgba(25, 60, 120, 0.15);
    max-width: 420px;
    width: 100%;
    text-align: center;
  }

  h2 {
    margin-bottom: 28px;
    color: #1f3c88; /* deep blue */
    font-weight: 700;
    font-size: 2rem;
    letter-spacing: 0.04em;
    user-select: none;
  }

  form {
    display: flex;
    flex-direction: column;
  }

  label {
    text-align: left;
    margin-bottom: 8px;
    font-weight: 600;
    color: #405f9e;
    font-size: 0.95rem;
    user-select: none;
  }

  input[type="text"],
  input[type="password"] {
    padding: 14px 18px;
    margin-bottom: 24px;
    border: 2px solid #aac8f7;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background-color: #f7faff;
    color: #1a2a57;
  }

  input[type="text"]::placeholder,
  input[type="password"]::placeholder {
    color: #9bb9e9;
  }

  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: #3a65c7;
    outline: none;
    box-shadow: 0 0 10px #3a65c7aa;
    background-color: #fff;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    color: #405f9e;
    font-weight: 500;
    font-size: 0.9rem;
    user-select: none;
  }

  .checkbox-label input {
    margin-right: 12px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3a65c7;
    border-radius: 4px;
  }

  button {
    background: #3a65c7;
    border: none;
    color: white;
    font-weight: 700;
    padding: 15px 0;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1.1rem;
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.15s ease;
    box-shadow: 0 6px 15px rgba(58, 101, 199, 0.4);
    user-select: none;
  }

  button:hover {
    background: #2e4fa1;
    box-shadow: 0 8px 20px rgba(46, 79, 161, 0.5);
    transform: translateY(-3px);
  }

  button:active {
    transform: translateY(0);
    box-shadow: 0 4px 10px rgba(46, 79, 161, 0.35);
  }

  .error {
    color: #d04545;
    margin-bottom: 22px;
    font-weight: 600;
    font-size: 0.95rem;
    user-select: none;
  }

  p.register-link {
    margin-top: 26px;
    font-size: 0.9rem;
    color: #3a65c7;
    user-select: none;
  }

  p.register-link a {
    color: #3053b4;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  p.register-link a:hover {
    color: #1f3c88;
    text-decoration: underline;
  }

  @media (max-width: 480px) {
    .container {
      padding: 35px 30px;
      border-radius: 15px;
    }
  }
</style>
</head>
<body>

<div class="container" role="main" aria-label="Admin Login Form">
  <h2>Admin Login</h2>

  <?php if (!empty($error)): ?>
    <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off" novalidate>
    <label for="username">Username</label>
    <input
      id="username"
      type="text"
      name="username"
      required
      value="<?= htmlspecialchars($remembered_username) ?>"
      placeholder="Enter your username"
      autofocus
      aria-required="true"
    >

    <label for="password">Password</label>
    <input
      id="password"
      type="password"
      name="password"
      required
      placeholder="Enter your password"
      aria-required="true"
    >

    <label class="checkbox-label" for="remember">
      <input
        type="checkbox"
        id="remember"
        name="remember"
        <?= $remembered_username ? 'checked' : '' ?>
        aria-checked="<?= $remembered_username ? 'true' : 'false' ?>"
      >
      Remember Me
    </label>

    <button type="submit" aria-label="Login to admin panel">Login</button>
  </form>

  <p class="register-link">
    Don't have an account? <a href="admin_register.php">Register here</a>
  </p>
</div>

</body>
</html>
