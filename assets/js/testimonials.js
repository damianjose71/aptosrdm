/**
 * testimonials.js
 * Carrusel continuo de reseñas de Google Maps.
 *
 * - Duplica el grupo de tarjetas para que el desplazamiento sea infinito.
 * - Pausa la animación al pasar el cursor (CSS) y al tocar en móvil.
 * - Pausa fuera de pantalla para ahorrar batería.
 */
(() => {
  const marquee = document.querySelector('[data-reviews-marquee]');
  const track = marquee?.querySelector('[data-reviews-track]');
  const group = track?.querySelector('[data-reviews-group]');

  if (!marquee || !track || !group || track.dataset.reviewsCloned === 'true') {
    return;
  }

  // Copia visual para cerrar el bucle. Se oculta a lectores de pantalla.
  const clone = group.cloneNode(true);
  clone.setAttribute('aria-hidden', 'true');
  clone.removeAttribute('data-reviews-group');
  track.appendChild(clone);
  track.dataset.reviewsCloned = 'true';

  // La animación se activa cuando el DOM ya tiene el ancho definitivo.
  marquee.classList.add('is-ready');

  const updateMarqueeLabel = () => {
    const fallback = 'Reseñas de huéspedes en Google Maps';
    const label =
      typeof window.rdmT === 'function'
        ? window.rdmT('testimonials_marquee_label', fallback)
        : fallback;

    marquee.setAttribute('aria-label', label);
  };

  updateMarqueeLabel();
  document.addEventListener('rdm:languagechange', updateMarqueeLabel);

  const pause = () => marquee.classList.add('is-paused');
  const resume = () => marquee.classList.remove('is-paused');

  marquee.addEventListener('pointerdown', pause, { passive: true });
  marquee.addEventListener('pointerup', resume, { passive: true });
  marquee.addEventListener('pointercancel', resume, { passive: true });
  marquee.addEventListener('pointerleave', resume, { passive: true });

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          marquee.classList.toggle('is-offscreen', !entry.isIntersecting);
        });
      },
      { threshold: 0.05 }
    );

    observer.observe(marquee);
  }
})();
