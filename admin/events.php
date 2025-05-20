<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Fetch logged-in admin profile info
$admin_username = $_SESSION['admin_username'] ?? null;
$admin_profile = null;
if ($admin_username) {
    $stmt = $conn->prepare("SELECT username, profile_image FROM admins WHERE username = ?");
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $admin_profile = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $venue = trim($_POST['venue']);
        $max_participants = (int)$_POST['max_participants'];
        
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, venue, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $title, $description, $event_date, $venue, $max_participants, $admin_username);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Event created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create event.";
        }
    }
    // Handle status update
    elseif ($_POST['action'] === 'update_status') {
        $event_id = (int)$_POST['event_id'];
        $new_status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE events SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $event_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Event status updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update event status.";
        }
    }
    // Handle event deletion
    elseif ($_POST['action'] === 'delete') {
        $event_id = (int)$_POST['event_id'];
        
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Event deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete event.";
        }
    }
    header("Location: events.php");
    exit;
}

// Fetch all events with participant count
$events = $conn->query("
    SELECT e.*, 
           COUNT(er.id) as registered_count,
           a.username as created_by_name
    FROM events e
    LEFT JOIN event_registrations er ON e.id = er.event_id
    LEFT JOIN admins a ON e.created_by = a.username
    GROUP BY e.id
    ORDER BY e.event_date DESC
");

// Fetch participants for a specific event if requested
$event_participants = null;
if (isset($_GET['view_participants'])) {
    $event_id = (int)$_GET['view_participants'];
    $event_participants = $conn->query("SELECT er.*, s.name, s.email 
        FROM event_registrations er 
        JOIN students s ON er.student_id = s.student_id 
        WHERE er.event_id = $event_id 
        ORDER BY er.registration_date DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Management - TrainTrack</title>
    <link rel="stylesheet" href="dash.css">
    <style>
        /* Reset and base */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #2af598 0%, #009efd 100%);
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
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
        }

        /* Admin profile in sidebar */
        .admin-profile-sidebar {
            text-align: center;
            margin-bottom: 25px;
            padding: 0 15px;
        }

        .profile-img-sidebar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #80cbc4;
            object-fit: cover;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
        }

        .admin-username-sidebar {
            color: #b2dfdb;
            font-weight: 700;
            font-size: 18px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.4);
            word-break: break-word;
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
        }

        h2 {
            margin-top: 0;
            color: #004d40;
            text-shadow: 0 1px 1px #a7ffeb;
        }

        /* Form styling */
        .event-form {
            background: #ffffffdd;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #004d40;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #00796b;
            outline: none;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffffdd;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: linear-gradient(90deg, #00796b, #004d40);
            color: #e0f2f1;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #00796b;
            color: white;
        }

        .btn-primary:hover {
            background: #00695c;
        }

        .btn-danger {
            background: #c62828;
            color: white;
        }

        .btn-danger:hover {
        background: #b71c1c;
        }

        .btn-success {
            background: #2e7d32;
            color: white;
        }

        .btn-success:hover {
            background: #1b5e20;
        }

        /* Status badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-upcoming {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-ongoing {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-completed {
            background: #f5f5f5;
            color: #616161;
        }

        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Add these new styles */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .content-header h2 {
            margin: 0;
        }

        .events-table-container {
            background: #ffffffdd;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        select[name="status"] {
            padding: 6px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            margin-right: 10px;
            background-color: white;
        }

        select[name="status"]:focus {
            border-color: #00796b;
            outline: none;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>TrainTrack</h2>

    <?php if ($admin_profile): ?>
        <div class="admin-profile-sidebar">
            <?php if (!empty($admin_profile['profile_image']) && file_exists('../' . $admin_profile['profile_image'])): ?>
                <img src="../<?= htmlspecialchars($admin_profile['profile_image']) ?>" alt="Admin Profile" class="profile-img-sidebar" />
            <?php else: ?>
                <img src="../uploads/admin_profiles/default.png" alt="Default Profile" class="profile-img-sidebar" />
            <?php endif; ?>
            <p class="admin-username-sidebar"><?= htmlspecialchars($admin_profile['username']) ?></p>
        </div>
    <?php endif; ?>

    <a href="dashboard.php">Dashboard</a>
    <a href="users.php">Users</a>
    <a href="events.php" class="active">Events</a>
    <a href="admin_profile.php">Manage Profile</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <div class="content-header">
        <h2>Event Management</h2>
        <button class="btn btn-primary" onclick="toggleEventForm()">Create New Event</button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="event-form" id="eventForm" style="display: none;">
        <h3>Create New Event</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date & Time</label>
                <input type="datetime-local" id="event_date" name="event_date" required>
            </div>

            <div class="form-group">
                <label for="venue">Venue</label>
                <input type="text" id="venue" name="venue" required>
            </div>

            <div class="form-group">
                <label for="max_participants">Maximum Participants</label>
                <input type="number" id="max_participants" name="max_participants" min="1" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Event</button>
                <button type="button" class="btn btn-danger" onclick="toggleEventForm()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="events-table-container">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Participants</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events->num_rows > 0): ?>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></td>
                            <td><?= htmlspecialchars($event['venue']) ?></td>
                            <td><?= $event['registered_count'] ?>/<?= $event['max_participants'] ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($event['status']) ?>">
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($event['created_by_name']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="upcoming" <?= $event['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                        <option value="ongoing" <?= $event['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="completed" <?= $event['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $event['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($event_participants): ?>
    <div class="participants-modal" id="participantsModal" style="display: block;">
        <div class="participants-content">
            <span class="close-modal" onclick="window.location.href='events.php'">&times;</span>
            <h3>Event Participants</h3>
            <table class="participants-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Ticket Number</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($participant = $event_participants->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['name']) ?></td>
                            <td><?= htmlspecialchars($participant['email']) ?></td>
                            <td><?= htmlspecialchars($participant['ticket_number']) ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($participant['registration_date'])) ?></td>
                            <td><?= ucfirst($participant['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleEventForm() {
    const form = document.getElementById('eventForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html> 