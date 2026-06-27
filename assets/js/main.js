// Banner Slider
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.banner-slide');
    const prevBtn = document.querySelector('.banner-nav.prev');
    const nextBtn = document.querySelector('.banner-nav.next');
    let currentSlide = 0;

    if (slides.length > 0) {
        function showSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            slides[index].classList.add('active');
        }

        if (prevBtn && nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });
            prevBtn.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });
        }

        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }

    // Countdown Timer
    function updateTimer() {
        const hours = document.getElementById('hours');
        const minutes = document.getElementById('minutes');
        const seconds = document.getElementById('seconds');
        if (!hours) return;

        let h = parseInt(hours.textContent);
        let m = parseInt(minutes.textContent);
        let s = parseInt(seconds.textContent);

        s--;
        if (s < 0) { s = 59; m--; }
        if (m < 0) { m = 59; h--; }
        if (h < 0) { h = 23; m = 59; s = 59; }

        hours.textContent = h.toString().padStart(2, '0');
        minutes.textContent = m.toString().padStart(2, '0');
        seconds.textContent = s.toString().padStart(2, '0');
    }
    setInterval(updateTimer, 1000);
});

// Mobile menu toggle
function toggleMobileMenu() {
    document.getElementById('mainNav').classList.toggle('mobile-open');
}
