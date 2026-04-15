<?php
session_start();
require_once '../../config/database.php';

// SECURITY (admin only)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied");
}

// SEARCH
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// PAGINATION
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$limit = (int)$limit;
$page = (int)$page;
$offset = (int)$offset;


$sql = "SELECT * FROM user WHERE role='member'";
$params = [];

if ($search) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status === 'active') {
    $sql .= " AND is_blocked = 0";
} elseif ($status === 'blocked') {
    $sql .= " AND is_blocked = 1";
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();


$countSql = "SELECT COUNT(*) FROM user WHERE role='member'";
$countParams = [];

if ($search) {
    $countSql .= " AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

if ($status === 'active') {
    $countSql .= " AND is_blocked = 0";
} elseif ($status === 'blocked') {
    $countSql .= " AND is_blocked = 1";
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// DASHBOARD STATS
$total = $totalRows;
$active = $pdo->query("SELECT COUNT(*) FROM user WHERE role='member' AND is_blocked=0")->fetchColumn();
$blocked = $pdo->query("SELECT COUNT(*) FROM user WHERE role='member' AND is_blocked=1")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Management</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 12px;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            flex: 1;
            padding: 15px;
            color: white;
            border-radius: 10px;
            text-align: center;
        }

        .blue { background: #007bff; }
        .green { background: #28a745; }
        .red { background: #dc3545; }

        .search-box {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 12px;
        }

        td {
            padding: 12px;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .active { color: green; font-weight: bold; }
        .blocked { color: red; font-weight: bold; }

        .btn {
            padding: 6px 12px;
            border-radius: 20px;
            border: none;
            font-size: 12px;
            color: white;
            cursor: pointer;
        }

        .btn-view { background: #17a2b8; }
        .btn-block { background: #ffc107; color:black; }
        .btn-unblock { background: #28a745; }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            padding: 8px 12px;
            background: #667eea;
            color: white;
            margin: 3px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1> Member Management</h1>

    <!-- DASHBOARD -->
    <div class="stats">
        <div class="card blue">Total<br><b><?= $total ?></b></div>
        <div class="card green">Active<br><b><?= $active ?></b></div>
        <div class="card red">Blocked<br><b><?= $blocked ?></b></div>
    </div>

    <!-- SEARCH + FILTER -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search..."
                   value="<?= htmlspecialchars($search) ?>">

            <select name="status">
                <option value="" <?= $status==''?'selected':'' ?>>All</option>
                <option value="active" <?= $status=='active'?'selected':'' ?>>Active</option>
                <option value="blocked" <?= $status=='blocked'?'selected':'' ?>>Blocked</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- TABLE -->
    <table>
        <thead>
        <tr>
            <th>Photo</th>
            <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td>
                    <img src="../../uploads/profiles/<?= $m['profile_photo'] ?: 'default.png' ?>"
                         class="profile-img">
                </td>

                <td><?= $m['user_id'] ?></td>
                <td><?= htmlspecialchars($m['username']) ?></td>
                <td><?= htmlspecialchars($m['full_name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>

                <td class="<?= $m['is_blocked'] ? 'blocked' : 'active' ?>">
                    <?= $m['is_blocked'] ? 'Blocked' : 'Active' ?>
                </td>

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

    <!-- PAGINATION -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</div>

<script>
$(document).ready(function() {
    $('.toggle-block').click(function() {
        var btn = $(this);
        var userId = btn.data('id');
    
        $.ajax({
            url: 'block.php',
            type: 'POST',
            data: {
                user_id: userId,
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                     showPopup(res.new_status ? "User Blocked" : "User Unblocked");
                    
                     // UPDATE BUTTON
                    btn.text(res.new_status ? "Unblock" : "Block");
                    btn.data('status', res.new_status);

                    // UPDATE STATUS TEXT
                    var row = btn.closest("tr");
                    var statusCell = row.find("td:nth-child(6)");

                    statusCell.text(res.new_status ? "Blocked" : "Active");
                    statusCell.removeClass("active blocked");
                    statusCell.addClass(res.new_status ? "blocked" : "active");

                } else {
                    alert("Error updating user");
                }
            }
        });
    });

});

function showPopup(message) {
    let popup = document.getElementById("popup");

    popup.textContent = message;
    popup.style.display = "block";

    setTimeout(() => {
        popup.style.display = "none";
    }, 2000);
}
</script>

<div id="popup" style="
    display:none;
    position:fixed;
    top:20px;
    right:20px;
    background:#28a745;
    color:white;
    padding:12px 18px;
    border-radius:8px;
    z-index:9999;
"></div>

</body>
</html>

