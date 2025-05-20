<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}
require 'config.php';

$student_id = $_SESSION['student_id'];
$resumes = $conn->query("SELECT * FROM resumes WHERE student_id = '$student_id' ORDER BY uploaded_at DESC");
$resume = $resumes->fetch_assoc(); // latest resume

$meetings = $conn->query("SELECT m.*, r.status as resume_status 
    FROM meetings m 
    JOIN resumes r ON m.resume_id = r.id 
    WHERE m.student_id = '$student_id' 
    ORDER BY m.meeting_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Dashboard</title>
  <style>
    /* Base Reset */
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #fafafa;
      color: #333;
      display: flex;
      height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: #ffffff;
      border-right: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      padding: 2rem 1.5rem;
      box-shadow: 2px 0 6px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease;
    }

    .sidebar h2 {
      margin: 0 0 2rem;
      font-weight: 700;
      font-size: 1.5rem;
      letter-spacing: 0.05em;
      color: #111;
      user-select: none;
    }

    .sidebar a {
      color: #555;
      text-decoration: none;
      margin-bottom: 1.5rem;
      font-weight: 600;
      padding: 0.6rem 0.3rem;
      border-left: 3px solid transparent;
      transition: all 0.3s ease;
      border-radius: 3px;
      user-select: none;
    }
    .sidebar a:hover,
    .sidebar a.active {
      color: #1a73e8;
      border-left: 3px solid #1a73e8;
      background-color: #f0f4ff;
      padding-left: 1rem;
    }

    .sidebar .logout-btn {
      margin-top: auto;
      background: #1a73e8;
      padding: 0.8rem 1.2rem;
      border-radius: 6px;
      text-align: center;
      font-weight: 700;
      color: white;
      cursor: pointer;
      user-select: none;
      box-shadow: 0 4px 10px rgba(26, 115, 232, 0.3);
      transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
      border: none;
      display: inline-block;
      text-decoration: none;
    }
    .sidebar .logout-btn:hover {
      background-color: #155ab6;
      box-shadow: 0 6px 14px rgba(21, 90, 182, 0.45);
      transform: translateY(-2px);
    }
    .sidebar .logout-btn:active {
      transform: translateY(0);
      box-shadow: 0 3px 7px rgba(21, 90, 182, 0.25);
    }

    /* Main dashboard content */
    .dashboard {
      flex: 1;
      padding: 2.5rem 3rem;
      background: #fff;
      overflow-y: auto;
      border-radius: 0 12px 12px 0;
      box-shadow: inset 0 0 15px #eee;
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    header h1 {
      font-weight: 700;
      color: #222;
      font-size: 1.8rem;
      user-select: none;
    }

    h2 {
      margin: 0 0 1rem;
      color: #222;
      font-weight: 700;
      font-size: 1.3rem;
      border-bottom: 2px solid #1a73e8;
      padding-bottom: 6px;
      user-select: none;
    }

    form input[type="file"] {
      margin: 10px 0 15px;
      padding: 8px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
      width: 100%;
      max-width: 400px;
    }
    form input[type="file"]:focus {
      border-color: #1a73e8;
      outline: none;
    }

    form button {
      background-color: #1a73e8;
      color: white;
      padding: 10px 22px;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
      transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
      user-select: none;
      max-width: 150px;
    }
    form button:hover {
      background-color: #155ab6;
      box-shadow: 0 6px 18px rgba(21, 90, 182, 0.45);
      transform: translateY(-3px);
    }
    form button:active {
      transform: translateY(0);
      box-shadow: 0 3px 8px rgba(21, 90, 182, 0.25);
    }

    .remark-box {
      margin-top: 12px;
      background: #f8f9fc;
      border-left: 5px solid #1a73e8;
      padding: 12px 18px;
      font-style: italic;
      color: #555;
      max-width: 600px;
      border-radius: 5px;
      user-select: none;
    }

    .status-box {
      padding: 10px 18px;
      font-weight: 700;
      border-radius: 8px;
      margin-top: 10px;
      width: fit-content;
      user-select: none;
      font-size: 1.05rem;
      transition: background-color 0.3s ease, color 0.3s ease;
      box-shadow: 0 2px 5px rgb(0 0 0 / 0.05);
    }
    .status-box.approved {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    .status-box.rejected {
      background-color: #f8d7da;
      color: #842029;
    }
    .status-box.pending {
      background-color: #fff3cd;
      color: #664d03;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
      margin-top: 20px;
      font-size: 0.95rem;
      user-select: none;
    }

    table th, table td {
      padding: 14px 18px;
      text-align: left;
    }
    table th {
      background-color: transparent;
      color: #666;
      font-weight: 700;
      border-bottom: 1px solid #ddd;
      user-select: none;
    }
    table tbody tr {
      background: #f9fafb;
      border-radius: 8px;
      transition: background-color 0.25s ease;
    }
    table tbody tr:hover {
      background-color: #e9f0ff;
    }
    table tbody tr td:first-child {
      font-weight: 600;
      color: #222;
    }

    a {
      color: #1a73e8;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
      user-select: none;
    }
    a:hover {
      text-decoration: underline;
      color: #155ab6;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        flex-direction: column;
        height: auto;
      }
      .sidebar {
        width: 100%;
        flex-direction: row;
        padding: 1rem 1rem;
        justify-content: space-around;
        border-radius: 0;
        border-right: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      }
      .sidebar h2 {
        display: none;
      }
      .dashboard {
        padding: 1.5rem 1.5rem;
        border-radius: 0;
        margin-top: 10px;
      }
    }

    /* Add these new styles */
    .meetings-section {
        margin-top: 30px;
    }

    .meetings-section h3 {
        color: #004d40;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .meetings-list {
        display: grid;
        gap: 15px;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .meeting-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #00796b;
    }

    .meeting-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .meeting-type {
        background: #e0f2f1;
        color: #00796b;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .meeting-status {
        background: #fff3e0;
        color: #e65100;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .meeting-details p {
        margin: 8px 0;
        color: #333;
    }

    .meeting-details a {
        color: #00796b;
        text-decoration: none;
        font-weight: 600;
    }

    .meeting-details a:hover {
        text-decoration: underline;
    }
  </style>
</head>
<body>

  <nav class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="events.php">Events</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </nav>

  <main class="dashboard">
    <header>
      <h1>Welcome, <?= htmlspecialchars($student_id) ?></h1>
    </header>

    <section class="upload-section">
      <h2>Upload Resume</h2>
      <form action="upload.php" method="POST" enctype="multipart/form-data" novalidate>
        <input type="file" name="resume" accept=".pdf,.docx" required>
        <button type="submit">Upload</button>
      </form>
    </section>

    <section class="status-section">
      <h2>Latest Resume Status</h2>
      <?php if ($resume): ?>
        <div class="status-box <?= strtolower($resume['status']) ?>">
          <?= ucfirst($resume['status']) ?>
        </div>

        <?php if (!empty($resume['remark'])): ?>
          <div class="remark-box">
            <strong>Admin Remark:</strong> <?= htmlspecialchars($resume['remark']) ?>
          </div>
        <?php endif; ?>

        <p><a href="uploads/<?= htmlspecialchars($resume['file_name']) ?>" target="_blank" rel="noopener noreferrer">View Uploaded Resume</a></p>
      <?php else: ?>
        <p>No resume uploaded yet.</p>
      <?php endif; ?>

      <?php if ($meetings->num_rows > 0): ?>
        <div class="meetings-section">
            <h3>Scheduled Meetings</h3>
            <div class="meetings-list">
                <?php while ($meeting = $meetings->fetch_assoc()): ?>
                    <div class="meeting-card">
                        <div class="meeting-header">
                            <span class="meeting-type"><?= ucfirst($meeting['meeting_type']) ?></span>
                            <span class="meeting-status"><?= ucfirst($meeting['status']) ?></span>
                        </div>
                        <div class="meeting-details">
                            <p><strong>Date:</strong> <?= date('F j, Y g:i A', strtotime($meeting['meeting_date'])) ?></p>
                            <p><strong>Platform:</strong> <?= htmlspecialchars($meeting['meeting_platform']) ?></p>
                            <?php if (!empty($meeting['meeting_link'])): ?>
                                <p><strong>Link:</strong> <a href="<?= htmlspecialchars($meeting['meeting_link']) ?>" target="_blank">Join Meeting</a></p>
                            <?php endif; ?>
                            <?php if (!empty($meeting['meeting_notes'])): ?>
                                <p><strong>Notes:</strong> <?= htmlspecialchars($meeting['meeting_notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
      <?php endif; ?>
    </section>

    <section class="history-section">
      <h2>Upload History</h2>
      <?php if ($resumes->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>File</th>
              <th>Status</th>
              <th>Remark</th>
              <th>Uploaded At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $resumes->data_seek(0);
            while ($row = $resumes->fetch_assoc()):
            ?>
              <tr>
                <td><?= htmlspecialchars($row['file_name']) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= htmlspecialchars($row['remark']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($row['uploaded_at'])) ?></td>
                <td><a href="uploads/<?= htmlspecialchars($row['file_name']) ?>" target="_blank" rel="noopener noreferrer">View</a></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No resume history available.</p>
      <?php endif; ?>
    </section>
  </main>

</body>
</html>
