<?php
session_start();
require '../config.php';  // Your DB connection

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION['admin_username'] ?? null;
if (!$admin_username) {
    echo "No admin info found in session.";
    exit;
}

// Fetch admin info from database
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Admin not found.";
    exit;
}

$admin = $result->fetch_assoc();

$update_message = '';
$error_message = '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);
    
    // Validate new username
    if (empty($new_username)) {
        $error_message = "Username cannot be empty.";
    } else {
        if ($new_username !== $admin_username) {
            $check_stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $check_stmt->bind_param("s", $new_username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $error_message = "Username already taken.";
            }
            $check_stmt->close();
        }
    }

    // Handle image upload if no errors
    $profile_image_path = $admin['profile_image'] ?? '';
    if (empty($error_message) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $upload_dir = '../uploads/admin_profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_image_name = uniqid('admin_', true) . "." . $ext;
            $target_file = $upload_dir . $new_image_name;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                if ($profile_image_path && file_exists('../' . $profile_image_path)) {
                    unlink('../' . $profile_image_path);
                }
                $profile_image_path = 'uploads/admin_profiles/' . $new_image_name;
            } else {
                $error_message = "Failed to upload profile image.";
            }
        } else {
            $error_message = "Invalid image type. Only JPG, PNG, GIF allowed.";
        }
    }

    if (empty($error_message)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE admins SET username = ?, password = ?, profile_image = ? WHERE username = ?");
            $stmt_update->bind_param("ssss", $new_username, $hashed_password, $profile_image_path, $admin_username);
        } else {
            $stmt_update = $conn->prepare("UPDATE admins SET username = ?, profile_image = ? WHERE username = ?");
            $stmt_update->bind_param("sss", $new_username, $profile_image_path, $admin_username);
        }

        if ($stmt_update->execute()) {
            $update_message = "Profile updated successfully!";
            if ($new_username !== $admin_username) {
                $_SESSION['admin_username'] = $new_username;
                $admin_username = $new_username;
            }
            $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->bind_param("s", $admin_username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
        } else {
            $error_message = "Failed to update profile.";
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Profile - TrainTrack</title>
<style>
    /* Reset and base */
    body, html {
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        background: linear-gradient(135deg, #2af598 0%, #009efd 100%);
        color: #333;
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        left: 0; top: 0;
        width: 220px;
        height: 100%;
        background: linear-gradient(180deg, #004d40, #00796b);
        padding-top: 20px;
        box-shadow: 2px 0 10px rgba(0,0,0,0.15);
        z-index: 1000;
    }

    .sidebar h2 {
        color: #e0f2f1;
        text-align: center;
        margin-bottom: 30px;
        font-weight: 700;
        font-size: 24px;
        letter-spacing: 1.2px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        user-select: none;
    }

    .sidebar a {
        display: block;
        color: #b2dfdb;
        padding: 15px 25px;
        text-decoration: none;
        font-weight: 600;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #004d40cc;
        color: #fff;
        border-left: 4px solid #80cbc4;
    }

    .sidebar a.active {
        background-color: #00796b;
        color: #fff;
        border-left: 4px solid #4db6ac;
    }

    /* Main content */
    .main-content {
        margin-left: 220px;
        padding: 40px 50px;
        box-sizing: border-box;
        min-height: 100vh;
        background: #ffffffdd;
        box-shadow: inset 0 0 25px #f0f0f0;
        border-radius: 12px;
        max-width: 700px;
    }

    h2 {
        margin-top: 0;
        color: #004d40;
        text-shadow: 0 1px 1px #a7ffeb;
        font-weight: 700;
        font-size: 28px;
    }

    /* Profile image */
    .profile-img {
        display: block;
        margin-bottom: 20px;
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #00796b;
        box-shadow: 0 3px 10px rgba(0, 121, 107, 0.5);
    }

    /* Messages */
    .form-message {
        margin-bottom: 20px;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-message.success {
        color: #1b5e20;
        background-color: #a5d6a7;
        border: 1.5px solid #43a047;
    }
    .form-message.error {
        color: #b71c1c;
        background-color: #ef9a9a;
        border: 1.5px solid #e53935;
    }

    /* Form */
    form {
        max-width: 100%;
    }

    label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #004d40;
        display: block;
    }

    input[type="text"],
    input[type="password"],
    input[type="file"] {
        width: 100%;
        padding: 12px 15px;
        border: 1.5px solid #00796b;
        border-radius: 8px;
        font-size: 15px;
        box-sizing: border-box;
        margin-bottom: 22px;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="file"]:focus {
        outline: none;
        border-color: #004d40;
        box-shadow: 0 0 10px #004d4066;
    }

    button.btn.approve {
        background-color: #00796b;
        color: white;
        border: none;
        padding: 14px 30px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        box-shadow: 0 4px 14px rgba(0,121,107,0.6);
    }

    button.btn.approve:hover {
        background-color: #004d40;
        box-shadow: 0 6px 20px rgba(0,77,64,0.9);
    }

    small {
        display: block;
        margin-top: -16px;
        margin-bottom: 20px;
        color: #00796bcc;
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 60px;
            padding-top: 20px;
        }
        .sidebar h2 {
            font-size: 0;
            margin-bottom: 0;
        }
        .sidebar a {
            padding: 15px 10px;
            font-size: 0;
            border-left: none;
            text-align: center;
            position: relative;
        }
        .sidebar a::before {
            content: attr(data-tooltip);
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: #00796b;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            z-index: 9999;
        }
        .sidebar a:hover::before {
            opacity: 1;
        }
        .main-content {
            margin-left: 60px;
            padding: 20px;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
            max-width: 100%;
        }
    }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TrainTrack</h2>
    <a href="dashboard.php" data-tooltip="Dashboard">Dashboard</a>
    <a href="users.php" data-tooltip="Users">Users</a>
    <a href="admin_profile.php" class="active" data-tooltip="Manage Profile">Manage Profile</a>
    <a href="logout.php" data-tooltip="Logout">Logout</a>
</div>

<div class="main-content">
    <h2>Manage Profile</h2>

    <?php if ($update_message): ?>
        <p class="form-message success"><?= htmlspecialchars($update_message) ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="form-message error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="">
        <?php if (!empty($admin['profile_image']) && file_exists('../' . $admin['profile_image'])): ?>
            <img src="../<?= htmlspecialchars($admin['profile_image']) ?>" alt="Profile Image" class="profile-img" />
        <?php else: ?>
            <img src="../uploads/admin_profiles/default.png" alt="Default Profile Image" class="profile-img" />
        <?php endif; ?>

        <label for="profile_image">Change Profile Image:</label>
        <input type="file" name="profile_image" id="profile_image" accept="image/*" />
        <small>Allowed: JPG, PNG, GIF. Leave empty to keep current image.</small>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required />

        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" />

        <button type="submit" name="update_profile" class="btn approve">Update Profile</button>
    </form>
</div>

</body>
</html>
