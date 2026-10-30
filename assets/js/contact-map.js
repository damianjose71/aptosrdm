(() => {
  const map = document.querySelector("#mapa-edificio");
  const mapLink = document.querySelector("#mapa-enlace");
  const buttons = [...document.querySelectorAll("[data-map-target]")];

  if (!map || !buttons.length) {
    return;
  }

  const locations = {
    "playa-salguero": {
      src: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3914.542!2d-74.2289!3d11.1965!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTHCsDExJzg3LjQiTiA3NMKwMTMnNDQuMCJX!5e0!3m2!1ses!2sco!4v1",
      title: "Mapa de Playa Salguero - Reserva del Mar",
      link: "https://maps.app.goo.gl/pB4uRWsYJX93Zz1o8",
    },
    "rodadero-sur": {
      src: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5884.71135051178!2d-74.23385353970221!3d11.18870414324373!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef45f00196d1a0d%3A0x401567a054029f0c!2sReserva%20del%20mar%20II!5e0!3m2!1ses-419!2sco!4v1793371166202!5m2!1ses-419!2sco",
      title: "Mapa de Rodadero Sur - Reserva del Mar II",
      link: "https://maps.app.goo.gl/YDTNcSqBquLtaxyN6",
    },
    "bello-horizonte": {
      src: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1611.9709680663634!2d-74.22686807520296!3d11.151002981969732!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef4592d6012340f%3A0x97a9560135c3bd44!2sReserva%20del%20Mar%20Luxury%20Apartasuites%20Bello%20Horizonte!5e0!3m2!1ses-419!2sco!4v1793371114112!5m2!1ses-419!2sco",
      title: "Mapa de Bello Horizonte - Reserva del Mar Luxury Apartasuites",
      link: "https://maps.app.goo.gl/pfgSGLJLkxF6J8ce7",
    },
  };

  const setActive = (id) => {
    buttons.forEach((button) => {
      button.setAttribute("aria-pressed", String(button.dataset.mapTarget === id));
    });
  };

  const updateMap = (id) => {
    const location = locations[id];

    if (!location) {
      return;
    }

    if (map.getAttribute("src") !== location.src) {
      map.setAttribute("src", location.src);
    }

    map.title = location.title;

    if (mapLink) {
      mapLink.href = location.link;
    }

    setActive(id);
  };

  buttons.forEach((button) => {
    button.addEventListener("click", () => updateMap(button.dataset.mapTarget));
  });

  // Playa Salguero queda seleccionado y visible al cargar la página.
  updateMap("playa-salguero");
})();
