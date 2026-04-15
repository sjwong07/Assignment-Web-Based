<?php
session_start();
require_once '../../config/database.php';

$_title = "Admin Management";
include('../../_head.php');

$stmt = $pdo->prepare("SELECT * FROM user WHERE role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$admins = $stmt->fetchAll();
?>

<div class="container">
    <h1>Admin Management</h1>
    <a href="create.php" class="btn-add">+ Add New Admin</a>
    
        <table>
            <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= $a['user_id'] ?></td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td><?= htmlspecialchars($a['full_name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['phone']) ?></td>
                    <td><?= $a['gender'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $a['user_id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete.php?id=<?= $a['user_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this admin?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php include('../../_foot.php'); ?>