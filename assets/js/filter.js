/**
 * filter.js
 * Catálogo de apartamentos y filtrado por rango de huéspedes.
 */

(() => {
  "use strict";

  const CATALOG_URL = "data/apartamentos.json";
  const WHATSAPP_NUMBER = "573218839636";

  const catalog = document.getElementById("catalogo-apartamentos");
  const filter = document.getElementById("filtro-huespedes");
  const count = document.getElementById("catalogo-conteo");
  const emptyState = document.getElementById("catalogo-vacio");

  if (!catalog || !filter) {
    return;
  }

  let apartments = [];

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

  const getRangeStart = (value) => {
    const match = String(value).match(/\d+/);
    return match ? Number(match[0]) : Number.POSITIVE_INFINITY;
  };

  const getBadge = (apartment) => {
    if (apartment.zona) {
      return apartment.zona;
    }

    const location = apartment.ubicacion || "";
    return location.split(",")[0].trim() || apartment.edificio || "Reserva del Mar";
  };

  const getWhatsAppUrl = (apartment) => {
    const title = apartment.titulo || "el apartamento";
    const building = apartment.edificio || "Reserva del Mar";
    const location = apartment.ubicacion || "Santa Marta";
    const message = `Hola, me interesa ${title} en ${building} (${location}). ¿Podrían enviarme información y cotización?`;
    return `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
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

  const cardTemplate = (apartment) => {
    const title = escapeHtml(apartment.titulo || "Apartamento");
    const building = escapeHtml(apartment.edificio || "Reserva del Mar");
    const range = escapeHtml(
      apartment.rango_huespedes ||
        apartment.capacidad_texto ||
        "Capacidad por confirmar"
    );
    const description = escapeHtml(apartment.descripcion || "");
    const image = escapeHtml(
      apartment.imagen_principal || "assets/images/logo-placeholder.svg"
    );
    const badge = escapeHtml(getBadge(apartment));
    const whatsappUrl = escapeHtml(getWhatsAppUrl(apartment));

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
          <p class="mt-2 flex items-center gap-1.5 text-sm font-semibold text-[#C5A059]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            ${range}
          </p>
          <p class="mt-3 text-sm leading-relaxed text-slate-600">${description}</p>
          <div class="mt-auto pt-6">
            <a
              href="${whatsappUrl}"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Reservar o cotizar ${title} en ${building}"
              class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-full bg-[#C5A059] px-5 py-3 text-sm font-bold text-[#0F172A] transition-colors duration-300 hover:bg-[#D4B26D]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z" />
              </svg>
              Reservar / Cotizar
            </a>
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
          "No hay apartamentos disponibles para este rango de huéspedes.";
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
    const ranges = [...new Set(apartments.map((item) => item.rango_huespedes).filter(Boolean))]
      .sort((first, second) => getRangeStart(first) - getRangeStart(second));

    filter.innerHTML = [
      '<option value="">Todos los rangos</option>',
      ...ranges.map(
        (range) =>
          `<option value="${escapeHtml(range)}">${escapeHtml(range)}</option>`
      ),
    ].join("");
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

      populateFilter();
      renderCards(apartments);
    } catch (error) {
      console.error(error);
      showError();
    }
  };

  filter.addEventListener("change", () => {
    const selectedRange = filter.value;
    const filtered = selectedRange
      ? apartments.filter(
          (apartment) => apartment.rango_huespedes === selectedRange
        )
      : apartments;

    renderCards(filtered);
  });

  loadCatalog();
})();
