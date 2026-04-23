<?php
require_once '../../config.php';

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$where_clause = "WHERE role = 'admin'";
if ($search) {
    $where_clause .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}

$sql = "SELECT * FROM user $where_clause ORDER BY user_id DESC";
$result = mysqli_query($connection, $sql);
$total_admins = mysqli_num_rows($result);

$_title = "Admin Management";
include('../../lib/_head.php');
?>

<div class="container" style="padding: 40px; background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="color: #0f172a; margin: 0; font-size: 1.8rem; font-weight: 800;">Admin Control Center</h1>
            <p style="color: #64748b; margin: 4px 0 0;">Manage your administrative team and system permissions.</p>
        </div>
        <div style="text-align: right;">
            <div style="margin-bottom: 10px; font-weight: 600; color: #64748b;">Staff Count: <span style="color: #4f46e5;"><?= $total_admins ?></span></div>
            <a href="create.php" style="background: #4f46e5; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Add New Admin
            </a>
        </div>
    </div>

    <div style="background: white; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 20px; color: #64748b; font-size: 0.75rem; text-transform: uppercase; width: 25%;">Administrator</th>
                    <th style="padding: 20px; color: #64748b; font-size: 0.75rem; text-transform: uppercase; width: 25%;">Email Address</th>
                    <th style="padding: 20px; color: #64748b; font-size: 0.75rem; text-transform: uppercase; width: 25%;">Activity Logs</th>
                    <th style="padding: 20px; color: #64748b; font-size: 0.75rem; text-transform: uppercase; width: 25%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = mysqli_fetch_assoc($result)): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px 20px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?= BASE_URL ?>/uploads/profiles/<?= !empty($admin['profile_photo']) ? $admin['profile_photo'] : 'default.png' ?>"
                            onerror ="this.src'<?= BASE_URL ?>/uploads/profiles/default.png';"
                                 style="width: 44px; height: 44px; border-radius: 10px; object-fit: cover;">
                            <div>
                                <div style="color: #0f172a; font-weight: 700;"><?= htmlspecialchars($admin['username']) ?></div>
                                <div style="color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($admin['full_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    
                    <td style="padding: 15px 20px; color: #475569; font-size: 0.9rem;">
                        <?= htmlspecialchars($admin['email']) ?>
                    </td>
                    
                    <td style="padding: 15px 20px;">
                        <div style="font-size: 0.85rem; color: #0f172a; font-weight: 600;">
                            Joined: <span style="font-weight: 400; color: #64748b;"><?= date('M d, Y', strtotime($admin['created_at'])) ?></span>
                        </div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">
                            Updated: <?= date('M d, Y H:i', strtotime($admin['updated_at'])) ?>
                        </div>
                    </td>

                    <td style="padding: 15px 20px; text-align: center;">
                        <div style="display: inline-flex; gap: 6px; background: #f8fafc; padding: 4px; border-radius: 10px; border: 1px solid #e2e8f0;">
                            
                            <?php $isBlocked = ($admin['is_blocked'] == 1); ?>
                            
                            <a href="toggle_block.php?id=<?= $admin['user_id'] ?>" 
                               style="display: flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: bold; color: white; background: <?= $isBlocked ? '#10b981' : '#f59e0b' ?>;"
                               onclick="return confirm('Update admin access status?');">
                                <?php if ($isBlocked): ?>
                                    <i class="fas fa-user-check" style="margin-right: 6px;"></i> Unblock
                                <?php else: ?>
                                    <i class="fas fa-user-slash" style="margin-right: 6px;"></i> Block
                                <?php endif; ?>
                            </a>

                            <a href="edit.php?id=<?= $admin['user_id'] ?>" 
                               style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: #eff6ff; color: #2563eb; text-decoration: none;"
                               title="Edit Admin">
                                <i class="fas fa-edit"></i>
                            </a>

                            <?php if($admin['user_id'] != $_SESSION['user_id']): ?>
                                <a href="delete.php?id=<?= $admin['user_id'] ?>" 
                                   style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: #fff1f2; color: #e11d48; text-decoration: none;"
                                   onclick="return confirm('Permanently remove this admin?')"
                                   title="Delete Admin">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../../lib/_foot.php'); ?>