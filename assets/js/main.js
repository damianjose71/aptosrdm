/**
 * main.js
 * Interacciones globales del sitio.
 */

(() => {
  'use strict';

  const menuButton = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');

  if (!menuButton || !mobileMenu) {
    return;
  }

  const openIcon = menuButton.querySelector('[data-menu-icon="open"]');
  const closeIcon = menuButton.querySelector('[data-menu-icon="close"]');

  const setMenuState = (isOpen, returnFocus = false) => {
    mobileMenu.classList.toggle('hidden', !isOpen);
    mobileMenu.setAttribute('aria-hidden', String(!isOpen));
    menuButton.setAttribute('aria-expanded', String(isOpen));
    menuButton.setAttribute(
      'aria-label',
      isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación'
    );

    openIcon?.classList.toggle('hidden', isOpen);
    closeIcon?.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);

    if (!isOpen && returnFocus) {
      menuButton.focus();
    }
  };

  menuButton.addEventListener('click', () => {
    setMenuState(mobileMenu.classList.contains('hidden'));
  });

  mobileMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenuState(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
      setMenuState(false, true);
    }
  });

  document.addEventListener('click', (event) => {
    if (mobileMenu.classList.contains('hidden')) {
      return;
    }

    if (mobileMenu.contains(event.target) || menuButton.contains(event.target)) {
      return;
    }

    setMenuState(false);
  });

  const desktopQuery = window.matchMedia('(min-width: 768px)');
  const closeMenuOnDesktop = (event) => {
    if (event.matches) {
      setMenuState(false);
    }
  };

  if (typeof desktopQuery.addEventListener === 'function') {
    desktopQuery.addEventListener('change', closeMenuOnDesktop);
  } else {
    desktopQuery.addListener(closeMenuOnDesktop);
  }
})();
