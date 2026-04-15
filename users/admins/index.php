<?php
session_start();
require_once '../config/database.php';

// Check login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php'); // Fixed path to login
    exit();
}

$_title = "Admin Management";
include('../../lib/_head.php'); 

// Handle Search logic
$search = $_GET['search'] ?? '';
// FIXED: Added missing " quote and space at the end of the query string
$query = "SELECT * FROM user WHERE role = 'admin'"; 

if ($search) {
    $query .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $query .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
}

$admins = $stmt->fetchAll();
?>

<style>
    .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .status-active { background: #d4edda; color: #155724; }
    .status-blocked { background: #f8d7da; color: #721c24; }
    .search-box { margin-bottom: 20px; display: flex; gap: 10px; }
    .search-box input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex-grow: 1; }
    .admin-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;}
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; }
    .btn-edit { background: #ffc107; color: #000; }
    .btn-delete { background: #dc3545; color: #fff; }
    .btn-add { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
</style>

<div class="container">
    <h1>Admin Management</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; margin-top: 20px;">
        <a href="create.php" class="btn-add">+ Add New Admin</a>
        
        <form method="GET" class="search-box" style="margin-bottom: 0;">
            <input type="text" name="search" placeholder="Search by name, email..." value="<?= htmlspecialchars($search) ?>">
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
                    $fileName = (!empty($a['profile_photo'])) ? $a['profile_photo'] : 'default.png';
                        
                    $imagePath = "../../uploads/profiles/" . $fileName;
                ?>
                <img src="<?= $imagePath ?>?t=<?= time() ?>" 
                    alt="profile" 
                    style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
            </td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td><?= htmlspecialchars($a['full_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($a['email'] ?? 'N/A') ?></td>
                    <td>
                        <?php if (($a['status'] ?? 'active') == 'active'): ?>
                            <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-blocked">Blocked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $a['user_id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete.php?id=<?= $a['user_id'] ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No admins found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../../lib/_foot.php'); ?> 