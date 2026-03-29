<?php
session_start();
require_once 'db.php'; // Ensure your PDO connection ($pdo) is included 

// --- 1. AUTOMATIC LOGIN (REMEMBER ME) ---
// This runs every time a page including auth.php is loaded
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    // Split the cookie into selector and validator
    $parts = explode(':', $_COOKIE['remember_me']);
    
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        // Search for a valid, non-expired token 
        $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires > NOW()");
        $stmt->execute([$selector]);
        $token = $stmt->fetch();

        // Verify the validator against the hashed version in the DB 
        if ($token && password_verify($validator, $token['hashed_validator'])) {
            // Restore session
            $u_stmt = $pdo->prepare("SELECT id, username, role FROM user WHERE id = ?");
            $u_stmt->execute([$token['user_id']]);
            $user = $u_stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
            }
        }
    }
}

// --- 2. LOGIN FUNCTION (WITH BLOCKING) ---
function login($username, $password, $pdo) {
    // Fetch user details [cite: 17]
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) return false;

    // Check for Temporary Blocking (3 Attempts) 
    if ($user['login_attempts'] >= 3) {
        $last_attempt = strtotime($user['last_attempt_time']);
        $timeout = 15 * 60; // 15-minute lockout period
        
        if ((time() - $last_attempt) < $timeout) {
            return "blocked"; 
        }
    }

    // Verify hashed password 
    if (password_verify($password, $user['password'])) {
        // SUCCESS: Reset attempts and set session
        $update = $pdo->prepare("UPDATE user SET login_attempts = 0 WHERE id = ?");
        $update->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    } else {
        // FAILURE: Increment attempts and record time 
        $update = $pdo->prepare("UPDATE user SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE id = ?");
        $update->execute([$user['id']]);
        return false;
    }
}

// --- 3. AUTHORIZATION HELPER ---
function authorize($role_required) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role_required) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}

// --- 4. LOGOUT HELPER ---
function logout($pdo) {
    // Clear Remember Me token from DB and Cookie if it exists [cite: 72, 108]
    if (isset($_COOKIE['remember_me'])) {
        $selector = explode(':', $_COOKIE['remember_me'])[0];
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->execute([$selector]);
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }

    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}