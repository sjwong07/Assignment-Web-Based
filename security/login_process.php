if (isset($_POST['remember_me'])) {
    // Generate random strings
    $selector = bin2hex(random_bytes(6)); // 12 chars
    $validator = bin2hex(random_bytes(32)); // 64 chars
    $expires = date('Y-m-d H:i:s', time() + 864000); // 10 days from now

    // Store in Cookie (selector:validator)
    setcookie('remember_me', $selector . ':' . $validator, time() + 864000, '/', '', false, true);

    // Store hashed validator in Database
    $token_stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, selector, hashed_validator, expires) VALUES (?, ?, ?, ?)");
    $token_stmt->execute([$user['id'], $selector, password_hash($validator, PASSWORD_DEFAULT), $expires]);
}