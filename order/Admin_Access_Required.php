<?php

require '../lib/_base.php';
// Check if NOT Admin - show message and STOP
// Check if NOT Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    include '../lib/_head.php'; // This already provides the sidebar and layout
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
            <p style="margin-top: 1.5rem; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> 
                <a href="javascript:history.back()" style="color: #2a5298; text-decoration: none; font-weight: 600;">Go Back</a>
            </p>
        </div>
    </div>

    <?php
    include '../lib/_foot.php';
    exit(); 
}
?>