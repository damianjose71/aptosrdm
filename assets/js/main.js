/**
 * main.js
 * Interacciones globales del sitio y modal de detalle de apartamentos.
 */

(() => {
  'use strict';

  const menuButton = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');

  if (menuButton && mobileMenu) {
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

      if (
        mobileMenu.contains(event.target) ||
        menuButton.contains(event.target)
      ) {
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
  }

  // Selector de idioma: la traducción y el indicador viven en assets/js/i18n.js
  const langDropdown = document.querySelector('.lang-dropdown');
  if (langDropdown) {
    const langButton = langDropdown.querySelector('.lang-btn');
    const langOptions = langDropdown.querySelectorAll('[data-lang]');

    const closeLanguageMenu = () => {
      langDropdown.classList.remove('is-open');
      if (langButton) {
        langButton.setAttribute('aria-expanded', 'false');
      }
    };

    langButton?.addEventListener('click', (event) => {
      event.stopPropagation();
      const isOpen = langDropdown.classList.toggle('is-open');
      langButton.setAttribute('aria-expanded', String(isOpen));
    });

    langOptions.forEach((option) => {
      option.addEventListener('click', (event) => {
        event.preventDefault();
        if (typeof window.rdmSetLanguage === 'function') {
          window.rdmSetLanguage(option.dataset.lang);
        }
        closeLanguageMenu();
      });
    });

    document.addEventListener('click', (event) => {
      if (!langDropdown.contains(event.target)) {
        closeLanguageMenu();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeLanguageMenu();
      }
    });
  }

  const modal = document.getElementById('modal-apartamento');
  if (!modal) {
    return;
  }

  const modalContent = document.getElementById('modal-apartamento-contenido');
  const modalClose = document.getElementById('modal-apartamento-cerrar');
  const modalBackdrop = document.getElementById('modal-apartamento-backdrop');

  if (!modalContent || !modalClose || !modalBackdrop) {
    return;
  }

  const WHATSAPP_NUMBER = '573218839636';
  const pageRegions = [
    document.querySelector('header'),
    document.querySelector('main'),
    document.querySelector('footer'),
  ].filter(Boolean);
  let activeApartment = null;
  let lastFocusedElement = null;

  const setPageInert = (isInert) => {
    pageRegions.forEach((region) => {
      if (isInert) {
        region.setAttribute('inert', '');
      } else {
        region.removeAttribute('inert');
      }
    });
  };

  const escapeHtml = (value = '') =>
    String(value).replace(
      /[&<>"']/g,
      (character) =>
        ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;',
        })[character]
    );

  const t = (key, fallback = '') =>
    typeof window.rdmT === 'function' ? window.rdmT(key, fallback) : fallback;

  const formatDescription = (value) => {
    const text = escapeHtml(
      value || t('modal_no_description', 'Descripción no disponible.')
    );

    return text
      .replace(/^###?\s+(.+)$/gm, '<strong>$1</strong>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/`([^`]+)`/g, '<code>$1</code>')
      .replace(/(^|[^*])\*(?!\s)([^*]+?)\*(?!\*)/g, '$1<em>$2</em>')
      .replace(/^\s*[-*]\s+/gm, '• ');
  };

  const getNumber = (value) => {
    if (value === null || value === undefined || String(value).trim() === "") {
      return null;
    }

    const number = Number(value);
    return Number.isFinite(number) ? number : null;
  };

  const getGuestCount = (apartment) => {
    const guests = getNumber(
      apartment.capacidad_huespedes ?? apartment.huespedes
    );
    if (guests !== null && guests > 0) {
      return guests;
    }

    const matches = String(
      apartment.rango_huespedes || apartment.capacidad_texto || ''
    ).match(/\d+/g);

    return matches ? Math.max(...matches.map(Number)) : null;
  };

  const metricValue = (value) => (value === null ? '—' : String(value));

  const getGallery = (apartment) => {
    const gallery = Array.isArray(apartment.galeria)
      ? apartment.galeria
          .map((item) => (typeof item === 'string' ? item : item?.url))
          .filter(Boolean)
      : [];

    if (gallery.length) {
      return gallery;
    }

    return [
      apartment.imagen_principal || 'assets/images/logordm.png',
    ];
  };

  const getFeatures = (apartment) =>
    [
      ...(Array.isArray(apartment.caracteristicas)
        ? apartment.caracteristicas
        : []),
    ]
      .map((feature) => String(feature).trim())
      .filter(Boolean);

  const getBuildingAmenities = (apartment) =>
    [
      ...(Array.isArray(apartment.amenidades_edificio)
        ? apartment.amenidades_edificio
        : []),
    ]
      .map((amenity) => String(amenity).trim())
      .filter(Boolean);

  const getWhatsAppUrl = (apartment) => {
    const title = apartment.titulo || 'el apartamento seleccionado';
    const message = `Hola, me interesa cotizar el apartamento ${title}`;
    return `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
  };

  const iconGuests = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-[#C5A059]" aria-hidden="true">
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
      <path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
  `;

  const iconRooms = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-[#C5A059]" aria-hidden="true">
      <path d="M2 4v16" />
      <path d="M2 8h18a2 2 0 0 1 2 2v10" />
      <path d="M2 17h20" />
      <path d="M6 8v9" />
    </svg>
  `;

  const iconBaths = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-[#C5A059]" aria-hidden="true">
      <path d="M10 4 8 6" />
      <path d="M17 19v2" />
      <path d="M2 12h20" />
      <path d="M7 19v2" />
      <path d="M9 5 7.62 3.62A2.12 2.12 0 0 0 4 6v6" />
      <path d="M4 12v3a5 5 0 0 0 5 5h6a5 5 0 0 0 5-5v-3" />
    </svg>
  `;

  const iconBeds = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-[#C5A059]" aria-hidden="true">
      <path d="M7 8V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
      <path d="M7 8v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" />
      <path d="M9 12h6" />
    </svg>
  `;

  const metricBox = (icon, value, labelKey) => `
    <div class="flex flex-col items-center justify-center gap-1 rounded-2xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
      ${icon}
      <span class="text-lg font-bold text-[#0F172A]">${metricValue(value)}</span>
      <span data-i18n="${labelKey}" class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]">${t(
        labelKey,
        labelKey
      )}</span>
    </div>
  `;

  const modalTemplate = (apartment) => {
    const title = escapeHtml(apartment.titulo || 'Apartamento');
    const building = escapeHtml(apartment.edificio || 'Reserva del Mar');
    const location = escapeHtml(
      apartment.ubicacion || 'Santa Marta, Colombia'
    );
    const description = formatDescription(apartment.descripcion);
    const gallery = getGallery(apartment);
    const mainImage = escapeHtml(gallery[0]);
    const features = getFeatures(apartment);
    const buildingAmenities = getBuildingAmenities(apartment);
    const guests = getGuestCount(apartment);
    const rooms = getNumber(apartment.habitaciones);
    const baths = getNumber(apartment.banos);
    const beds = getNumber(apartment.camas);
    const whatsappUrl = escapeHtml(getWhatsAppUrl(apartment));

    const thumbnails =
      gallery.length > 1
        ? `
          <div class="mt-3 flex gap-2 overflow-x-auto pb-1" aria-label="Miniaturas de la galería">
            ${gallery
              .map(
                (image, index) => `
                  <button
                    type="button"
                    data-modal-imagen="${escapeHtml(image)}"
                    data-imagen-index="${index}"
                    aria-label="Mostrar imagen ${index + 1} de ${gallery.length}"
                    aria-pressed="${index === 0}"
                    class="relative h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-200 transition duration-300 ${
                      index === 0
                        ? 'ring-2 ring-[#C5A059]'
                        : 'ring-1 ring-slate-900/10 opacity-70 hover:opacity-100'
                    }"
                  >
                    <img src="${escapeHtml(image)}" alt="" class="h-full w-full object-cover" loading="lazy">
                  </button>
                `
              )
              .join('')}
          </div>
        `
        : '';

    const featuresMarkup = features.length
      ? features
          .map(
            (feature) => `
              <li class="flex items-start gap-2 text-sm leading-relaxed text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-[#C5A059]" aria-hidden="true">
                  <path d="m5 12 4 4L19 6" />
                </svg>
                <span>${escapeHtml(feature)}</span>
              </li>
            `
          )
          .join('')
      : `<li data-i18n="modal_features_empty" class="text-sm text-slate-500">${t(
          'modal_features_empty',
          'Consulta con nuestro equipo las características disponibles.'
        )}</li>`;

    const buildingAmenitiesMarkup = buildingAmenities.length
      ? `
        <div class="mt-4">
          <p data-i18n="modal_building_amenities" class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">${t(
            'modal_building_amenities',
            'Amenidades del edificio'
          )}</p>
          <div class="mt-2 flex flex-wrap gap-2">
            ${buildingAmenities
              .map(
                (amenity) => `
                  <span class="rounded-full bg-[#C5A059]/10 px-3 py-1 text-xs font-semibold text-[#0F172A]">${escapeHtml(amenity)}</span>
                `
              )
              .join('')}
          </div>
        </div>
      `
      : '';

    return `
      <div class="grid md:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        <div class="bg-slate-100 p-3 md:sticky md:top-0 md:self-start md:p-4">
          <div class="relative overflow-hidden rounded-2xl bg-slate-200">
            <img
              id="modal-imagen-principal"
              src="${mainImage}"
              alt="${title} en ${building}"
              class="h-60 w-full object-cover sm:h-72 md:h-[420px]"
              decoding="async"
            >
            ${
              gallery.length > 1
                ? `<span id="modal-galeria-contador" class="absolute bottom-4 right-4 rounded-full bg-[#0F172A]/85 px-3 py-1 text-xs font-bold text-white backdrop-blur">1 / ${gallery.length}</span>`
                : ''
            }
          </div>
          ${thumbnails}
        </div>

        <div class="p-5 sm:p-6 md:p-8">
          <p data-i18n="modal_published" class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#C5A059]">${t(
            'modal_published',
            'Publicación'
          )}</p>
          <h2 id="modal-apartamento-titulo" class="mt-2 text-2xl font-bold leading-tight text-[#0F172A] md:text-3xl">${title}</h2>

          <p class="mt-2 flex items-start gap-2 text-sm leading-relaxed text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-[#C5A059]" aria-hidden="true">
              <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span data-no-translate><strong class="font-semibold text-[#0F172A]">${building}</strong><span class="text-slate-400"> · </span>${location}</span>
          </p>

          <div class="mt-5 grid grid-cols-2 gap-1 sm:grid-cols-4">
            ${metricBox(iconGuests, guests, 'guests')}
            ${metricBox(iconRooms, rooms, 'rooms')}
            ${metricBox(iconBaths, baths, 'baths')}
            ${metricBox(iconBeds, beds, 'beds')}
          </div>

          <div class="mt-6 border-t border-slate-100 pt-5">
            <h3 data-i18n="modal_description" class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C5A059]">${t(
              'modal_description',
              'Descripción'
            )}</h3>
            <p id="modal-apartamento-descripcion" class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">${description}</p>
          </div>

          <div class="mt-6 border-t border-slate-100 pt-5">
            <h3 data-i18n="modal_features" class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C5A059]">${t(
              'modal_features',
              'Características y servicios'
            )}</h3>
            <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">${featuresMarkup}</ul>
            ${buildingAmenitiesMarkup}
          </div>

          <div class="mt-7 border-t border-slate-100 pt-5">
            <a
              href="${whatsappUrl}"
              data-i18n="quote_whatsapp"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#C5A059] px-6 py-3 text-sm font-bold text-[#0F172A] shadow-[0_16px_35px_-14px_rgba(197,160,89,0.9)] transition duration-300 hover:bg-[#D4B26D] focus:outline-none focus:ring-2 focus:ring-[#C5A059] focus:ring-offset-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z" />
              </svg>
              ${t('quote_whatsapp', 'Cotizar por WhatsApp')}
            </a>
            <p data-i18n="modal_direct" class="mt-3 text-center text-[11px] text-slate-400">${t(
              'modal_direct',
              'Atención directa del equipo Reserva del Mar'
            )}</p>
          </div>
        </div>
      </div>
    `;
  };

  const openModal = (apartment) => {
    if (!apartment) {
      return;
    }

    activeApartment = apartment;
    modalContent.innerHTML = modalTemplate(apartment);
    lastFocusedElement = document.activeElement;

    setPageInert(true);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    requestAnimationFrame(() => modalClose.focus());
  };

  const closeModal = () => {
    if (modal.classList.contains('hidden')) {
      return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    setPageInert(false);
    document.body.classList.remove('modal-open');
    modalContent.innerHTML = '';
    activeApartment = null;

    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
      lastFocusedElement.focus();
    }

    lastFocusedElement = null;
  };

  document.addEventListener('apartamento:detalle', (event) => {
    openModal(event.detail?.apartment);
  });

  modalClose.addEventListener('click', closeModal);
  modalBackdrop.addEventListener('click', closeModal);

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  modal.addEventListener('click', (event) => {
    const thumbnail = event.target.closest('[data-modal-imagen]');
    if (!thumbnail || !activeApartment) {
      return;
    }

    const gallery = getGallery(activeApartment);
    const index = Number(thumbnail.dataset.imagenIndex);
    const image = gallery[index];

    if (!image) {
      return;
    }

    const mainImage = modal.querySelector('#modal-imagen-principal');
    if (mainImage) {
      mainImage.src = image;
      mainImage.alt = `${activeApartment.titulo || 'Apartamento'} - imagen ${
        index + 1
      }`;
    }

    modal.querySelectorAll('[data-modal-imagen]').forEach((item) => {
      const isActive = item === thumbnail;
      item.setAttribute('aria-pressed', String(isActive));
      item.classList.toggle('ring-2', isActive);
      item.classList.toggle('ring-[#C5A059]', isActive);
      item.classList.toggle('ring-1', !isActive);
      item.classList.toggle('ring-slate-900/10', !isActive);
      item.classList.toggle('opacity-70', !isActive);
      item.classList.toggle('opacity-100', isActive);
    });

    const counter = modal.querySelector('#modal-galeria-contador');
    if (counter) {
      counter.textContent = `${index + 1} / ${gallery.length}`;
    }
  });

  document.addEventListener('keydown', (event) => {
    if (modal.classList.contains('hidden')) {
      return;
    }

    if (event.key === 'Escape') {
      event.preventDefault();
      closeModal();
      return;
    }

    if (event.key !== 'Tab') {
      return;
    }

    const focusables = [...modal.querySelectorAll(
      'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )].filter((element) => element.offsetParent !== null);

    if (!focusables.length) {
      return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
})();
