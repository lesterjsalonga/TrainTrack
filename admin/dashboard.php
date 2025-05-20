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

// Handle approve/reject and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resume_id'], $_POST['action'])) {
        $resume_id = intval($_POST['resume_id']);
        $action = $_POST['action'] === 'approve' ? 'Approved' : ($_POST['action'] === 'reject' ? 'Rejected' : null);
        $remark = trim($_POST['remark']);

        if ($action) {
            $stmt = $conn->prepare("UPDATE resumes SET status = ?, remark = ? WHERE id = ?");
            $stmt->bind_param("ssi", $action, $remark, $resume_id);
            $stmt->execute();

            // If approved, handle meeting scheduling
            if ($action === 'Approved' && isset($_POST['schedule_meeting'])) {
                $meeting_date = $_POST['meeting_date'];
                $meeting_type = $_POST['meeting_type'];
                $meeting_platform = $_POST['meeting_platform'];
                $meeting_link = $_POST['meeting_link'];
                $meeting_notes = $_POST['meeting_notes'];
                $student_id = $_POST['student_id'];

                $stmt = $conn->prepare("INSERT INTO meetings (resume_id, student_id, meeting_date, meeting_type, meeting_platform, meeting_link, meeting_notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $resume_id, $student_id, $meeting_date, $meeting_type, $meeting_platform, $meeting_link, $meeting_notes);
                $stmt->execute();
            }
        }
    }

    if (isset($_POST['delete_resume_id'])) {
        $delete_id = intval($_POST['delete_resume_id']);
        $stmt = $conn->prepare("SELECT file_name FROM resumes WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $file_path = "../uploads/" . $row['file_name'];
            if (file_exists($file_path)) unlink($file_path);
        }

        $stmt = $conn->prepare("DELETE FROM resumes WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
}

// Handle search/filter input
$search = $_GET['search'] ?? '';
$search = trim($search);

if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE file_name LIKE CONCAT('%', ?, '%') OR student_id LIKE CONCAT('%', ?, '%') ORDER BY uploaded_at DESC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $resumes = $stmt->get_result();
} else {
    $resumes = $conn->query("SELECT * FROM resumes ORDER BY uploaded_at DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - TrainTrack</title>
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

        /* Search/filter bar */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .search-bar input[type="text"] {
            width: 300px;
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: box-shadow 0.3s ease;
        }

        .search-bar input[type="text"]:focus {
            outline: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }

        .search-bar button {
            padding: 10px 20px;
            background-color: #004d40;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #00695c;
        }

        /* Table styling */
        table {
            border-collapse: collapse;
            width: 100%;
            background: #ffffffdd;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            font-size: 14px;
        }

        th, td {
            padding: 14px 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: linear-gradient(90deg, #00796b, #004d40);
            color: #e0f2f1;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background-color: #e0f2f1cc;
        }

        /* Buttons */
        .btn {
            padding: 8px 14px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: background-color 0.3s ease;
        }

        .approve {
            background-color: #43a047;
        }
        .approve:hover {
            background-color: #388e3c;
        }

        .reject {
            background-color: #e53935;
        }
        .reject:hover {
            background-color: #c62828;
        }

        .delete {
            background-color: #6d4c41;
        }
        .delete:hover {
            background-color: #4e342e;
        }

        input.remark-input {
            padding: 6px 8px;
            width: 100%;
            border-radius: 6px;
            border: 1px solid #bbb;
            box-sizing: border-box;
            font-size: 13px;
            margin-bottom: 8px;
            transition: border-color 0.3s ease;
        }
        input.remark-input:focus {
            outline: none;
            border-color: #00796b;
            box-shadow: 0 0 8px #00796baa;
        }

        form.inline-form {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            width: 150px;
        }

        a.download-link {
            color: #00796b;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a.download-link:hover {
            color: #004d40;
        }

        /* Add these new styles */
        .meeting-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }

        .meeting-form h3 {
            margin-top: 0;
            color: #004d40;
            margin-bottom: 20px;
        }

        .meeting-form .form-group {
            margin-bottom: 15px;
        }

        .meeting-form label {
            display: block;
            margin-bottom: 5px;
            color: #004d40;
            font-weight: 600;
        }

        .meeting-form input,
        .meeting-form select,
        .meeting-form textarea {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .meeting-form input:focus,
        .meeting-form select:focus,
        .meeting-form textarea:focus {
            border-color: #00796b;
            outline: none;
        }

        .meeting-form .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* Update these styles */
        .meeting-form textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
        }

        .meeting-form textarea:focus {
            border-color: #00796b;
            outline: none;
        }

        .inline-form {
            display: inline-flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .approve {
            background-color: #43a047;
            color: white;
        }

        .approve:hover {
            background-color: #388e3c;
        }

        .reject {
            background-color: #e53935;
            color: white;
        }

        .reject:hover {
            background-color: #c62828;
        }

        .delete {
            background-color: #6d4c41;
            color: white;
        }

        .delete:hover {
            background-color: #4e342e;
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

    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="users.php">Users</a>
    <a href="events.php">Event Management</a>
    <a href="admin_profile.php">Manage Profile</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Admin Dashboard</h2>

    <form method="GET" class="search-bar" action="">
        <input type="text" name="search" placeholder="Search by File Name or Student ID..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <button type="button" onclick="window.location.href='dashboard.php';" style="background:#c62828;">Clear</button>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Student ID</th>
                <th>Uploaded At</th>
                <th>Status</th>
                <th>Remark</th>
                <th>Download</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resumes->num_rows > 0): ?>
                <?php while ($row = $resumes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['file_name']) ?></td>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['remark']) ?></td>
                        <td><a class="download-link" href="../uploads/<?= urlencode($row['file_name']) ?>" download>Download</a></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="resume_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                <button class="btn approve" type="submit" name="action" value="approve" onclick="return showMeetingForm(this)">Approve</button>
                                <button class="btn reject" type="submit" name="action" value="reject">Reject</button>
                            </form>
                            <form method="POST" class="inline-form" style="margin-left:8px;" onsubmit="return confirm('Are you sure you want to delete this resume?');">
                                <input type="hidden" name="delete_resume_id" value="<?= $row['id'] ?>">
                                <button class="btn delete" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="padding:20px;">No resume submissions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="overlay" id="overlay"></div>
<div class="meeting-form" id="meetingForm">
    <h3>Schedule Meeting</h3>
    <form method="POST" id="meetingScheduleForm">
        <input type="hidden" name="resume_id" id="meeting_resume_id">
        <input type="hidden" name="student_id" id="meeting_student_id">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="schedule_meeting" value="1">
        
        <div class="form-group">
            <label for="remark">Feedback/Remark</label>
            <textarea id="remark" name="remark" rows="3" placeholder="Enter your feedback or remarks about the resume..." required></textarea>
        </div>

        <div class="form-group">
            <label for="meeting_date">Meeting Date & Time</label>
            <input type="datetime-local" id="meeting_date" name="meeting_date" required>
        </div>

        <div class="form-group">
            <label for="meeting_type">Meeting Type</label>
            <select id="meeting_type" name="meeting_type" required>
                <option value="interview">Interview</option>
                <option value="consultation">Consultation</option>
            </select>
        </div>

        <div class="form-group">
            <label for="meeting_platform">Platform</label>
            <select id="meeting_platform" name="meeting_platform" required>
                <option value="Zoom">Zoom</option>
                <option value="Google Meet">Google Meet</option>
                <option value="Microsoft Teams">Microsoft Teams</option>
                <option value="In-person">In-person</option>
            </select>
        </div>

        <div class="form-group">
            <label for="meeting_link">Meeting Link (if online)</label>
            <input type="url" id="meeting_link" name="meeting_link" placeholder="https://...">
        </div>

        <div class="form-group">
            <label for="meeting_notes">Additional Notes</label>
            <textarea id="meeting_notes" name="meeting_notes" rows="3" placeholder="Enter any additional information or instructions..."></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Schedule & Approve</button>
            <button type="button" class="btn btn-danger" onclick="hideMeetingForm()">Cancel</button>
        </div>
    </form>
</div>

<script>
function showMeetingForm(button) {
    const form = button.closest('form');
    const resumeId = form.querySelector('input[name="resume_id"]').value;
    const studentId = form.querySelector('input[name="student_id"]').value;
    
    document.getElementById('meeting_resume_id').value = resumeId;
    document.getElementById('meeting_student_id').value = studentId;
    
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('meetingForm').style.display = 'block';
    
    return false; // Prevent form submission
}

function hideMeetingForm() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('meetingForm').style.display = 'none';
}

// Close form when clicking outside
document.getElementById('overlay').addEventListener('click', hideMeetingForm);
</script>

</body>
</html>
