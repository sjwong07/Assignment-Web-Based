<?php
session_start();
require_once '../config/database.php';

// Check login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$_title = "Admin Management";
include('../../_head.php');

// Handle Search logic
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM user WHERE role = 'admin' AND is_deleted = 0";

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
</style>

<?php foreach ($admins as $a): ?>
<tr>
    <td><?= $a['user_id'] ?></td>
    <td>
        <img src="../../assets/uploads/<?= $a['photo'] ?>" class="admin-photo" alt="profile">
    </td>
    <td><?= htmlspecialchars($a['username']) ?></td>
    </tr>
<?php endforeach; ?>

<div class="container">
    <h1>Admin Management</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
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
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Status</th> <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($admins) > 0): ?>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= $a['user_id'] ?></td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td><?= htmlspecialchars($a['full_name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
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
                           onclick="return confirm('Are you sure? This will remove the admin from the active list.')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No admins found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../../_foot.php'); ?>