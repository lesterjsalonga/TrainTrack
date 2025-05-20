<?php
session_start();
require '../config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$username_value = '';
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $username_value = htmlspecialchars($username);

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hashed_password);
            if ($insert->execute()) {
                $registration_success = true;
            } else {
                $error = "Failed to register. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Register - TrainTrack</title>
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

  p.login-link {
    margin-top: 26px;
    font-size: 0.9rem;
    color: #3a65c7;
    user-select: none;
  }

  p.login-link a {
    color: #3053b4;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  p.login-link a:hover {
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

<div class="container" role="main" aria-label="Admin Registration Form">
  <h2>Admin Register</h2>

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
      value="<?= $username_value ?>"
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

    <label for="confirm_password">Confirm Password</label>
    <input
      id="confirm_password"
      type="password"
      name="confirm_password"
      required
      placeholder="Confirm your password"
      aria-required="true"
    >

    <button type="submit" aria-label="Register new admin">Register</button>
  </form>

  <p class="login-link">
    Already have an account? <a href="admin_login.php">Login here</a>
  </p>
</div>

<?php if ($registration_success): ?>
<script>
  alert("Admin registered successfully! Redirecting to login...");
  window.location.href = "admin_login.php";
</script>
<?php endif; ?>

</body>
</html>
