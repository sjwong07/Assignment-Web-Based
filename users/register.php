<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = 'member';
    
    if (empty($username) || empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($password)) {
        $error = 'All fields are required';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';

    } elseif (strlen($password) < 4) {
       $error = 'Password must be at least 4 characters';
    } else{

        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
       
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $profile_photo = 'default.png';
             $upload_path = 'uploads/profiles/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_photo']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $profile_photo = time() . '_' . $filename;
                    move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path . $profile_photo);
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO user (username, full_name, email, phone, gender, password, role, profile_photo, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $full_name, $email, $phone, $gender, $hashed_password, $role, $profile_photo]);
            
            header("Location:login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; padding: 10px; background: #ffebee; border-radius: 4px; }
        .photo-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-top: 10px; display: none; }
        .login-link { text-align: center; margin-top: 15px;}
        .login-link a {color: #3b82f6; font-weight: bold; text-decoration: none; }

    </style>
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone *</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Gender *</label>
                <select name="gender" required>
                    <option value="">Select</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Role *</label>
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" onchange="previewImage(this)">
                <img id="preview" class="photo-preview">
            </div>
            <button type="submit">Register</button>
        </form>
        
        <!-- BACK TO LOGIN -->
         <div class="login-link"> 
            Already have an account? 
            <a href="login.php">Login here</a>
        </div>

    </div>
    <script>
        function previewImage(input) {
            var preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>