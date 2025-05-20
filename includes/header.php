<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <a href="index.php">TrainTrack</a>
        </div>
        <nav class="main-nav">
            <?php if (isset($_SESSION['student_id'])): ?>
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
.main-header {
    background: #1a73e8;
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo a {
    color: white;
    text-decoration: none;
    font-size: 1.5rem;
    font-weight: bold;
}

.main-nav {
    display: flex;
    align-items: center;
    gap: 20px;
}

.welcome-text {
    font-size: 0.9rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style> 