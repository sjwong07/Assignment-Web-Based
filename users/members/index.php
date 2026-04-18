<?php
require_once '../config.php'; 

// 2. Security Check: Only admins can manage members
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$members = [];

// 3. Search and Fetch Logic using MySQLi ($connection)
if ($search) {
    $searchTerm = "%$search%";
    $sql = "SELECT * FROM user WHERE role = 'member' AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM user WHERE role = 'member' ORDER BY user_id DESC";
    $result = mysqli_query($connection, $sql);
}

while ($row = mysqli_fetch_assoc($result)) {
    $members[] = $row;
}

$_title = "Member Maintenance";
include('../../lib/_head.php'); 
?>

<style>
    .container { padding: 40px; }
    table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .status-active { color: #28a745; font-weight: bold; }
    .status-blocked { color: #dc3545; font-weight: bold; }
    .action-links a { margin-right: 10px; text-decoration: none; color: #3b82f6; font-size: 14px; }
    .action-links a:hover { text-decoration: underline; }
    .search-input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 300px; }
    .btn-search { padding: 8px 15px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; }
</style>

<div class="container">
    <h2>Member Maintenance</h2>

    <form method="GET" style="margin: 20px 0;">
        <input type="text" name="search" class="search-input" placeholder="Search by name, email, or username..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
        <?php if($search): ?> <a href="index.php" style="margin-left:10px;">Clear</a> <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($members) > 0): ?>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td>
                        <?php
                        // Match column 'profile_photo' from dbA.sql
                        $userPhoto = !empty($m['profile_photo']) ? $m['profile_photo'] : 'default.png.jpg';
                        ?>
                        <img src="../../uploads/profiles/<?= $userPhoto ?>" 
                             alt="Profile" 
                             style="width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd;">
                    </td>
                    <td><?= htmlspecialchars($m['full_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td>
                        <?php if ($m['is_blocked'] == 0): ?>
                            <span class="status-active">ACTIVE</span>
                        <?php else: ?>
                            <span class="status-blocked">BLOCKED</span>
                        <?php endif; ?>
                    </td>
                    <td class="action-links">
                        <a href="detail.php?id=<?= $m['user_id'] ?>">View</a>
                        <a href="edit.php?id=<?= $m['user_id'] ?>">Edit</a>
                        <a href="toggle.php?id=<?= $m['user_id'] ?>&current=<?= $m['is_blocked'] ?>" 
                           onclick="return confirm('Change status for this user?')">Toggle Status</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No members found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../../lib/_foot.php'); ?>