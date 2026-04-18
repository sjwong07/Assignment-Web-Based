<?php
require_once '../../config.php'; // Path depends on folder depth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$_title = "Admin Management";
include('../lib/_head.php'); 

// 3. Handle Search logic using MySQLi
$search = $_GET['search'] ?? '';
$admins = [];

if ($search) {
    $searchTerm = "%$search%";
    $query = "SELECT * FROM user WHERE role = 'admin' AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $query = "SELECT * FROM user WHERE role = 'admin' ORDER BY user_id DESC";
    $result = mysqli_query($connection, $query);
}

while ($row = mysqli_fetch_assoc($result)) {
    $admins[] = $row;
}
?>

<style>
    /* ... keep your existing styles ... */
    .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .status-active { background: #d4edda; color: #155724; }
    .status-blocked { background: #f8d7da; color: #721c24; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-right: 5px; }
    .btn-edit { background: #ffc107; color: #000; }
    .btn-delete { background: #dc3545; color: #fff; }
</style>

<div class="container" style="padding: 20px;">
    <h1>Admin Management</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
        <a href="create.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">+ Add New Admin</a>
        
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="submit">Search</button>
            <?php if($search): ?> <a href="index.php">Clear</a> <?php endif; ?>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Status</th> 
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($admins) > 0): ?>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td>
                        <?php 
                            $img = !empty($a['profile_photo']) ? $a['profile_photo'] : 'default.png.jpg';
                            $imagePath = "../uploads/profiles/" . $img;
                        ?>
                        <img src="<?= $imagePath ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    </td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td><?= htmlspecialchars($a['full_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($a['email'] ?? 'N/A') ?></td>
                    <td>
                        <?php if ($a['is_blocked'] == 0): ?>
                            <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-blocked">Blocked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $a['user_id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete.php?id=<?= $a['user_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No admins found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../lib/_foot.php'); ?>