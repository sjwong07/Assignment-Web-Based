<?php
session_start();

// 1. Protection
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: /login.php");
    exit;
}

// --- 1. SWITCHED TO PDO (PREPARED STATEMENTS) ---
$host = 'localhost';
$dbname = 'dbA';
$username_db = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// RETRIEVE CURRENT DATA using Prepared Statements
$current_username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT email, phone, password, profile_photo FROM user WHERE username = ?");
$stmt->execute([$current_username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$userData = [
    'username' => $current_username,
    'email'    => $row['email'] ?? '',
    'phone'    => $row['phone'] ?? '',
    'photo'    => (!empty($row['profile_photo'])) ? $row['profile_photo'] : 'uploads/profiles/default.png.jpg' 
];

$error = "";
$success = "";

// Handle POST Requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle Profile Photo (Default Selection)
    if (isset($_POST['set_default_photo'])) {
        $photo_path = $_POST['set_default_photo'];
        
        if ($photo_path === $userData['photo']) {
            $error = "This is already your current profile photo.";
        } else {
            $upd = $pdo->prepare("UPDATE user SET profile_photo = ? WHERE username = ?");
            if($upd->execute([$photo_path, $current_username])) {
                $userData['photo'] = $photo_path;
                $_SESSION['profile_photo'] = $photo_path;
                $success = "Photo updated successfully!";
            }
        }
    }

    // Handle Profile Photo (Upload)
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        
        // --- CHANGE: Added time() to make the filename unique every time ---
        $target_file = $target_dir . "profile_" . $current_username . "_" . time() . "." . $file_ext;
        
        // This check will now pass because the filename is unique
        if ($target_file === $userData['photo']) {
            $error = "This image is already set as your current profile photo.";
        } else {
            $check = @getimagesize($_FILES["profile_photo"]["tmp_name"]);
            if($check !== false) {
                // Optional: Delete the old file from the folder to save space
                if (!empty($userData['photo']) && file_exists($userData['photo']) && strpos($userData['photo'], 'default') === false) {
                    @unlink($userData['photo']);
                }

                if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                    $upd = $pdo->prepare("UPDATE user SET profile_photo = ? WHERE username = ?");
                    $upd->execute([$target_file, $current_username]);
                    $userData['photo'] = $target_file;
                    $_SESSION['profile_photo'] = $target_file;
                    $success = "Photo updated successfully!";
                } else { $error = "Failed to upload photo."; }
            } else { $error = "Invalid image file."; }
        }
    }

    // Update Personal Info
    if (isset($_POST['update_profile'])) {
        $new_user  = trim($_POST['username']);
        $new_email = str_replace(' ', '', $_POST['email']);
        $new_phone = str_replace(' ', '', $_POST['phone']);

        if ($new_user === $userData['username'] && $new_email === $userData['email'] && $new_phone === $userData['phone']) {
            $error = "No changes were made to your profile.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!preg_match('/^[0-9]{7,15}$/', str_replace(['+', '-'], '', $new_phone))) {
            $error = "Please enter a valid phone number.";
        } else {
            $upd = $pdo->prepare("UPDATE user SET username = ?, email = ?, phone = ? WHERE username = ?");
            if($upd->execute([$new_user, $new_email, $new_phone, $current_username])){
                $_SESSION['username'] = $new_user;
                $userData['username'] = $new_user;
                $userData['email'] = $new_email;
                $userData['phone'] = $new_phone;
                $success = "Profile updated successfully!";
            }
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $curr_pass    = $_POST['current_password'];
        $new_pass     = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        $db_pass      = $row['password'];

        if (strpos($new_pass, ' ') !== false) {
            $error = "Passwords cannot contain spaces.";
        }
        elseif (hash('sha256', $curr_pass) !== $db_pass && !password_verify($curr_pass, $db_pass)) {
            $error = "Current password is incorrect.";
        } 
        elseif ($curr_pass === $new_pass) {
            $error = "New password cannot be the same as current password.";
        }
        elseif (
            strlen($new_pass) < 8 || strlen($new_pass) > 12 || 
            !preg_match('/[A-Z]/', $new_pass) ||   
            !preg_match('/[0-9]/', $new_pass) ||   
            !preg_match('/[\W]/', $new_pass)
        ) {
            $error = "New password must be 8-12 chars with Uppercase, Number, and Special Char.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        } else {
            $hashed_new = hash('sha256', $new_pass);
            $upd = $pdo->prepare("UPDATE user SET password = ? WHERE username = ?");
            $upd->execute([$hashed_new, $current_username]);
            $success = "Password changed successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root { 
            --primary-color: #475569; --accent-color: #3b82f6; --bg-light: #f8fafc; 
            --error-red: #ef4444; --success-green: #10b981; --text-muted: #64748b;
        }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-light); margin: 0; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 8% auto; padding: 25px; width: 450px; border-radius: 12px; text-align: center; }
        .avatar-selection-container { display: flex; align-items: center; gap: 10px; margin: 20px 0; position: relative; }
        .avatar-scroll { display: flex; overflow-x: auto; scroll-behavior: smooth; gap: 15px; padding: 10px 5px; white-space: nowrap; scrollbar-width: none; }
        .avatar-scroll::-webkit-scrollbar { display: none; }
        .avatar-scroll img { width: 70px; height: 70px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; object-fit: cover; flex-shrink: 0; transition: 0.2s; }
        .avatar-scroll img:hover { border-color: var(--accent-color); transform: scale(1.05); }
        .scroll-btn { background: #f1f5f9; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; color: var(--primary-color); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .scroll-btn:hover { background: var(--accent-color); color: white; }
        .divider { border-top: 1px solid #ddd; margin: 20px 0; position: relative; }
        .divider span { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: white; padding: 0 10px; font-size: 12px; color: var(--text-muted); }
        #drop-zone-box { border: 2px dashed #ddd; padding: 20px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
        #drop-zone-box.dragover { border-color: var(--accent-color); background: #eff6ff; color: var(--accent-color); }
        .profile-header { text-align: center; margin-bottom: 20px; }
        .photo-container { position: relative; width: 150px; height: 150px; margin: 0 auto 15px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .photo-container img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .camera-btn { position: absolute; bottom: 5px; right: 5px; background: var(--accent-color); color: white; padding: 8px; border-radius: 50%; cursor: pointer; border: 2px solid white; }
        .header-info h1 { margin-bottom: 5px; }
        .header-info p { margin: 2px 0; color: var(--text-muted); font-size: 14px; }
        .action-btns { display: flex; gap: 15px; margin: 30px 0; }
        .btn-tab { flex: 1; padding: 12px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 8px; font-weight: 600; color: var(--primary-color); }
        .btn-tab.active { background: var(--accent-color); color: white; border-color: var(--accent-color); }
        .profile-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: none; margin-bottom: 20px; }
        .profile-card.active { display: block; }
        .form-group { margin-bottom: 20px; position: relative; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--primary-color); }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-submit.disabled-style { background-color: #cbd5e1 !important; cursor: not-allowed; color: white; }
        .btn-submit.active-blue { background-color: var(--accent-color) !important; cursor: pointer; color: white; }
        .msg { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid; }
        .error { background: #fee2e2; color: var(--error-red); border-color: var(--error-red); }
        .success { background: #d1fae5; color: var(--success-green); border-color: var(--success-green); }
        .nav-links { margin-top: 20px; text-align: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); margin: 0 10px; font-weight: 600; transition: 0.2s; }
        .nav-links a:hover { color: var(--accent-color); }
    </style>
</head>
<body>

<div id="photoModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Default Photo</h3>
        <div class="avatar-selection-container">
            <button class="scroll-btn" type="button" onclick="scrollAvatars(-100)"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="avatar-scroll" id="avatarScroll">
                <form method="POST" id="default-photo-form" style="display: flex; gap: 15px;">
                    <input type="hidden" name="set_default_photo" id="selected_avatar">
                    <img src="uploads/profiles/default.png.jpg" class="avatar-opt" data-path="uploads/profiles/default.png.jpg">
                    <img src="uploads/profiles/user2.jpg" class="avatar-opt" data-path="uploads/profiles/user2.jpg.jpg">
                    <img src="uploads/profiles/user3.jpg" class="avatar-opt" data-path="uploads/profiles/user3.jpg">
                    <img src="uploads/profiles/user4.jpg" class="avatar-opt" data-path="uploads/profiles/user4.jpg">
                    <img src="uploads/profiles/user5.jpg" class="avatar-opt" data-path="uploads/profiles/user5.jpg">
                </form>
            </div>
            <button class="scroll-btn" type="button" onclick="scrollAvatars(100)"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div class="divider"><span>OR</span></div>
        <div id="drop-zone-box">
            <p id="drop-text"><i class="fa-solid fa-cloud-arrow-up"></i> Click or Drag to upload</p>
            <form id="upload-form" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="profile_photo" id="photo-input" accept="image/*">
            </form>
        </div>
        <button type="button" id="closeModalBtn" style="margin-top:15px; background:none; border:none; color:var(--text-muted); cursor:pointer;">Cancel</button>
    </div>
</div>

<div class="container">
    <div id="status-msg">
        <?php if ($error): ?> <div class="msg error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="msg success"><?php echo $success; ?></div> <?php endif; ?>
    </div>

    <div class="profile-header">
        <div class="photo-container">
            <img src="<?php echo htmlspecialchars($userData['photo']); ?>" id="preview-img">
            <div class="camera-btn" id="openModalBtn">
                <i class="fa-solid fa-camera"></i>
            </div>
        </div>
        <div class="header-info">
            <h1><?php echo htmlspecialchars($userData['username']); ?></h1>
            <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($userData['phone']); ?></p>
        </div>
    </div>

    <div class="action-btns">
        <button class="btn-tab active" data-target="info">Manage Profile Info</button>
        <button class="btn-tab" data-target="security">Manage Password</button>
    </div>

    <div id="info" class="profile-card active">
        <h2><i class="fa-solid fa-user-gear"></i> Profile Info</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="no-space" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="no-space" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn-submit active-blue">Update Info</button>
        </form>
    </div>

    <div id="security" class="profile-card">
        <h2><i class="fa-solid fa-shield-halved"></i> Security</h2>
        <form method="POST" id="passwordForm">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="pass-input no-space" required>
            </div>
            <div class="form-group">
                <label>New Password (8-12 chars)</label>
                <input type="password" name="new_password" id="new_pass" class="pass-input no-space" maxlength="12" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="pass-input no-space" maxlength="12" required>
            </div>
            <button type="submit" name="change_password" id="changePassBtn" class="btn-submit disabled-style">Change Password</button>
        </form>
    </div>

    <div class="nav-links">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a> |
        <a href="logout.php" style="color:var(--error-red);"><i class="fa-solid fa-power-off"></i> Logout</a>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Auto-hide messages
        setTimeout(() => { $('#status-msg').fadeOut(); }, 3000);

        // Modal Controls
        $('#openModalBtn').on('click', () => $('#photoModal').show());
        $('#closeModalBtn').on('click', () => $('#photoModal').hide());

        // Avatar Selection
        $('.avatar-opt').on('click', function() {
            $('#selected_avatar').val($(this).data('path'));
            $('#default-photo-form').submit();
        });

        // Tab Switching
        $('.btn-tab').on('click', function() {
            const target = $(this).data('target');
            $('.btn-tab').removeClass('active');
            $(this).addClass('active');
            $('.profile-card').hide().removeClass('active');
            $('#' + target).show().addClass('active');
        });

        // No-space constraint
        $('.no-space').on('input', function() {
            $(this).val($(this).val().replace(/\s/g, ''));
        });

        // Password button state check
        $('.pass-input').on('input', function() {
            let allFilled = true;
            $('.pass-input').each(function() {
                if ($(this).val().trim() === '') allFilled = false;
            });

            const btn = $('#changePassBtn');
            if (allFilled) {
                btn.removeClass('disabled-style').addClass('active-blue');
            } else {
                btn.removeClass('active-blue').addClass('disabled-style');
            }
        });

$('#passwordForm').on('submit', function(e) {
    let allFilled = true;
    $('.pass-input').each(function() {
        if ($(this).val().trim() === '') allFilled = false;
    });

    if (!allFilled) {
        return true; 
    }
});

        const dropZone = $('#drop-zone-box');
        
        dropZone.on('click', function(e) {
            // Trigger click on the hidden input
            $('#photo-input').trigger('click');
        });

        $('#photo-input').on('click', function(e) {
            e.stopPropagation();
        });

        // Ensure picking a file automatically submits the form
        $('#photo-input').on('change', function() {
            if (this.files && this.files.length > 0) {
                $('#upload-form').submit();
            }
        });

        // Drag and Drop
        dropZone.on('dragover', (e) => {
            e.preventDefault();
            dropZone.addClass('dragover');
            $('#drop-text').html('<i class="fa-solid fa-file-import"></i> Drop to select file');
        });

        dropZone.on('dragleave', () => {
            dropZone.removeClass('dragover');
            $('#drop-text').html('<i class="fa-solid fa-cloud-arrow-up"></i> Upload from device');
        });

        dropZone.on('drop', (e) => {
            e.preventDefault();
            dropZone.removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                if (!files[0].type.startsWith('image/')) {
                    alert("Only image files are allowed.");
                    return;
                }
                document.getElementById('photo-input').files = files;
                $('#upload-form').submit();
            }
        });
    });

    // Helper for scrolling
    function scrollAvatars(distance) {
        $('#avatarScroll').animate({ scrollLeft: '+=' + distance }, 300);
    }
</script>
</body>
</html>