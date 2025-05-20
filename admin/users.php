<?php
session_start();
require '../config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all admin users
$admins = $conn->query("SELECT username, created_at FROM admins ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Users - TrainTrack</title>
<style>
    /* Reset and base */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    body, html {
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        background: linear-gradient(135deg, #2af598 0%, #009efd 100%);
        color: #2c3e50;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        left: 0; top: 0;
        width: 240px;
        height: 100%;
        background: linear-gradient(180deg, #004d40, #00796b);
        padding-top: 30px;
        box-shadow: 3px 0 15px rgba(0,0,0,0.15);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sidebar h2 {
        color: #e0f2f1;
        margin-bottom: 40px;
        font-weight: 800;
        font-size: 28px;
        letter-spacing: 1.5px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
        user-select: none;
        font-family: 'Poppins', sans-serif;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 90%;
        color: #b2dfdb;
        padding: 14px 28px;
        margin: 6px 0;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        border-radius: 8px;
        border-left: 5px solid transparent;
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .sidebar a:hover {
        background-color: rgba(0, 77, 64, 0.85);
        color: #ffffff;
        border-left: 5px solid #80cbc4;
        box-shadow: 0 4px 14px rgba(0, 77, 64, 0.5);
    }

    .sidebar a.active {
        background-color: #00796b;
        color: #ffffff;
        border-left: 5px solid #4db6ac;
        box-shadow: 0 5px 20px rgba(77, 182, 172, 0.6);
        font-weight: 700;
    }

    /* Main content */
    .main-content {
        margin-left: 240px;
        padding: 50px 60px;
        box-sizing: border-box;
        min-height: 100vh;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: inset 0 0 40px #e0f2f1;
        border-radius: 16px 0 0 16px;
        transition: margin-left 0.3s ease;
    }

    h2 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #004d40;
        font-weight: 700;
        font-size: 32px;
        letter-spacing: 1.1px;
        text-shadow: 0 1px 2px #a7ffeb;
        font-family: 'Poppins', sans-serif;
    }

    a.back-link {
        display: inline-block;
        margin: 15px 0 30px 0;
        color: #00796b;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: color 0.3s ease, border-bottom-color 0.3s ease;
    }
    a.back-link:hover {
        color: #004d40;
        border-bottom-color: #004d40;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 16px;
        font-size: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    thead tr {
        background: linear-gradient(90deg, #00796b, #004d40);
        color: #e0f2f1;
        font-weight: 700;
        font-size: 17px;
        text-transform: uppercase;
        letter-spacing: 1.1px;
    }
    thead tr th {
        padding: 18px 24px;
        text-align: left;
        border-radius: 16px 16px 0 0;
        user-select: none;
    }
    tbody tr {
        background-color: #fafafa;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
        cursor: default;
    }
    tbody tr:hover {
        background-color: #e0f2f1cc;
        box-shadow: 0 6px 22px rgba(0,0,0,0.1);
    }
    tbody tr td {
        padding: 18px 24px;
        color: #34495e;
        border-bottom: 1px solid #ddd;
    }
    tbody tr:last-child td {
        border-bottom: none;
    }

    /* No records message */
    tbody tr.no-records td {
        text-align: center;
        font-style: italic;
        color: #888;
        padding: 40px 0;
        border: none;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .sidebar {
            width: 70px;
            padding-top: 30px;
        }
        .sidebar h2 {
            font-size: 0;
            margin-bottom: 0;
        }
        .sidebar a {
            padding: 15px 12px;
            font-size: 0;
            border-left: none;
            text-align: center;
            position: relative;
            border-radius: 0;
        }
        .sidebar a::before {
            content: attr(data-tooltip);
            position: absolute;
            left: 80px;
            top: 50%;
            transform: translateY(-50%);
            background: #00796b;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .sidebar a:hover::before {
            opacity: 1;
        }
        .main-content {
            margin-left: 70px;
            padding: 30px 20px;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }
        table, thead tr th, tbody tr td {
            font-size: 14px;
            padding: 12px 15px;
        }
    }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TrainTrack</h2>
    <a href="dashboard.php" data-tooltip="Dashboard">Dashboard</a>
    <a href="users.php" class="active" data-tooltip="Users">Users</a>
    <a href="admin_profile.php" data-tooltip="Manage Profile">Manage Profile</a>
    <a href="logout.php" data-tooltip="Logout">Logout</a>
</div>

<div class="main-content" role="main" aria-label="Admin Users Section">
    <h2>Admin Users</h2>
    <a href="dashboard.php" class="back-link" aria-label="Back to Dashboard">&larr; Back to Dashboard</a>
    <table aria-describedby="admin-users-table">
        <thead>
            <tr>
                <th scope="col">Username</th>
                <th scope="col">Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($admins && $admins->num_rows > 0): ?>
                <?php while ($admin = $admins->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['username']) ?></td>
                        <td><?= htmlspecialchars($admin['created_at'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="no-records"><td colspan="2">No admin accounts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
