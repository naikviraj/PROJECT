<?php
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/inc/header.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: auth.html');
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $errors[] = 'Invalid form submission (CSRF). Please reload the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($username === '' || $password === '') {
            $errors[] = 'Username and password are required.';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password should be at least 8 characters.';
        } else {
            try {
                $pdo = getPDO();
                // check duplicate username
                $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $errors[] = 'That username is already taken.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
                    $stmt->execute([$username, $hash]);
                    $success = 'Admin created successfully.';
                    // regenerate token to avoid double posts
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                }
            } catch (Exception $e) {
                error_log('admin_add error: ' . $e->getMessage());
                $errors[] = 'Server error. Check logs.';
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin — Add Administrator</title>
    <link rel="stylesheet" href="Page1.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="back">
    <main class="admin-wrap">
        <h1>Create Administrator</h1>

        <?php if (!empty($errors)): ?>
            <div class="msg error"><?php foreach($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="publish-card" style="max-width:540px;margin:12px auto;">
            <h2>New Admin</h2>
            <form class="publish-form" method="post" action="admin_add.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" required value="<?php echo isset($_POST['username'])?htmlspecialchars($_POST['username']):''; ?>">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="field">
                    <label for="password_confirm">Confirm Password</label>
                    <input id="password_confirm" name="password_confirm" type="password" required>
                </div>

                <div class="publish-actions">
                    <button class="btn-cta" type="submit">Create Admin</button>
                    <div class="note" style="margin-left:8px;align-self:center">Password must be at least 8 characters.</div>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
