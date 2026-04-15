<?php
session_start();
// Go up TWO levels: members -> users -> root
require_once '../../config/database.php'; 

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// SQL: Only show members
$sql = "SELECT * FROM user WHERE role = 'member' AND is_deleted = 0";

if ($search) {
    $sql .= " AND (username LIKE :s OR email LIKE :s OR full_name LIKE :s)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query($sql);
}
$members = $stmt->fetchAll();

$_title = "Member Maintenance";
include('../../_head.php'); 
?>

<div class="container">
    <h2>Member Maintenance</h2>

    <form method="GET" style="margin: 20px 0;">
        <input type="text" name="search" placeholder="Search members..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
                <td>
                    <?php
                    $userPhoto = !empty($m['photo']) ? $m['photo'] : 'default.png';
                    ?>
                    
                    <img src="../../assets/uploads/<?= $userPhoto ?>" 
                     alt="Profile Picture" 
                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd;">

                </td>
                <td><?= htmlspecialchars($m['full_name']) ?></td>
                <td><?= strtoupper($m['status']) ?></td>
                <td>
                    <a href="detail.php?id=<?= $m['user_id'] ?>">View</a>
                    <a href="edit.php?id=<?= $m['user_id'] ?>">Edit</a>
                    <a href="toggle.php?id=<?= $m['user_id'] ?>&status=<?= $m['status'] ?>">Toggle Status</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../../_foot.php'); ?>