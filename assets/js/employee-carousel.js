document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('employees-grid');
    if (!container) return;

    let isDown = false;
    let startX;
    let scrollLeft;
    let velocity;
    let animationFrame;
    let lastScrollTime = 0;

    // Détection du support du toucher
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints;

    // Gestionnaires d'événements pour le glisser-déposer
    container.addEventListener('mousedown', handleStart);
    container.addEventListener('mousemove', handleMove);
    container.addEventListener('mouseup', handleEnd);
    container.addEventListener('mouseleave', handleEnd);

    // Gestionnaires pour le tactile
    if (isTouchDevice) {
        container.addEventListener('touchstart', handleStart, { passive: true });
        container.addEventListener('touchmove', handleMove, { passive: false });
        container.addEventListener('touchend', handleEnd, { passive: true });
    }

    // Empêcher le défilement de la page lors du swipe
    container.addEventListener('touchmove', function(e) {
        if (isDown) {
            e.preventDefault();
        }
    }, { passive: false });

    function handleStart(e) {
        isDown = true;
        startX = (e.pageX || e.touches[0].pageX) - container.offsetLeft;
        scrollLeft = container.scrollLeft;
        velocity = 0;
        lastScrollTime = Date.now();
        container.style.scrollBehavior = 'auto';
        container.style.cursor = 'grabbing';
    }

    function handleMove(e) {
        if (!isDown) return;
        e.preventDefault();
        
        const x = (e.pageX || e.touches[0].pageX) - container.offsetLeft;
        const walk = (x - startX) * 1.5; // Multiplicateur pour un défilement plus rapide
        
        // Calcul de la vélocité pour l'effet d'inertie
        const now = Date.now();
        const timeDiff = now - lastScrollTime;
        if (timeDiff > 0) {
            velocity = (container.scrollLeft - scrollLeft) / timeDiff;
        }
        lastScrollTime = now;
        scrollLeft = container.scrollLeft;
        
        container.scrollLeft = scrollLeft - walk;
    }

    function handleEnd() {
        isDown = false;
        container.style.cursor = 'grab';
        container.style.scrollBehavior = 'smooth';
        
        // Appliquer l'inertie
        if (Math.abs(velocity) > 0.1) {
            applyInertia();
        } else {
            snapToNearestCard();
        }
    }

    function applyInertia() {
        if (Math.abs(velocity) < 0.1) {
            snapToNearestCard();
            return;
        }
        
        container.scrollLeft += velocity * 20;
        velocity *= 0.95; // Facteur d'amortissement
        
        if (Math.abs(velocity) > 0.1) {
            animationFrame = requestAnimationFrame(applyInertia);
        } else {
            snapToNearestCard();
        }
    }

    function snapToNearestCard() {
        cancelAnimationFrame(animationFrame);
        
        const cards = container.querySelectorAll('.employee-card-modern');
        if (!cards.length) return;
        
        const containerRect = container.getBoundingClientRect();
        const containerCenter = containerRect.left + (containerRect.width / 2);
        
        let closestCard = null;
        let minDistance = Infinity;
        
        cards.forEach(card => {
            const cardRect = card.getBoundingClientRect();
            const cardCenter = cardRect.left + (cardRect.width / 2);
            const distance = Math.abs(cardCenter - containerCenter);
            
            if (distance < minDistance) {
                minDistance = distance;
                closestCard = card;
            }
        });
        
        if (closestCard) {
            const scrollPosition = closestCard.offsetLeft - (container.offsetWidth - closestCard.offsetWidth) / 2;
            container.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        }
    }

    // Gestion du redimensionnement de la fenêtre
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            snapToNearestCard();
        }, 250);
    });

    // Initialisation
    container.style.cursor = 'grab';
    container.style.scrollBehavior = 'smooth';
    snapToNearestCard();
});
