(() => {
    const navbar = document.querySelector('.topbar');
    if (!navbar) return;

    let previousY = window.scrollY;
    let scrollFrame = false;

    const updateNavbar = () => {
        const currentY = window.scrollY;
        document.body.classList.toggle('has-scrolled', currentY > 30);

        if (currentY < 30) {
            navbar.classList.remove('navbar-hidden', 'navbar-returning');
        } else if (currentY > previousY + 5) {
            navbar.classList.add('navbar-hidden');
            navbar.classList.remove('navbar-returning');
        } else if (currentY < previousY - 5) {
            navbar.classList.remove('navbar-hidden');
            navbar.classList.add('navbar-returning');
        }

        previousY = currentY;
        scrollFrame = false;
    };

    window.addEventListener('scroll', () => {
        if (scrollFrame) return;
        scrollFrame = true;
        window.requestAnimationFrame(updateNavbar);
    }, { passive: true });

    const hero = document.querySelector('.hero-catering');
    const menu = document.querySelector('.market-section');
    if (!hero || !menu) return;

    let transitionStarted = false;
    window.addEventListener('wheel', (event) => {
        if (event.ctrlKey || event.deltaY <= 8 || transitionStarted) return;

        const heroEnd = hero.offsetTop + hero.offsetHeight;
        if (window.scrollY >= heroEnd - 80) return;

        event.preventDefault();
        transitionStarted = true;
        window.scrollTo({ top: menu.offsetTop, behavior: 'smooth' });
        window.setTimeout(() => { transitionStarted = false; }, 900);
    }, { passive: false });
})();