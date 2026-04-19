<?php
include 'lib/_base.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: /login.php");
    exit;
}

$userData = [
    'username' => $_user->username,
    'email'    => $_user->email,
    'phone'    => $_user->phone,
    'photo'    => (!empty($_user->profile_photo)) ? $_user->profile_photo : 'uploads/profiles/default.png.jpg' 
];

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle Profile Photo (Default Selection)
    if (isset($_POST['set_default_photo'])) {
        $photo_path = trim($_POST['set_default_photo']);
        
        if ($photo_path === $userData['photo']) {
            $error = "This is already your current profile photo.";
        } else {
            $upd = $_db->prepare("UPDATE user SET profile_photo = ? WHERE user_id = ?");
            if($upd->execute([$photo_path, $_SESSION['user_id']])) {
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
        $target_file = $target_dir . "profile_" . $_SESSION['user_id'] . "_" . time() . "." . $file_ext;        
        
        $check = @getimagesize($_FILES["profile_photo"]["tmp_name"]);
        
        if($check !== false) {
            if (!empty($userData['photo']) && file_exists($userData['photo']) && strpos($userData['photo'], 'default') === false) {
                @unlink($userData['photo']);
            }
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $upd = $_db->prepare("UPDATE user SET profile_photo = ? WHERE user_id = ?");
                $upd->execute([$target_file, $_SESSION['user_id']]);
                
                $userData['photo'] = $target_file;
                $_SESSION['profile_photo'] = $target_file;
                $success = "Photo updated successfully!";
            } else { 
                $error = "Failed to upload photo."; 
            }
        } else { 
            $error = "Invalid image file."; 
        }
    }

    // Update Personal Info
    if (isset($_POST['update_profile'])) {
        $new_email = str_replace(' ', '', $_POST['email']);
        $new_phone = str_replace(' ', '', $_POST['phone']);

        if ($new_email === $userData['email'] && $new_phone === $userData['phone']) {
            $error = "No changes were made to your profile.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $check = $_db->prepare("SELECT user_id FROM user WHERE email = ? AND user_id != ?");
            $check->execute([$new_email, $_SESSION['user_id']]);
            
            if ($check->fetch()) {
                $error = "This email address is already registered to another account.";
            } else {
                $upd = $_db->prepare("UPDATE user SET email = ?, phone = ? WHERE user_id = ?");
                if($upd->execute([$new_email, $new_phone, $_SESSION['user_id']])){
                    $userData['email'] = $new_email;
                    $userData['phone'] = $new_phone;
                    $success = "Profile updated successfully!";
                }
            }
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $curr_pass    = $_POST['current_password'];
        $new_pass     = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        $db_pass = $_user->password;

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
            $hashed_new = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $_db->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            if($upd->execute([$hashed_new, $_SESSION['user_id']])) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to update password.";
            }
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
    <link rel="stylesheet" href="/CSS/app.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="profile-settings-body profile-settings">

<div id="photoModal" class="ps-modal">
    <div class="ps-modal-content">
        <h3 style="margin-top:0;">Default Photo</h3>
        <div class="ps-avatar-selection-container">
            <button class="ps-scroll-btn" type="button" onclick="scrollAvatars(-100)"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="ps-avatar-scroll" id="avatarScroll">
                <form method="POST" id="default-photo-form" style="display: flex; gap: 15px;">
                    <input type="hidden" name="set_default_photo" id="selected_avatar">
                    <img src="uploads/profiles/default.png.jpg" class="avatar-opt" data-path="uploads/profiles/default.png.jpg">
                    <img src="uploads/profiles/user2.jpg" class="avatar-opt" data-path="uploads/profiles/user2.jpg">
                    <img src="uploads/profiles/user3.jpg" class="avatar-opt" data-path="uploads/profiles/user3.jpg">
                    <img src="uploads/profiles/user4.jpg" class="avatar-opt" data-path="uploads/profiles/user4.jpg">
                    <img src="uploads/profiles/user5.jpg" class="avatar-opt" data-path="uploads/profiles/user5.jpg">
                </form>
            </div>
            <button class="ps-scroll-btn" type="button" onclick="scrollAvatars(100)"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div class="ps-divider"><span>OR</span></div>
        <div id="ps-drop-zone-box">
            <p id="drop-text"><i class="fa-solid fa-cloud-arrow-up"></i> Click or Drag to upload</p>
            <form id="upload-form" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="profile_photo" id="photo-input" accept="image/*">
            </form>
        </div>
        <button type="button" id="closeModalBtn" style="margin-top:15px; background:none; border:none; color:#64748b; cursor:pointer;">Cancel</button>
    </div>
</div>

<div class="ps-container">
    <div id="status-msg">
        <?php if ($error): ?> <div class="ps-msg error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="ps-msg success"><?php echo $success; ?></div> <?php endif; ?>
    </div>

    <div class="ps-header">
        <div class="ps-photo-container">
            <img src="<?php echo htmlspecialchars($userData['photo']); ?>" id="preview-img">
            <div class="ps-camera-btn" id="openModalBtn">
                <i class="fa-solid fa-camera"></i>
            </div>
        </div>
        <div class="ps-header-info">
            <h1><?php echo htmlspecialchars($userData['username']); ?></h1>
            <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($userData['phone']); ?></p>
        </div>
    </div>

    <div class="ps-action-btns">
        <button class="ps-btn-tab active" data-target="info">Manage Profile Info</button>
        <button class="ps-btn-tab" data-target="security">Manage Password</button>
    </div>

    <div id="info" class="ps-card active">
        <h2><i class="fa-solid fa-user-gear"></i> Profile Info</h2>
        <form method="POST">
            <div class="ps-form-group">
                <label>Username</label>
                <input type="text" 
                    name="username" 
                    value="<?php echo htmlspecialchars($userData['username']); ?>" 
                    class="ps-input-readonly" 
                    readonly>
            </div>
            <div class="ps-form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="no-space" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
            <div class="ps-form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="no-space" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="ps-btn-submit active-blue">Update Info</button>
        </form>
    </div>

    <div id="security" class="ps-card">
        <h2><i class="fa-solid fa-shield-halved"></i> Security</h2>
        <form method="POST" id="passwordForm">
            <div class="ps-form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="pass-input no-space" required>
            </div>
            <div class="ps-form-group">
                <label>New Password (8-12 chars)</label>
                <input type="password" name="new_password" id="new_pass" class="pass-input no-space" maxlength="12" required>
            </div>
            <div class="ps-form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="pass-input no-space" maxlength="12" required>
            </div>
            <button type="submit" name="change_password" id="changePassBtn" class="ps-btn-submit disabled-style">Change Password</button>
        </form>
    </div>

    <div class="ps-nav-links">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a> |
        <a href="logout.php" style="color:#ef4444;"><i class="fa-solid fa-power-off"></i> Logout</a>
    </div>
</div>

<script>
    $(document).ready(function() {
        setTimeout(() => { $('#status-msg').fadeOut(); }, 3000);

        $('#openModalBtn').on('click', () => $('#photoModal').show());
        $('#closeModalBtn').on('click', () => $('#photoModal').hide());

        $('.avatar-opt').on('click', function() {
            $('#selected_avatar').val($(this).data('path'));
            $('#default-photo-form').submit();
        });

        $('.ps-btn-tab').on('click', function() {
            const target = $(this).data('target');
            $('.ps-btn-tab').removeClass('active');
            $(this).addClass('active');
            $('.ps-card').hide().removeClass('active');
            $('#' + target).show().addClass('active');
        });

        $('.no-space').on('input', function() {
            $(this).val($(this).val().replace(/\s/g, ''));
        });

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

        const dropZone = $('#ps-drop-zone-box');
        dropZone.on('click', function() { $('#photo-input').trigger('click'); });
        $('#photo-input').on('click', (e) => e.stopPropagation());
        $('#photo-input').on('change', function() {
            if (this.files && this.files.length > 0) $('#upload-form').submit();
        });

        dropZone.on('dragover', (e) => {
            e.preventDefault();
            dropZone.addClass('dragover');
            $('#drop-text').html('<i class="fa-solid fa-file-import"></i> Drop to select file');
        });

        dropZone.on('dragleave', () => {
            dropZone.removeClass('dragover');
            $('#drop-text').html('<i class="fa-solid fa-cloud-arrow-up"></i> Click or Drag to upload');
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

    function scrollAvatars(distance) {
        $('#avatarScroll').animate({ scrollLeft: '+=' + distance }, 300);
    }
</script>
</body>
</html>