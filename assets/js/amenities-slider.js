/**
 * amenities-slider.js
 * Slider interno de fotografías para las tarjetas de amenidades.
 *
 * Cada tarjeta conserva su propio estado y puede navegarse con las flechas,
 * el teclado o un gesto táctil, sin afectar al resto de las tarjetas del grid.
 */
(function () {
  'use strict';

  const FALLBACK_IMAGE = 'assets/images/amenidades.jpg';
  const fallbackApplied = new WeakSet();

  function applyFallback(image) {
    if (!(image instanceof HTMLImageElement)) {
      return;
    }

    if (!image.closest('.amenity-slider')) {
      return;
    }

    if (fallbackApplied.has(image)) {
      return;
    }

    fallbackApplied.add(image);
    image.src = FALLBACK_IMAGE;
  }

  function handleImageError(event) {
    applyFallback(event.target);
  }

  function updateCounter(slider, slides, activeIndex) {
    const counter = slider.querySelector('.amenity-counter');

    if (counter) {
      counter.textContent = `${activeIndex + 1} / ${slides.length}`;
    }
  }

  function showSlide(slider, direction) {
    if (!slider) {
      return;
    }

    const slides = Array.from(slider.querySelectorAll('.amenity-img'));

    if (slides.length < 2) {
      return;
    }

    let activeIndex = slides.findIndex((slide) =>
      slide.classList.contains('active')
    );

    if (activeIndex < 0) {
      activeIndex = 0;
      slides[0].classList.add('active');
    }

    slides[activeIndex].classList.remove('active');

    const nextIndex =
      (activeIndex + direction + slides.length) % slides.length;

    slides[nextIndex].classList.add('active');
    updateCounter(slider, slides, nextIndex);
  }

  function initSlider(slider) {
    if (!slider || slider.dataset.sliderReady === 'true') {
      return;
    }

    const slides = Array.from(slider.querySelectorAll('.amenity-img'));
    const activeIndex = Math.max(
      0,
      slides.findIndex((slide) => slide.classList.contains('active'))
    );

    if (slides.length < 2) {
      slider.querySelectorAll('.slider-btn, .amenity-counter').forEach((item) => {
        item.hidden = true;
      });
      slider.dataset.sliderReady = 'true';
      return;
    }

    updateCounter(slider, slides, activeIndex);

    let touchStartX = null;

    slider.addEventListener(
      'touchstart',
      (event) => {
        if (event.touches.length === 1) {
          touchStartX = event.touches[0].clientX;
        }
      },
      { passive: true }
    );

    slider.addEventListener(
      'touchend',
      (event) => {
        if (touchStartX === null) {
          return;
        }

        const endX = event.changedTouches[0]?.clientX ?? touchStartX;
        const delta = endX - touchStartX;
        touchStartX = null;

        if (Math.abs(delta) < 40) {
          return;
        }

        showSlide(slider, delta < 0 ? 1 : -1);
      },
      { passive: true }
    );

    slider.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        showSlide(slider, -1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        showSlide(slider, 1);
      }
    });

    slider.dataset.sliderReady = 'true';
  }

  window.nextSlide = function nextSlide(button) {
    const slider = button?.closest?.('.amenity-slider');
    showSlide(slider, 1);
  };

  window.prevSlide = function prevSlide(button) {
    const slider = button?.closest?.('.amenity-slider');
    showSlide(slider, -1);
  };

  document.addEventListener('error', handleImageError, true);

  function init() {
    document.querySelectorAll('.amenity-slider').forEach(initSlider);
    document.querySelectorAll('.amenity-slider img').forEach((image) => {
      if (image.complete && image.naturalWidth === 0) {
        applyFallback(image);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
