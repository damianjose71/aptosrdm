(() => {
  const form = document.querySelector("[data-contact-form]");
  const status = document.querySelector("[data-form-status]");

  if (!form) {
    return;
  }

  const WHATSAPP_NUMBER = "573218839636";

  const t = (key, fallback = "") =>
    typeof window.rdmT === "function" ? window.rdmT(key, fallback) : fallback;

  const showStatus = (message) => {
    if (!status) {
      return;
    }

    status.textContent = message;
    status.classList.remove("hidden");
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const data = new FormData(form);
    const nombre = String(data.get("nombre") || "").trim();
    const telefono = String(data.get("telefono") || "").trim();
    const edificio = String(data.get("edificio") || "").trim();
    const mensaje = String(data.get("mensaje") || "").trim();

    const texto = [
      "Hola, deseo información sobre Apartamentos Reserva del Mar.",
      `Nombre: ${nombre}`,
      `Teléfono/WhatsApp: ${telefono}`,
      `Edificio de interés: ${edificio}`,
      `Mensaje: ${mensaje}`,
    ].join("\n");

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(texto)}`;
    const link = document.createElement("a");

    link.href = url;
    link.target = "_blank";
    link.rel = "noopener noreferrer";
    document.body.appendChild(link);
    link.click();
    link.remove();

    showStatus(
      t(
        "form_status",
        "Abriendo WhatsApp con tu mensaje listo para enviar."
      )
    );
  });
})();
