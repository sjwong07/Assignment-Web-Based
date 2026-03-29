session_start();

function login($username, $password, $db) {
    if (empty($username) || empty($password)) 
        return false;

    $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function authorize($role_required) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role_required) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}