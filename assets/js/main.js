/* JS Logic */
document.addEventListener('DOMContentLoaded', () => {
    console.log("Renta Enterprise UI initialized");
    
    // Toggle mobile menu / dropdown
    const catBtn = document.querySelector('.categories-btn');
    const catDropdown = document.querySelector('.categories-dropdown');
    
    if(catBtn && catDropdown) {
        catBtn.addEventListener('click', (e) => {
            // Note: on desktop homepage, this should remain always visible visually by design
            // but for mobile or other pages we will toggle it.
            // On desktop homepage, the CSS '.categories-dropdown' is .active by default.
            
            // For now, toggle capability (useful on mobile view):
            if (window.innerWidth <= 991) {
                catDropdown.classList.toggle('active');
            }
        });
    }

    // Slider Logic for multiple sliders
    const sliderContainers = document.querySelectorAll('.slider-container');
    
    sliderContainers.forEach(container => {
        const sliderWrapper = container.querySelector('.slider-wrapper');
        const slides = container.querySelectorAll('.slide');
        const prevBtn = container.querySelector('.prev-btn');
        const nextBtn = container.querySelector('.next-btn');
        const dotsContainer = container.querySelector('.slider-dots');
        
        if (sliderWrapper && slides.length > 0) {
            let currentSlide = 0;
            const totalSlides = slides.length;

            if (dotsContainer) {
                // Generate dots if there are more than 1 slide and we haven't manually added them all
                if (totalSlides > 1 && dotsContainer.children.length !== totalSlides) {
                    dotsContainer.innerHTML = '';
                    for (let i = 0; i < totalSlides; i++) {
                        const dot = document.createElement('span');
                        dot.classList.add('dot');
                        if (i === 0) dot.classList.add('active');
                        dot.dataset.slide = i;
                        dotsContainer.appendChild(dot);
                    }
                }

                const dots = container.querySelectorAll('.dot');

                function updateSlider() {
                    sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
                    
                    // Update dots
                    dots.forEach(dot => dot.classList.remove('active'));
                    if(dots[currentSlide]) {
                        dots[currentSlide].classList.add('active');
                    }
                }

                function nextSlide() {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    updateSlider();
                }

                function prevSlide() {
                    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                    updateSlider();
                }

                if (nextBtn) nextBtn.addEventListener('click', nextSlide);
                if (prevBtn) prevBtn.addEventListener('click', prevSlide);

                dots.forEach(dot => {
                    dot.addEventListener('click', (e) => {
                        currentSlide = parseInt(e.target.dataset.slide);
                        updateSlider();
                    });
                });
            }
        }
    });
});
