(() => {
  const mapElement = document.querySelector("#mapa-santa-marta");

  if (!mapElement || typeof L === "undefined" || mapElement._leaflet_id) {
    return;
  }

  // Coordenadas exactas de los mapas embebidos oficiales. Se usan para los marcadores y para map.flyTo.
  const buildings = [
    {
      id: "playa-salguero",
      name: "Playa Salguero",
      coords: [11.196500, -74.228900],
      mapsUrl: "https://maps.app.goo.gl/pB4uRWsYJX93Zz1o8",
    },
    {
      id: "rodadero-sur",
      name: "Rodadero Sur",
      coords: [11.188704, -74.233854],
      mapsUrl: "https://maps.app.goo.gl/YDTNcSqBquLtaxyN6",
    },
    {
      id: "bello-horizonte",
      name: "Bello Horizonte",
      coords: [11.151003, -74.226868],
      mapsUrl: "https://maps.app.goo.gl/pfgSGLJLkxF6J8ce7",
    },
  ];

  const map = L.map(mapElement, {
    scrollWheelZoom: false,
    zoomControl: true,
  }).setView([11.18, -74.229], 13);

  L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(map);

  const markerIcon = L.divIcon({
    className: "rdm-map-marker",
    html: '<span class="rdm-map-marker__pin" aria-hidden="true"></span>',
    iconSize: [30, 30],
    iconAnchor: [15, 15],
    popupAnchor: [0, -16],
  });

  const buttons = [...document.querySelectorAll("[data-map-target]")];
  const markers = {};

  const setActive = (id) => {
    buttons.forEach((button) => {
      button.setAttribute("aria-pressed", String(button.dataset.mapTarget === id));
    });
  };

  buildings.forEach((building) => {
    const marker = L.marker(building.coords, {
      icon: markerIcon,
      title: building.name,
      alt: `Ubicación de ${building.name}`,
      keyboard: true,
    }).addTo(map);

    marker.bindPopup(
      `<div class="rdm-map-popup">
        <p class="rdm-map-popup__eyebrow">Reserva del Mar</p>
        <p class="rdm-map-popup__title">${building.name}</p>
        <a class="rdm-map-popup__link" href="${building.mapsUrl}" target="_blank" rel="noopener noreferrer">Abrir en Google Maps <span aria-hidden="true">↗</span></a>
      </div>`,
    );

    marker.on("click", () => setActive(building.id));
    markers[building.id] = marker;
  });

  const bounds = L.latLngBounds(buildings.map((building) => building.coords));
  map.fitBounds(bounds.pad(0.35), { animate: false, maxZoom: 15 });

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const building = buildings.find((item) => item.id === button.dataset.mapTarget);

      if (!building) {
        return;
      }

      setActive(building.id);
      map.flyTo(building.coords, 17, {
        duration: 1.2,
        easeLinearity: 0.25,
      });
      map.once("moveend", () => markers[building.id].openPopup());
    });
  });

  // El contenedor puede adquirir su altura definitiva después del primer render.
  window.setTimeout(() => map.invalidateSize(), 250);
})();
