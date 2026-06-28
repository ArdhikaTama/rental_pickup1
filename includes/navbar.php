<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light py-3" style="background-color: #cdcdcd;">
    <div class="container">
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2 mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link text-dark px-3 fw-bold small text-uppercase" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link text-dark px-3 fw-bold small text-uppercase" href="index.php#about">About us</a></li>
                <li class="nav-item"><a class="nav-link text-dark px-3 fw-bold small text-uppercase" href="index.php#unit">Gallery</a></li>
                <li class="nav-item me-lg-4"><a class="nav-link text-dark px-3 fw-bold small text-uppercase" href="index.php#contact">Contact</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="btn btn-orange text-white px-4 py-2 rounded-2 small fw-bold shadow-sm" href="dashboard/index.php">Dashboard</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-warning text-orange px-4 py-2 rounded-2 small fw-bold me-1" style="border-color: #ff7a00; background: transparent;" href="login.php?action=register">Sign Up</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-orange text-white px-4 py-2 rounded-2 small fw-bold shadow-sm" href="login.php">Sign In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>