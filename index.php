<?php
session_start();
require_once __DIR__ . '/configs/config.php';
include __DIR__ . '/includes/header.php';

function resolveReelThumbnailPath(string $thumbnail = ''): string
{
    $default = '/FoodFusion-LYH/assets/images/logo4.png';

    if ($thumbnail === '') {
        return $default;
    }

    $storedThumb = trim($thumbnail);
    $storedThumb = str_replace('/FoodFusion-LYH/', '', $storedThumb);

    $candidates = [
        $_SERVER['DOCUMENT_ROOT'] . '/FoodFusion-LYH/' . ltrim($storedThumb, '/'),
        __DIR__ . '/' . ltrim($storedThumb, '/'),
        __DIR__ . '/assets/images/reels/' . basename($storedThumb),
        __DIR__ . '/uploads/reels/' . basename($storedThumb),
    ];

    foreach ($candidates as $fullPath) {
        if (file_exists($fullPath)) {
            if (strpos($fullPath, $_SERVER['DOCUMENT_ROOT']) === 0) {
                return str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);
            }

            $relativePath = str_replace(__DIR__ . '/', '', $fullPath);
            return '/FoodFusion-LYH/' . ltrim(str_replace('\\', '/', $relativePath), '/');
        }
    }

    return $default;
}

/*
|--------------------------------------------------------------------------
| Top 10 Most Liked Recipes
|--------------------------------------------------------------------------
*/
$featuredStmt = $pdo->query("

    SELECT 

        r.recipe_id,

        r.title,

        r.description,

        r.image,

        r.cook_time,

        r.created_at,

        c.cuisine_name,

        COUNT(DISTINCT f.favorite_id) AS like_count

    FROM recipes r

    LEFT JOIN cuisines c ON r.cuisine_id = c.cuisine_id

    LEFT JOIN favorites f ON r.recipe_id = f.recipe_id

    GROUP BY

        r.recipe_id,

        r.title,

        r.description,

        r.image,

        r.cook_time,

        r.created_at,

        c.cuisine_name

    ORDER BY like_count DESC, r.created_at DESC

    LIMIT 7

");

$featuredRecipes = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

$featuredReelsStmt = $pdo->query("

    SELECT

        r.reel_id,

        r.title,

        r.caption,

        r.video,

        r.thumbnail,

        r.created_at,

        COUNT(DISTINCT rl.like_id) AS like_count

    FROM reels r

    LEFT JOIN reel_likes rl ON r.reel_id = rl.reel_id

    GROUP BY

        r.reel_id,

        r.title,

        r.caption,

        r.video,

        r.thumbnail,

        r.created_at

    ORDER BY like_count DESC, r.created_at DESC

    LIMIT 7

");

$featuredReels = $featuredReelsStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Culinary Trends (Trending Recipes)
|--------------------------------------------------------------------------
*/
$trendStmt = $pdo->query("
    SELECT 
        r.recipe_id,
        r.title,
        r.image,
        r.created_at,
        COUNT(DISTINCT f.favorite_id) AS like_count,
        COALESCE(AVG(rr.rating), 0) AS avg_rating,
        COUNT(DISTINCT rr.rating_id) AS rating_count
    FROM recipes r
    LEFT JOIN favorites f ON r.recipe_id = f.recipe_id
    LEFT JOIN recipe_ratings rr ON r.recipe_id = rr.recipe_id
    GROUP BY 
        r.recipe_id,
        r.title,
        r.image,
        r.created_at
    ORDER BY like_count DESC, r.created_at DESC
    LIMIT 5
");
$trendRecipes = $trendStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="home-page">

    <!-- HERO SLIDESHOW -->
    <section class="home-hero">
        <div class="hero-slides">

            <div class="hero-slide active">
                <div class="hero-overlay"></div>
                <img src="/FoodFusion-LYH/assets/images/hero1.jpg" alt="Delicious Food">
                <div class="hero-content">
                    <h1>Discover Amazing Recipes</h1>
                    <p>Explore a world of flavors, cooking ideas, and creative food inspiration.</p>
                    <div class="hero-buttons">
                        <a href="/FoodFusion-LYH/pages/recipeCollection.php" class="hero-btn primary-btn">Explore Recipes</a>
                        <a href="/FoodFusion-LYH/pages/aboutUs.php" class="hero-btn secondary-btn">About Us</a>
                    </div>
                </div>
            </div>

            <div class="hero-slide">
                <div class="hero-overlay"></div>
                <img src="/FoodFusion-LYH/assets/images/hero2.webp" alt="Cooking Together">
                <div class="hero-content">
                    <h1>Share Your Food Journey</h1>
                    <p>Post your recipes, inspire others, and connect with food lovers everywhere.</p>
                    <div class="hero-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/FoodFusion-LYH/pages/addRecipe.php" class="hero-btn primary-btn">Add Recipe</a>
                        <?php else: ?>
                            <button type="button" class="hero-btn primary-btn" onclick="openLoginModal()">Add Recipe</button>
                        <?php endif; ?>
                        <a href="/FoodFusion-LYH/pages/aboutUs.php" class="hero-btn secondary-btn">Learn More</a>
                    </div>
                </div>
            </div>

            <div class="hero-slide">
                <div class="hero-overlay"></div>
                <img src="/FoodFusion-LYH/assets/images/hero3.webp" alt="Global Cuisine">
                <div class="hero-content">
                    <h1>Taste Different Cultures</h1>
                    <p>Find recipes from many cuisines and enjoy unique culinary experiences.</p>
                    <div class="hero-buttons">
                        <a href="/FoodFusion-LYH/pages/recipeCollection.php" class="hero-btn primary-btn">Start Cooking</a>
                        <a href="/FoodFusion-LYH/pages/aboutUs.php" class="hero-btn secondary-btn">About FoodFusion</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="hero-dots">
            <span class="hero-dot active" data-slide="0"></span>
            <span class="hero-dot" data-slide="1"></span>
            <span class="hero-dot" data-slide="2"></span>
        </div>
    </section>

    <section class="home-main-section">

        <!-- FEATURED RECIPES -->
        <section class="home-section">
            <div class="section-title-center">
                <h2>Featured Recipes</h2>
                <span class="section-underline"></span>
                <p>Our most loved recipes, based on likes from the FoodFusion community.</p>
            </div>

            <div class="section-heading-action">
                <a href="/FoodFusion-LYH/pages/recipeCollection.php" class="section-link-btn">View All</a>
            </div>

            <?php if (!empty($featuredRecipes)): ?>
                <div class="featured-slider-wrap">
                    <button type="button" class="featured-slider-btn left" id="featuredPrevBtn">&lt;</button>

                    <div class="featured-slider-viewport" id="featuredSliderViewport">
                        <div class="featured-slider-track" id="featuredSliderTrack">
                            <?php foreach ($featuredRecipes as $recipe): ?>
                                <?php
                                $recipeImage = '/FoodFusion-LYH/assets/images/default-recipe.jpg';
                                if (!empty($recipe['image'])) {
                                    $dbImagePath = ltrim($recipe['image'], '/');
                                    $fullImagePath = __DIR__ . '/' . $dbImagePath;
                                    if (file_exists($fullImagePath)) {
                                        $recipeImage = '/FoodFusion-LYH/' . $dbImagePath;
                                    }
                                }
                                ?>
                                <article class="featured-recipe-card featured-slider-card">
                                    <img
                                        src="<?php echo htmlspecialchars($recipeImage); ?>"
                                        alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                        class="featured-recipe-image"
                                    >

                                    <div class="featured-recipe-body">
                                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>

                                        <p class="featured-meta">
                                            <strong>Cuisine:</strong>
                                            <?php echo htmlspecialchars($recipe['cuisine_name'] ?? 'N/A'); ?>
                                        </p>

                                        <p class="featured-meta">
                                            <strong>Cook Time:</strong>
                                            <?php echo htmlspecialchars($recipe['cook_time'] ?? 'N/A'); ?> mins
                                        </p>

                                        <div class="featured-card-actions-row">
                                            <a href="/FoodFusion-LYH/pages/recipeDetails.php?id=<?php echo (int)$recipe['recipe_id']; ?>" class="small-card-btn">
                                                View Details
                                            </a>

                                            <div class="featured-like-group">
                                                <img
                                                    src="/FoodFusion-LYH/assets/images/heart-fill.png"
                                                    alt="Likes"
                                                    class="featured-heart-icon"
                                                >
                                                <span class="featured-like-count">
                                                    <?php echo (int)($recipe['like_count'] ?? 0); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="button" class="featured-slider-btn right" id="featuredNextBtn">&gt;</button>
                </div>
            <?php else: ?>
                <div class="empty-home-box">
                    <p>No featured recipes yet.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- FEATURED REELS -->
        <section class="home-section">
            <div class="section-title-center">
                <h2>Featured Reels</h2>
                <span class="section-underline"></span>
                <p>Short food videos our community is loving the most right now.</p>
            </div>

            <div class="section-heading-action">
                <a href="/FoodFusion-LYH/pages/reels.php" class="section-link-btn">View All</a>
            </div>

            <?php if (!empty($featuredReels)): ?>
                <div class="featured-slider-wrap">
                    <button type="button" class="featured-slider-btn left" id="featuredReelPrevBtn">&lt;</button>

                    <div class="featured-slider-viewport" id="featuredReelSliderViewport">
                        <div class="featured-slider-track" id="featuredReelSliderTrack">

                            <?php foreach ($featuredReels as $reel): ?>
                                <?php
                                $reelThumb = resolveReelThumbnailPath($reel['thumbnail'] ?? '');
                                ?>

                                <article class="featured-reel-card featured-slider-card">
                                    <a href="/FoodFusion-LYH/pages/reelDetails.php?id=<?php echo (int)$reel['reel_id']; ?>" class="featured-reel-thumb-link">
                                        <?php if (!empty($reel['video'])): ?>
                                            <video class="featured-reel-image" muted loop autoplay playsinline>
                                                <source src="/FoodFusion-LYH/<?php echo htmlspecialchars($reel['video']); ?>" type="video/mp4">
                                            </video>
                                        <?php else: ?>
                                            <img src="<?php echo htmlspecialchars($reelThumb); ?>" class="featured-reel-image">
                                        <?php endif; ?>
                                        <span class="featured-reel-play">▶</span>
                                    </a>

                                    <div class="featured-reel-body">
                                        <h3><?php echo htmlspecialchars($reel['title'] ?? 'Untitled Reel'); ?></h3>

                                        <p class="featured-meta">
                                            <?php
                                            $caption = trim($reel['caption'] ?? '');
                                            echo htmlspecialchars(
                                                mb_strimwidth(
                                                    $caption !== '' ? $caption : 'Watch this trending food reel from the community.',
                                                    0,
                                                    70,
                                                    '...'
                                                )
                                            );
                                            ?>
                                        </p>

                                        <div class="featured-card-actions-row">
                                            <a href="/FoodFusion-LYH/pages/reelDetails.php?id=<?php echo (int)$reel['reel_id']; ?>" class="small-card-btn">
                                                View Reel
                                            </a>

                                            <div class="featured-like-group">
                                                <img
                                                    src="/FoodFusion-LYH/assets/images/heart-fill.png"
                                                    alt="Likes"
                                                    class="featured-heart-icon"
                                                >
                                                <span class="featured-like-count">
                                                    <?php echo (int)($reel['like_count'] ?? 0); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <button type="button" class="featured-slider-btn right" id="featuredReelNextBtn">&gt;</button>
                </div>
            <?php else: ?>
                <div class="empty-home-box">
                    <p>No featured reels yet.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- CULINARY TRENDS -->
        <section class="home-section alt-bg culinary-split-section">
            <div class="culinary-split-layout">

                <!-- LEFT CONTENT -->
                <div class="culinary-split-content">
                    <h2>Culinary Trends</h2>
                    <span class="section-underline"></span>
                    <p>
                        Culinary trends for FoodFusion reflect how modern cooking is becoming
                        more creative, healthy, and globally inspired. Today’s food culture is
                        shifting toward bold yet balanced flavors, comforting classics, and
                        exciting new combinations that bring people together.
                    </p>
                    <a href="/FoodFusion-LYH/pages/recipeCollection.php" class="culinary-collection-btn">
                        View All Recipes
                    </a>
                </div>

                <!-- RIGHT CAROUSEL -->
                <div class="culinary-split-slider">

                    <button class="culinary-arrow left" id="trendPrev">&#10094;</button>

                    <div class="culinary-card-stage">

                        <?php foreach ($trendRecipes as $recipe): ?>
                            <?php
                            $image = '/FoodFusion-LYH/assets/images/default-recipe.jpg';
                            if (!empty($recipe['image'])) {
                                $path = __DIR__ . '/' . ltrim($recipe['image'], '/');
                                if (file_exists($path)) {
                                    $image = '/FoodFusion-LYH/' . ltrim($recipe['image'], '/');
                                }
                            }

                            $rating = round($recipe['avg_rating'] ?? 0, 1);
                            ?>
                            <article class="culinary-card">
                                <div class="culinary-card-image-wrap">
                                    <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($recipe['title']); ?>" class="culinary-card-image">
                                </div>

                                <div class="culinary-card-body">
                                    <h3><?= htmlspecialchars($recipe['title']); ?></h3>

                                    <div class="culinary-stars">
                                        <?php
                                        $avgRating = round((float)($recipe['avg_rating'] ?? 0), 1);
                                        $fullStars = floor($avgRating);
                                        for ($i = 1; $i <= 5; $i++):
                                        ?>
                                            <span class="culinary-star <?php echo ($i <= $fullStars) ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>

                                    <p class="culinary-rating">
                                        <?php echo number_format($avgRating, 1); ?>
                                        (<?php echo (int)($recipe['rating_count'] ?? 0); ?>)
                                    </p>

                                    <a href="/FoodFusion-LYH/pages/recipeDetails.php?id=<?= (int)$recipe['recipe_id']; ?>" class="culinary-btn">
                                        View Recipe
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>

                    </div>

                    <button class="culinary-arrow right" id="trendNext">&#10095;</button>

                </div>
            </div>
        </section>

        <!-- UPCOMING EVENTS -->
        <section class="home-section upcoming-events-band">
            <div class="section-title-center">
                <h2>Upcoming Events</h2>
                <span class="section-underline"></span>
                <p>Exciting activities and food-themed moments to look forward to.</p>
            </div>

            <div class="events-timeline">
                <article class="timeline-event fade-up-card">
                    <div class="timeline-date">
                        <span>18</span>
                        <small>Apr</small>
                    </div>

                    <div class="timeline-line-dot"></div>

                    <div class="timeline-card">
                        <div class="timeline-card-image-wrap">
                            <img src="/FoodFusion-LYH/assets/images/event1.jpeg" alt="Community Cooking Challenge" class="timeline-card-image">
                        </div>

                        <div class="timeline-card-content">
                            <span class="timeline-tag">Community Event</span>

                            <h3>Community Cooking Challenge</h3>

                            <div class="timeline-meta-list">
                                <span>🕒 3:00 PM - 6:00 PM</span>
                                <span>🎟 Free Entry</span>
                            </div>

                            <p>
                                Show your signature homemade dish, meet other food lovers,
                                and share your creativity in a fun cooking challenge.
                            </p>

                            <a href="/FoodFusion-LYH/pages/contactUs.php" class="timeline-event-btn">
                                Join Event
                            </a>
                        </div>
                    </div>
                </article>

                <article class="timeline-event fade-up-card reverse">
                    <div class="timeline-date">
                        <span>25</span>
                        <small>Apr</small>
                    </div>

                    <div class="timeline-line-dot"></div>

                    <div class="timeline-card">
                        <div class="timeline-card-image-wrap">
                            <img src="/FoodFusion-LYH/assets/images/event2.jpg" alt="Weekend Dessert Spotlight" class="timeline-card-image">
                        </div>

                        <div class="timeline-card-content">
                            <span class="timeline-tag">Dessert Spotlight</span>

                            <h3>Weekend Dessert Spotlight</h3>

                            <div class="timeline-meta-list">
                                <span>🕒 5:00 PM - 9:00 PM</span>
                                <span>🎟 18+ • $20</span>
                            </div>

                            <p>
                                Discover beautiful desserts, sweet trends,
                                and creative recipes shared by dessert lovers.
                            </p>

                            <a href="/FoodFusion-LYH/pages/contactUs.php" class="timeline-event-btn">
                                Book Spot
                            </a>
                        </div>
                    </div>
                </article>

                <article class="timeline-event fade-up-card">
                    <div class="timeline-date">
                        <span>03</span>
                        <small>May</small>
                    </div>

                    <div class="timeline-line-dot"></div>

                    <div class="timeline-card">
                        <div class="timeline-card-image-wrap">
                            <img src="/FoodFusion-LYH/assets/images/event3.jpg" alt="Global Cuisine Week" class="timeline-card-image">
                        </div>

                        <div class="timeline-card-content">
                            <span class="timeline-tag">Global Food</span>

                            <h3>Global Cuisine Week</h3>

                            <div class="timeline-meta-list">
                                <span>🕒 12:00 PM - 8:00 PM</span>
                                <span>🎟 Free • Family Friendly</span>
                            </div>

                            <p>
                                Celebrate recipes from different cultures,
                                explore international flavors, and enjoy culinary inspiration.
                            </p>

                            <a href="/FoodFusion-LYH/pages/recipeCollection.php" class="timeline-event-btn">
                                View Recipes
                            </a>
                        </div>
                </article>
            </div>
        </section>

    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        slides.forEach((slide) => {
            slide.classList.remove('active');

            const elements = slide.querySelectorAll('h1, p, .hero-buttons');
            elements.forEach(el => {
                el.style.animation = 'none';
                el.offsetHeight;
                el.style.animation = '';
            });
        });

        dots.forEach(dot => dot.classList.remove('active'));

        slides[index].classList.add('active');
        dots[index].classList.add('active');

        currentSlide = index;
    }

    function nextSlide() {
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    function startSlider() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    dots.forEach(dot => {
        dot.addEventListener('click', function () {
            clearInterval(slideInterval);
            showSlide(parseInt(this.dataset.slide));
            startSlider();
        });
    });

    showSlide(0);
    startSlider();

    /* Featured recipe circular slider */
    const viewport = document.getElementById('featuredSliderViewport');
    const track = document.getElementById('featuredSliderTrack');
    const prevBtn = document.getElementById('featuredPrevBtn');
    const nextBtn = document.getElementById('featuredNextBtn');

    if (viewport && track && prevBtn && nextBtn) {
        const cards = Array.from(track.querySelectorAll('.featured-slider-card'));
        const totalCards = cards.length;
        let currentIndex = 0;

        function getCardStep() {
            const firstCard = cards[0];
            if (!firstCard) return 0;

            const cardWidth = firstCard.offsetWidth;
            const cardStyle = window.getComputedStyle(track);
            const gap = parseFloat(cardStyle.columnGap || cardStyle.gap || 0);

            return cardWidth + gap;
        }

        function updateSlider() {
            const step = getCardStep();
            viewport.scrollTo({
                left: currentIndex * step,
                behavior: 'smooth'
            });
        }

        nextBtn.addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % totalCards;
            updateSlider();
        });

        prevBtn.addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + totalCards) % totalCards;
            updateSlider();
        });

        window.addEventListener('resize', function () {
            updateSlider();
        });
    }

    const fadeCards = document.querySelectorAll('.fade-up-card');

    if (fadeCards.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('show-card');
                    }, index * 140);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.18
        });

        fadeCards.forEach(card => observer.observe(card));
    }

    /* ===== CULINARY SPLIT CAROUSEL ===== */
    const culinaryCards = document.querySelectorAll('.culinary-card');
    const culinaryPrev = document.getElementById('trendPrev');
    const culinaryNext = document.getElementById('trendNext');

    if (culinaryCards.length > 0 && culinaryPrev && culinaryNext) {
        let current = 1;

        function updateCulinaryCards() {
            const total = culinaryCards.length;
            const leftIndex = (current - 1 + total) % total;
            const rightIndex = (current + 1) % total;

            culinaryCards.forEach((card, i) => {
                card.classList.remove('left', 'right', 'active', 'hidden');

                if (i === current) {
                    card.classList.add('active');
                } else if (i === leftIndex) {
                    card.classList.add('left');
                } else if (i === rightIndex) {
                    card.classList.add('right');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        culinaryPrev.addEventListener('click', () => {
            current = (current - 1 + culinaryCards.length) % culinaryCards.length;
            updateCulinaryCards();
        });

        culinaryNext.addEventListener('click', () => {
            current = (current + 1) % culinaryCards.length;
            updateCulinaryCards();
        });

        updateCulinaryCards();
    }

        /* Featured reel circular slider */
    const reelViewport = document.getElementById('featuredReelSliderViewport');
    const reelTrack = document.getElementById('featuredReelSliderTrack');
    const reelPrevBtn = document.getElementById('featuredReelPrevBtn');
    const reelNextBtn = document.getElementById('featuredReelNextBtn');

    if (reelViewport && reelTrack && reelPrevBtn && reelNextBtn) {
        const reelCards = Array.from(reelTrack.querySelectorAll('.featured-slider-card'));
        const totalReelCards = reelCards.length;
        let currentReelIndex = 0;

        function getReelCardStep() {
            const firstCard = reelCards[0];
            if (!firstCard) return 0;

            const cardWidth = firstCard.offsetWidth;
            const cardStyle = window.getComputedStyle(reelTrack);
            const gap = parseFloat(cardStyle.columnGap || cardStyle.gap || 0);

            return cardWidth + gap;
        }

        function updateReelSlider() {
            const step = getReelCardStep();
            reelViewport.scrollTo({
                left: currentReelIndex * step,
                behavior: 'smooth'
            });
        }

        reelNextBtn.addEventListener('click', function () {
            currentReelIndex = (currentReelIndex + 1) % totalReelCards;
            updateReelSlider();
        });

        reelPrevBtn.addEventListener('click', function () {
            currentReelIndex = (currentReelIndex - 1 + totalReelCards) % totalReelCards;
            updateReelSlider();
        });

        window.addEventListener('resize', function () {
            updateReelSlider();
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>