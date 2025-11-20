<?php
require_once __DIR__ . '/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) { header('Location: auth.html'); exit; }

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: admin_dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? 'dog');
    $breed = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $is_published = !empty($_POST['is_published']) ? 1 : 0;

    $stmt = $pdo->prepare('UPDATE pets SET name=?, `type`=?, breed=?, age=?, description=?, is_published=?, published_at = CASE WHEN ?=1 THEN NOW() ELSE published_at END WHERE id=?');
    $stmt->execute([$name, $type, $breed, $age, $description, $is_published, $is_published, $id]);

    // optionally replace image
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $filename = uniqid('pet_', true) . '.' . $ext;
            $target = 'IMAGES/' . $filename;
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $target)) {
                // delete old
                $stmt = $pdo->prepare('SELECT image_path FROM pets WHERE id=?'); $stmt->execute([$id]); $old = $stmt->fetch();
                if ($old && !empty($old['image_path']) && file_exists(__DIR__ . '/' . $old['image_path'])) @unlink(__DIR__ . '/' . $old['image_path']);
                $stmt = $pdo->prepare('UPDATE pets SET image_path = ? WHERE id = ?'); $stmt->execute([$target, $id]);
            }
        }
    }

    header('Location: admin_dashboard.php?updated=1'); exit;
}

$stmt = $pdo->prepare('SELECT * FROM pets WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$pet = $stmt->fetch();
if (!$pet) { header('Location: admin_dashboard.php'); exit; }

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Pet — <?= htmlspecialchars($pet['name']) ?></title>
    <link rel="stylesheet" href="Page1.css">
    <style>
        .edit-wrap{max-width:920px;margin:28px auto;padding:18px}
        .edit-card{display:flex;gap:20px;background:linear-gradient(180deg, rgba(255,255,255,0.9), #fff);padding:18px;border-radius:14px;box-shadow:0 14px 36px rgba(2,40,70,0.06);}
        .pet-image{width:320px;height:320px;border-radius:12px;overflow:hidden;flex:0 0 320px;background:#f6f6f6;display:flex;align-items:center;justify-content:center}
        .pet-image img{width:100%;height:100%;object-fit:cover}
        .edit-form{flex:1}
        .edit-form label{display:block;margin:10px 0;font-weight:600;color:#051650}
        .edit-form input[type="text"], .edit-form input[type="number"], .edit-form select, .edit-form textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #e0e0e0}
        .actions-row{display:flex;gap:12px;margin-top:12px}
    </style>
</head>
<body class="back">
    <?php include __DIR__ . '/inc/header.php'; ?>
    <main class="edit-wrap">
        <h1 style="color:#051650">Edit Pet — <?= htmlspecialchars($pet['name']) ?></h1>
        <div class="edit-card">
            <div class="pet-image">
                <?php if (!empty($pet['image_path'])): ?>
                    <img src="<?= htmlspecialchars($pet['image_path']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                <?php else: ?>
                    <div style="color:#999">No image</div>
                <?php endif; ?>
            </div>

            <div class="edit-form">
                <form method="post" enctype="multipart/form-data">
                    <label>Name</label>
                    <input name="name" value="<?= htmlspecialchars($pet['name']) ?>">

                    <label>Type</label>
                    <select name="type"><option value="dog" <?= $pet['type']=='dog'?'selected':'' ?>>Dog</option><option value="cat" <?= $pet['type']=='cat'?'selected':'' ?>>Cat</option></select>

                    <label>Breed</label>
                    <input name="breed" value="<?= htmlspecialchars($pet['breed']) ?>">

                    <label>Age</label>
                    <input name="age" type="number" value="<?= (int)$pet['age'] ?>">

                    <label>Description</label>
                    <textarea name="description" rows="5"><?= htmlspecialchars($pet['description']) ?></textarea>

                    <label>Replace Image</label>
                    <input type="file" name="image" accept="image/*">

                    <label><input type="checkbox" name="is_published" value="1" <?= $pet['is_published'] ? 'checked' : '' ?>> Published</label>

                    <div class="actions-row">
                        <button type="submit" class="btn-cta">Save Changes</button>
                        <a href="admin_dashboard.php" class="btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
