<?php

require '../lib/_base.php';
// Check if NOT Admin - show message and STOP
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    include '../lib/_head.php'; 
    ?>
    <div class="error-container">
        <div class="message-box">
            <i class="fas fa-shield-alt"></i>
            <h2>Admin Access Required</h2>
            <p>This page is restricted to administrators only.</p>
            <p>Your current role: 
                <span class="role-badge">
                    <i class="fas fa-user"></i> 
                    <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Guest' ?>
                </span>
            </p>

            <div class="back-link-container">
                <a href="javascript:history.back()" class="back-link">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
    </div>
    <?php
    include '../lib/_foot.php';
    exit(); 
}
?>