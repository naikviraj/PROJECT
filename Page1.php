<?php
require_once __DIR__ . '/inc/header.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Pet Adoption website</title>
        <link rel="stylesheet" type="text/css" href="Page1.css">
        <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    </head>
    <body class="back">
        <?php // header included above ?>

        <div>
        <h1 class="adoption-quote">
        <span class="red-text">Adopt.</span>
        <span class="black-text">Don't Shop 🐾</span>
        </h1>
        </div>

        <div class="dog-section hero-card">
    <div class="dog-left">
        <img src="IMAGES/dogocat1.png" alt="dog" class="dog-img">
    </div>

    <div class="dog-right">
        <h2 class="hero-title">Guaranteed furever friends. Find your Pawfect Match today!</h2>
        <p class="hero-sub"><span class="typewriter" aria-live="polite"></span></p>

        <div class="cta-row">
            <a class="btn-cta" href="pets.php" aria-label="Browse pets">
                <img src="IMAGES/adoptme.png" alt="Browse pets" class="browse-img">
            </a>
        </div>
    </div>
</div>

    <?php include __DIR__ . '/inc/footer.php'; ?>
    </body>
    <script>
        (function(){
            const phrases = [
                'Discover friendly, vaccinated pets ready for a loving home.',
                'Browse profiles, meet your match and adopt with confidence.',
                'Trained, healthy, and full of cuddles — find your companion today!'
            ];
            const el = document.querySelector('.typewriter'); if (!el) return;
            const typingSpeed = 28, pauseAfter = 1300, fadeDuration=200;
            let phraseIndex=0;
            function sleep(ms){return new Promise(r=>setTimeout(r,ms));}
            async function typePhrase(p){for(let i=0;i<p.length;i++){el.textContent=p.slice(0,i+1);await sleep(typingSpeed);} }
            async function run(){ while(true){ const cur=phrases[phraseIndex]; el.classList.remove('fade-out'); await typePhrase(cur); await sleep(pauseAfter); el.classList.add('fade-out'); await sleep(fadeDuration); el.textContent=''; el.classList.remove('fade-out'); phraseIndex=(phraseIndex+1)%phrases.length; await sleep(180);} }
            setTimeout(run,700);
        })();
    </script>
</html>
