<script>
    (function () {
        const slider = document.querySelector('[data-home-slider]');
        if (!slider) {
            return;
        }

        const slides = Array.from(slider.querySelectorAll('[data-slide]'));
        if (slides.length === 0) {
            return;
        }

        const indicators = Array.from(slider.querySelectorAll('[data-indicator]'));

        let activeIndex = 0;
        let timerId = null;

        const setActive = (index) => {
            activeIndex = index;
            slides.forEach((slide, idx) => {
                const isActive = idx === index;
                slide.classList.toggle('opacity-100', isActive);
                slide.classList.toggle('opacity-0', !isActive);
                slide.classList.toggle('pointer-events-none', !isActive);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });

            indicators.forEach((dot, idx) => {
                dot.classList.toggle('bg-white', idx === index);
                dot.classList.toggle('bg-white/40', idx !== index);
            });
        };

        const next = () => {
            setActive((activeIndex + 1) % slides.length);
        };

        const stop = () => {
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        const start = () => {
            if (slides.length < 2) {
                return;
            }

            stop();
            timerId = setInterval(next, 6000);
        };

        indicators.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                setActive(idx);
                start();
            });
        });

        slider.addEventListener('click', function (event) {
            if (event.target.closest('[data-indicator]')) {
                return;
            }

            next();
            start();
        });

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);

        setActive(0);
        start();
    })();
</script>
