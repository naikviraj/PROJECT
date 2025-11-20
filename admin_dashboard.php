<?php
require_once __DIR__ . '/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: auth.html'); exit;
}

$pdo = getPDO();
// handle simple delete via GET ?delete=
if (!empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT image_path FROM pets WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        // remove image file if exists
        if (!empty($row['image_path']) && file_exists(__DIR__ . '/' . $row['image_path'])) {
            @unlink(__DIR__ . '/' . $row['image_path']);
        }
        $stmt = $pdo->prepare('DELETE FROM pets WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: admin_dashboard.php'); exit;
}

// Fetch pets and any adoption/user info so admins can see who adopted a pet
$stmt = $pdo->query("SELECT pets.*, u.name AS adopter_name, a.adopted_at 
    FROM pets 
    LEFT JOIN adoptions a ON a.pet_id = pets.id 
    LEFT JOIN users u ON u.id = a.user_id 
    ORDER BY published_at DESC");
$pets = $stmt->fetchAll();

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pawfect Match — Admin Dashboard</title>
    <link rel="stylesheet" href="Page1.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="back">
    <?php include __DIR__ . '/inc/header.php'; ?>

    <main class="admin-wrap">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <div class="dashboard-actions">
                <a class="btn-admin" href="admin_add.php" aria-label="Create admin">
                    <span class="btn-icon">+</span>
                    <span class="btn-label">New Admin</span>
                    <span class="btn-caret">▾</span>
                </a>
            </div>
        </div>

        <?php if (!empty($_GET['published'])): ?>
            <div class="msg success">Pet published successfully.</div>
        <?php elseif (!empty($_GET['updated'])): ?>
            <div class="msg success">Pet updated successfully.</div>
        <?php elseif (!empty($_GET['error'])): ?>
            <div class="msg error">An error occurred. <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <section>
                <h2>Publish New Pet</h2>
                <div class="publish-card">
                    <div class="image-preview" id="imagePreview">
                        <span class="preview-placeholder">No preview</span>
                        <img id="imagePreviewImg" src="" alt="Image preview" style="display:none">
                    </div>
                    <form class="publish-form" method="post" action="publish_pet.php" enctype="multipart/form-data">
                        <div class="field">
                            <label>Name</label>
                            <input name="name" required>
                        </div>

                        <div class="field">
                            <label>Type</label>
                            <select name="type"><option value="dog">Dog</option><option value="cat">Cat</option></select>
                        </div>

                        <div class="field">
                            <label>Breed</label>
                            <input name="breed">
                        </div>

                        <div class="field">
                            <label>Age</label>
                            <input name="age" type="number" min="0">
                        </div>

                        <div class="field full">
                            <label>Description</label>
                            <textarea name="description" rows="4"></textarea>
                        </div>

                        <div class="field full">
                            <label>Image</label>
                            <input type="file" name="image" accept="image/*" required>
                        </div>

                        <div class="publish-actions">
                            <button type="submit" class="btn-cta">Publish Pet</button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="admin-sep" aria-hidden="true"></div>

            <section>
                <h2>Published Ads</h2>
                <div class="cards-grid">
                    <?php foreach($pets as $p): ?>
                    <article class="pet-card">
                        <div class="pet-media">
                            <?php if(!empty($p['image_path'])): ?>
                                <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php else: ?>
                                <div class="no-image">No image</div>
                            <?php endif; ?>
                        </div>
                        <div class="pet-body">
                            <h3 class="pet-title"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="pet-meta"><?= htmlspecialchars(ucfirst($p['type'])) ?> — <?= htmlspecialchars($p['breed']) ?></p>
                            <p class="pet-age">Age: <?= (int)$p['age'] ?></p>
                            <?php if (!empty($p['is_adopted'])): ?>
                                <div class="adopted-label">Adopted by: <?= htmlspecialchars($p['adopter_name'] ?? 'Unknown') ?></div>
                            <?php endif; ?>
                            <div class="pet-actions">
                                <a class="icon-btn" href="edit_pet.php?id=<?= (int)$p['id'] ?>" aria-label="Edit <?= htmlspecialchars($p['name']) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit
                                </a>
                                <a class="icon-btn btn-danger" href="delete_pet.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this pet ad?')" aria-label="Delete <?= htmlspecialchars($p['name']) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-4.5l-1-1z"/></svg>
                                    Delete
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
</body>

<script>
// Live image preview for publish form
;(function(){
    const fileInput = document.querySelector('.publish-form input[type=file]');
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('imagePreviewImg');
    const placeholder = preview.querySelector('.preview-placeholder');
    if (!fileInput || !preview || !previewImg) return;

    fileInput.addEventListener('change', function(e){
        const file = this.files && this.files[0];
        if (!file) {
            previewImg.src = '';
            previewImg.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
            return;
        }
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function(ev){
            previewImg.src = ev.target.result;
            previewImg.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>
</html>
</body>
</html>
