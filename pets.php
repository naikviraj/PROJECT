<?php
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/inc/header.php';

// capture any flash message (pet-specific) before fetching pets
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_pet_id = isset($_SESSION['flash_pet_id']) ? (int)$_SESSION['flash_pet_id'] : null;
// clear session flash immediately so it only applies to this request
unset($_SESSION['flash_success'], $_SESSION['flash_pet_id']);

// recent adopted list from session (persist across this session)
$recent_adopted = $_SESSION['recent_adopted'] ?? [];
if (!is_array($recent_adopted)) $recent_adopted = [];
$recent_adopted = array_map('intval', $recent_adopted);

$pdo = getPDO();
// fetch published pets; normally exclude adopted, but if we have a flash_pet_id
$include_ids = $recent_adopted;
if ($flash_pet_id) $include_ids[] = (int)$flash_pet_id;
$include_ids = array_values(array_unique(array_filter($include_ids, function($v){ return $v > 0; })));

if (count($include_ids) > 0) {
    // build placeholders
    $placeholders = implode(',', array_fill(0, count($include_ids), '?'));
    $sql = "SELECT * FROM pets WHERE is_published = 1 AND (is_adopted = 0 OR id IN ($placeholders)) ORDER BY published_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($include_ids);
} else {
    $stmt = $pdo->prepare('SELECT * FROM pets WHERE is_published = 1 AND is_adopted = 0 ORDER BY published_at DESC');
    $stmt->execute();
}
$pets = $stmt->fetchAll();

function esc($s){ return htmlspecialchars($s, ENT_QUOTES); }
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pawfect Match — Pets</title>
    <link rel="stylesheet" href="Pets.css">
</head>
<body>
    <main class="container">
        <?php
        // show top-page banner if we have a flash for this request
        if (!empty($flash_success)):
            // try to find the pet name for context
            $flash_pet_name = null;
            if ($flash_pet_id) {
                foreach ($pets as $pp) {
                    if ((int)$pp['id'] === (int)$flash_pet_id) { $flash_pet_name = $pp['name']; break; }
                }
            }
        ?>
            <div class="top-badge"><?= htmlspecialchars($flash_success) ?><?php if ($flash_pet_name): ?> — <strong><?= htmlspecialchars($flash_pet_name) ?></strong><?php endif; ?></div>
        <?php endif; ?>

        <h1 class="page-title">Available Pets</h1>

        <?php
        // build unique breeds list for the species select
        $breeds = array_values(array_unique(array_filter(array_map(function($p){ return trim($p['breed'] ?? ''); }, $pets))));
        sort($breeds, SORT_STRING | SORT_FLAG_CASE);
        ?>

        <div class="filters" role="region" aria-label="Pet filters">
            <div class="type-filters" role="tablist" aria-label="Type filter">
                <button type="button" class="type-btn active" data-type="">All</button>
                <button type="button" class="type-btn" data-type="dog">Dogs</button>
                <button type="button" class="type-btn" data-type="cat">Cats</button>
            </div>

            <div class="species-group">
                <label class="species-label" for="speciesSelect">Species / Breed:</label>
                <select id="speciesSelect" aria-label="Filter by breed">
                    <option value="">All</option>
                    <?php foreach ($breeds as $b): ?>
                        <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input id="searchInput" type="search" placeholder="Search name or description..." aria-label="Search pets">
        </div>

        <section id="petsGrid" class="pets-grid">
            <?php foreach($pets as $pet): ?>
                <article class="pet-card" data-type="<?= esc($pet['type']) ?>" data-species="<?= esc($pet['breed']) ?>">
                    <img src="<?= esc($pet['image_path']) ?>" alt="<?= esc($pet['name']) ?>">
                    <h3><?= esc($pet['name']) ?></h3>
                    <p class="pet-id">ID: <?= (int)$pet['id'] ?></p>
                    <p class="meta"><?= esc(ucfirst($pet['type'])) ?> — <?= esc($pet['breed']) ?></p>
                    <p>Age: <?= (int)$pet['age'] ?> • <?= esc($pet['description']) ?></p>
                    <?php
                    // Show badge for any recently adopted pets (session-scoped)
                    $is_recent = in_array((int)$pet['id'], $recent_adopted, true);
                    if ($is_recent):
                    ?>
                        <div class="adopt-badge" aria-hidden="true">Adoption requested</div>
                        <?php if ($flash_success && $flash_pet_id === (int)$pet['id']): ?>
                            <div class="card-toast" role="status" aria-live="polite" aria-hidden="true"><?= htmlspecialchars($flash_success) ?></div>
                        <?php endif; ?>
                    <?php elseif (!empty($_SESSION['user_id'])): ?>
                        <form method="post" action="adopt.php" class="adopt-form">
                            <input type="hidden" name="pet_id" value="<?= (int)$pet['id'] ?>">
                            <button class="adopt-btn" type="submit">Adopt</button>
                        </form>
                    <?php else: ?>
                        <p style="font-style:italic;color:#666;">Login to adopt</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
    <?php include __DIR__ . '/inc/footer.php'; ?>

    <script>
    (function(){
        const typeBtns = document.querySelectorAll('.type-btn');
        const species = document.getElementById('speciesSelect');
        const search = document.getElementById('searchInput');
        const cards = Array.from(document.querySelectorAll('.pet-card'));

        function applyFilter(){
            const type = document.querySelector('.type-btn.active')?.dataset.type || '';
            const sp = (species?.value || '').toLowerCase();
            const q = (search?.value || '').trim().toLowerCase();

            cards.forEach(c => {
                const ctype = (c.dataset.type || '').toLowerCase();
                const cbreed = (c.dataset.species || '').toLowerCase();
                const name = (c.querySelector('h3')?.textContent || '').toLowerCase();
                const desc = (Array.from(c.querySelectorAll('p')).map(p=>p.textContent).join(' ')).toLowerCase();

                let ok = true;
                if (type && ctype !== type) ok = false;
                if (sp && cbreed !== sp) ok = false;
                if (q && !(name.includes(q) || desc.includes(q))) ok = false;

                c.style.display = ok ? '' : 'none';
            });
        }

        typeBtns.forEach(b => {
            b.addEventListener('click', function(){
                typeBtns.forEach(x=>x.classList.remove('active'));
                this.classList.add('active');
                applyFilter();
            });
        });

        if (species) species.addEventListener('change', applyFilter);
        if (search) search.addEventListener('input', applyFilter);
        // Animated inline toasts: show, then hide after a short delay (do not remove the card)
        document.querySelectorAll('.card-toast').forEach(function(t){
            requestAnimationFrame(function(){ t.classList.add('show'); });
            setTimeout(function(){
                t.classList.remove('show');
                t.classList.add('hide');
                setTimeout(function(){ if (t && t.parentNode) t.parentNode.removeChild(t); }, 300);
            }, 3000);
        });
    })();
    </script>
</body>
</html>
