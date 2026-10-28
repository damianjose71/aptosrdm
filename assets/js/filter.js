/**
 * filter.js
 * Catálogo de apartamentos: filtros combinados y tarjetas resumidas.
 */

(() => {
  "use strict";

  const CATALOG_URL = "data/apartamentos.json";

  const catalog = document.getElementById("catalogo-apartamentos");
  const filterForm = document.getElementById("filtro-catalogo");
  const locationFilter = document.getElementById("filtro-ubicacion");
  const rangeFilter = document.getElementById("filtro-huespedes");
  const count = document.getElementById("catalogo-conteo");
  const emptyState = document.getElementById("catalogo-vacio");

  if (!catalog || !filterForm || !locationFilter || !rangeFilter) {
    return;
  }

  let apartments = [];
  let buildings = [];

  const escapeHtml = (value = "") =>
    String(value).replace(
      /[&<>"']/g,
      (character) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[character]
    );

  const normalizeText = (value = "") =>
    String(value).trim().toLocaleLowerCase("es");

  const getRangeStart = (value) => {
    const match = String(value).match(/\d+/);
    return match ? Number(match[0]) : Number.POSITIVE_INFINITY;
  };

  const getNumber = (value) => {
    if (value === null || value === undefined || String(value).trim() === "") {
      return null;
    }

    const number = Number(value);
    return Number.isFinite(number) ? number : null;
  };

  const getGuestCount = (apartment) => {
    const guests = getNumber(apartment.huespedes);
    if (guests !== null && guests > 0) {
      return guests;
    }

    const matches = String(
      apartment.rango_huespedes || apartment.capacidad_texto || ""
    ).match(/\d+/g);

    return matches ? Math.max(...matches.map(Number)) : null;
  };

  const metricValue = (value) => (value === null ? "—" : String(value));

  const getBadge = (apartment) => {
    if (apartment.zona) {
      return apartment.zona;
    }

    const location = apartment.ubicacion || "";
    return location.split(",")[0].trim() || apartment.edificio || "Reserva del Mar";
  };

  const updateCount = (total) => {
    if (!count) {
      return;
    }

    count.textContent =
      total === 1
        ? "1 apartamento disponible"
        : `${total} apartamentos disponibles`;
  };

  const iconGuests = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
      <path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
  `;

  const iconRooms = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
      <path d="M2 4v16" />
      <path d="M2 8h18a2 2 0 0 1 2 2v10" />
      <path d="M2 17h20" />
      <path d="M6 8v9" />
    </svg>
  `;

  const iconBaths = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
      <path d="M10 4 8 6" />
      <path d="M17 19v2" />
      <path d="M2 12h20" />
      <path d="M7 19v2" />
      <path d="M9 5 7.62 3.62A2.12 2.12 0 0 0 4 6v6" />
      <path d="M4 12v3a5 5 0 0 0 5 5h6a5 5 0 0 0 5-5v-3" />
    </svg>
  `;

  const metricBox = (value, label, icon) => `
    <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
      ${icon}
      <span class="text-sm font-bold text-[#0F172A]">${metricValue(value)}</span>
      <span class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]">${label}</span>
    </div>
  `;

  const cardTemplate = (apartment) => {
    const id = escapeHtml(apartment.id || "");
    const title = escapeHtml(apartment.titulo || "Apartamento");
    const building = escapeHtml(apartment.edificio || "Reserva del Mar");
    const image = escapeHtml(
      apartment.imagen_principal || "assets/images/logo-placeholder.svg"
    );
    const badge = escapeHtml(getBadge(apartment));
    const guests = getGuestCount(apartment);
    const rooms = getNumber(apartment.habitaciones);
    const baths = getNumber(apartment.banos);

    return `
      <article class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-[0_18px_45px_-22px_rgba(15,23,42,0.35)] ring-1 ring-slate-900/5 transition duration-300 hover:-translate-y-1.5 hover:shadow-[0_28px_60px_-22px_rgba(15,23,42,0.45)]">
        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
          <img
            src="${image}"
            alt="${title} en ${building}"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-cover object-[center_30%] transition duration-500 group-hover:scale-[1.04]"
          >
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0F172A]/35 via-transparent to-transparent"></div>
          <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-[#0F172A]/90 px-3 py-1.5 text-xs font-bold text-white shadow-sm backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 text-[#C5A059]" aria-hidden="true">
              <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            ${badge}
          </span>
        </div>

        <div class="flex flex-1 flex-col p-6">
          <h3 class="min-h-[3.5rem] text-xl font-bold leading-snug text-[#0F172A]">${title}</h3>
          <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">${building}</p>

          <div class="mt-4 grid grid-cols-3 gap-2">
            ${metricBox(guests, "Huéspedes", iconGuests)}
            ${metricBox(rooms, "Habitaciones", iconRooms)}
            ${metricBox(baths, "Baños", iconBaths)}
          </div>

          <div class="mt-auto pt-6">
            <button
              type="button"
              data-detalle-id="${id}"
              aria-label="Ver detalles de ${title} en ${building}"
              class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#0F172A] px-5 py-3 text-sm font-bold text-white transition duration-300 hover:bg-[#1E293B] focus:outline-none focus:ring-2 focus:ring-[#C5A059] focus:ring-offset-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              Ver Detalles
            </button>
          </div>
        </div>
      </article>
    `;
  };

  const renderCards = (items) => {
    catalog.setAttribute("aria-busy", "false");

    if (!items.length) {
      catalog.innerHTML = "";
      updateCount(0);

      if (emptyState) {
        emptyState.textContent =
          "No hay apartamentos disponibles para la ubicación y el rango de huéspedes seleccionados.";
        emptyState.classList.remove("hidden");
      }
      return;
    }

    if (emptyState) {
      emptyState.classList.add("hidden");
    }

    catalog.innerHTML = items.map(cardTemplate).join("");
    updateCount(items.length);
  };

  const populateFilter = () => {
    const ranges = [
      ...new Set(
        apartments.map((item) => item.rango_huespedes).filter(Boolean)
      ),
    ].sort((first, second) => getRangeStart(first) - getRangeStart(second));

    rangeFilter.innerHTML = [
      '<option value="">Todos los rangos</option>',
      ...ranges.map(
        (range) =>
          `<option value="${escapeHtml(range)}">${escapeHtml(range)}</option>`
      ),
    ].join("");
  };

  const applyFilters = () => {
    const selectedLocation = normalizeText(locationFilter.value);
    const selectedRange = rangeFilter.value;

    const filtered = apartments.filter((apartment) => {
      const locationMatches =
        !selectedLocation ||
        [apartment.ubicacion, apartment.edificio, apartment.zona]
          .filter(Boolean)
          .some((value) => normalizeText(value).includes(selectedLocation));

      const rangeMatches =
        !selectedRange || apartment.rango_huespedes === selectedRange;

      return locationMatches && rangeMatches;
    });

    renderCards(filtered);
  };

  const showError = () => {
    catalog.innerHTML = "";
    catalog.setAttribute("aria-busy", "false");

    if (count) {
      count.textContent = "Catálogo no disponible";
    }

    if (emptyState) {
      emptyState.textContent =
        "No fue posible cargar los apartamentos. Intenta nuevamente más tarde.";
      emptyState.classList.remove("hidden");
    }
  };

  const loadCatalog = async () => {
    try {
      const response = await fetch(CATALOG_URL, {
        headers: { Accept: "application/json" },
      });

      if (!response.ok) {
        throw new Error(`No se pudo cargar el catálogo (${response.status}).`);
      }

      const data = await response.json();
      apartments = Array.isArray(data.apartamentos) ? data.apartamentos : [];
      buildings = Array.isArray(data.edificios) ? data.edificios : [];

      populateFilter();
      applyFilters();
    } catch (error) {
      console.error(error);
      showError();
    }
  };

  catalog.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-detalle-id]");
    if (!trigger) {
      return;
    }

    const apartment = apartments.find(
      (item) => String(item.id) === trigger.dataset.detalleId
    );

    if (!apartment) {
      return;
    }

    const building = buildings.find(
      (item) => String(item.nombre) === String(apartment.edificio)
    );

    document.dispatchEvent(
      new CustomEvent("apartamento:detalle", {
        detail: {
          apartment: {
            ...apartment,
            amenidades_edificio: Array.isArray(building?.amenidades)
              ? building.amenidades
              : [],
          },
        },
      })
    );
  });

  filterForm.addEventListener("submit", (event) => {
    event.preventDefault();
    applyFilters();
  });

  loadCatalog();
})();
