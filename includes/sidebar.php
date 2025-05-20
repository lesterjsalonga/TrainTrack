<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
    <a href="events.php" <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'class="active"' : ''; ?>>Events</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</nav>

<style>
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

@media (max-width: 768px) {
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
}
</style> 