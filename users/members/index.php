<?php
session_start();
require_once '../../config/database.php';

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = 'member' AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?) ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = 'member' ORDER BY created_at DESC");
    $stmt->execute();
}
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Management</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { margin-bottom: 20px; border-left: 4px solid #007bff; padding-left: 15px; }
        .stats { background: #e3f2fd; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .search-box { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .search-box input { padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f5f5f5; }
        .active { color: green; font-weight: bold; }
        .blocked { color: red; font-weight: bold; }
        .btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; font-size: 12px; }
        .btn-view { background: #17a2b8; color: white; }
        .btn-block { background: #ffc107; color: black; }
        .btn-unblock { background: #28a745; color: white; }
        .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Member Management</h1>
        
        <div class="stats">
            Total Members: <?= count($members) ?>
        </div>
        
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name, email or username..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <?php if ($search): ?>
                    <a href="index.php">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr><th>Photo</th><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><img src="../../uploads/profiles/<?= $m['profile_photo'] ?: 'default.png' ?>" class="profile-img" onerror="this.src='../../uploads/profiles/default.png'"></td>
                    <td><?= $m['user_id'] ?></td>
                    <td><?= htmlspecialchars($m['username']) ?></td>
                    <td><?= htmlspecialchars($m['full_name']) ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><?= htmlspecialchars($m['phone']) ?></td>
                    <td><?= $m['gender'] ?></td>
                    <td><span class="<?= $m['is_blocked'] ? 'blocked' : 'active' ?>"><?= $m['is_blocked'] ? 'Blocked' : 'Active' ?></span></td>
                    <td><?= date('Y-m-d', strtotime($m['created_at'])) ?></td>
                    <td>
                        <a href="detail.php?id=<?= $m['user_id'] ?>" class="btn btn-view">View</a>
                        <button class="btn <?= $m['is_blocked'] ? 'btn-unblock' : 'btn-block' ?> toggle-block" 
                                data-id="<?= $m['user_id'] ?>" 
                                data-status="<?= $m['is_blocked'] ?>">
                            <?= $m['is_blocked'] ? 'Unblock' : 'Block' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    $(document).ready(function() {
        $('.toggle-block').click(function() {
            var btn = $(this);
            var userId = btn.data('id');
            var currentStatus = btn.data('status');
            
            $.ajax({
                url: 'block.php',
                type: 'POST',
                data: {user_id: userId, current_status: currentStatus},
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + res.error);
                    }
                },
                error: function() {
                    alert('Something went wrong');
                }
            });
        });
    });
    </script>
</body>
</html>

