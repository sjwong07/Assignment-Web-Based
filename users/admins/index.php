<?php
session_start();
require_once '../../config/database.php';

$stmt = $pdo->prepare("SELECT * FROM user WHERE role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { margin-bottom: 20px; border-left: 4px solid #28a745; padding-left: 15px; }
        .btn-add { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #28a745; color: white; }
        .btn { padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; font-size: 12px; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; color: white; }
    </style>
</head>
<body>
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
</body>
</html>