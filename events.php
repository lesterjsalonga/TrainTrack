<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}
require 'config.php';

$student_id = $_SESSION['student_id'];

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    // Check if event exists and has available slots
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND status = 'upcoming'");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    if ($event) {
        // Check if user is already registered
        $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND student_id = ?");
        $stmt->bind_param("is", $event_id, $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            // Check available slots
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $registered = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($registered < $event['max_participants']) {
                // Generate unique ticket number
                $ticket_number = 'TKT-' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, student_id, ticket_number) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $event_id, $student_id, $ticket_number);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Successfully registered for the event!";
                } else {
                    $_SESSION['error'] = "Failed to register for the event.";
                }
            } else {
                $_SESSION['error'] = "Sorry, this event is full.";
            }
        } else {
            $_SESSION['error'] = "You are already registered for this event.";
        }
    }
    header("Location: events.php");
    exit;
}

// Fetch all upcoming events
$stmt = $conn->prepare("
    SELECT e.*, 
           COUNT(er.id) as registered_count,
           (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND student_id = ?) as is_registered
    FROM events e
    LEFT JOIN event_registrations er ON e.id = er.event_id
    WHERE e.status = 'upcoming'
    GROUP BY e.id
    ORDER BY e.event_date ASC
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$upcoming_events = $stmt->get_result();

// Fetch user's registered events
$stmt = $conn->prepare("
    SELECT e.*, er.ticket_number, er.registration_date
    FROM events e
    JOIN event_registrations er ON e.id = er.event_id
    WHERE er.student_id = ?
    ORDER BY e.event_date ASC
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$registered_events = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Events - TrainTrack</title>
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

        /* Event cards */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .event-card {
            background: #ffffffdd;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        .event-title {
            color: #004d40;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .event-details {
            color: #00695c;
            margin-bottom: 20px;
        }

        .event-details p {
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-details i {
            color: #00796b;
        }

        .event-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #00796b;
            color: white;
        }

        .btn-primary:hover {
            background: #00695c;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #004d40;
            color: white;
        }

        .btn-secondary:hover {
            background: #003d33;
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .slots-info {
            font-size: 0.9rem;
            color: #00695c;
            font-weight: 600;
        }

        .section-title {
            color: #004d40;
            font-size: 1.8rem;
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00796b;
        }

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

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>TrainTrack</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="events.php" class="active">Events</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Events</h2>

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

    <h3 class="section-title">Your Registered Events</h3>
    <div class="events-grid">
        <?php if ($registered_events->num_rows > 0): ?>
            <?php while ($event = $registered_events->fetch_assoc()): ?>
                <div class="event-card">
                    <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <div class="event-details">
                        <p><i class="fas fa-calendar"></i> <?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue']) ?></p>
                        <p><i class="fas fa-ticket-alt"></i> Ticket: <?= htmlspecialchars($event['ticket_number']) ?></p>
                    </div>
                    <div class="event-actions">
                        <a href="generate_ticket.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary">Download Ticket</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't registered for any events yet.</p>
        <?php endif; ?>
    </div>

    <h3 class="section-title">Upcoming Events</h3>
    <div class="events-grid">
        <?php if ($upcoming_events->num_rows > 0): ?>
            <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                <div class="event-card">
                    <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <div class="event-details">
                        <p><i class="fas fa-calendar"></i> <?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue']) ?></p>
                        <p><i class="fas fa-users"></i> <?= $event['registered_count'] ?>/<?= $event['max_participants'] ?> registered</p>
                    </div>
                    <div class="event-actions">
                        <?php if ($event['is_registered']): ?>
                            <button class="btn btn-secondary" disabled>Already Registered</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-primary" <?= $event['registered_count'] >= $event['max_participants'] ? 'disabled' : '' ?>>
                                    <?= $event['registered_count'] >= $event['max_participants'] ? 'Event Full' : 'Join Event' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events available.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html> 