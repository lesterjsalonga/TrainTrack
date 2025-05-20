<?php
session_start();
require 'config.php';

if (!isset($_SESSION['student_id']) || !isset($_GET['event_id'])) {
    header("Location: events.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$event_id = (int)$_GET['event_id'];

// Fetch event and registration details
$stmt = $conn->prepare("SELECT e.*, er.ticket_number, s.name, s.student_id as student_id
    FROM events e 
    JOIN event_registrations er ON e.id = er.event_id 
    JOIN students s ON er.student_id = s.student_id
    WHERE e.id = ? AND er.student_id = ?");
$stmt->bind_param("is", $event_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($ticket = $result->fetch_assoc()) {
    // Generate QR code using Google Charts API
    $qr_code_url = "https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" . urlencode($ticket['ticket_number']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - <?php echo htmlspecialchars($ticket['title']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .ticket-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .ticket-header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        
        .ticket-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .ticket-details {
            color: #333;
        }
        
        .ticket-details p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .ticket-details strong {
            color: #2c3e50;
        }
        
        .ticket-qr {
            text-align: center;
        }
        
        .ticket-qr img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        
        .ticket-number {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 20px;
        }
        
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        
        .print-btn:hover {
            background: #2980b9;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .ticket {
                box-shadow: none;
                padding: 0;
            }
            
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            <h1>EVENT TICKET</h1>
        </div>
        
        <div class="ticket-content">
            <div class="ticket-details">
                <p><strong>Event:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($ticket['event_date'])); ?></p>
                <p><strong>Venue:</strong> <?php echo htmlspecialchars($ticket['venue']); ?></p>
                <p><strong>Attendee:</strong> <?php echo htmlspecialchars($ticket['name']); ?></p>
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($ticket['student_id']); ?></p>
            </div>
            
            <div class="ticket-qr">
                <img src="<?php echo $qr_code_url; ?>" alt="QR Code">
                <div class="ticket-number">
                    <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                </div>
            </div>
        </div>
    </div>
    
    <a href="javascript:window.print()" class="print-btn">Print Ticket</a>
</body>
</html>
<?php
} else {
    header("Location: events.php");
    exit;
} 