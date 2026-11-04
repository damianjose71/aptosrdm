/**
 * locations.js
 * Expansión y colapso de las descripciones de las tarjetas de ubicaciones.
 */

(() => {
  "use strict";

  const toggles = document.querySelectorAll("[data-description-toggle]");

  const t = (key, fallback = "") =>
    typeof window.rdmT === "function" ? window.rdmT(key, fallback) : fallback;

  toggles.forEach((button) => {
    const description = document.getElementById(button.getAttribute("aria-controls"));
    const label = button.querySelector("[data-label]");
    const chevron = button.querySelector("[data-chevron]");

    if (!description || !label || !chevron) {
      return;
    }

    const lineHeight = Number.parseFloat(getComputedStyle(description).lineHeight);
    const collapsedHeight = lineHeight * 3;

    // Si el texto no supera las tres líneas, no hace falta mostrar el control.
    if (description.scrollHeight <= collapsedHeight + 1) {
      description.style.maxHeight = "none";
      button.hidden = true;
      return;
    }

    button.addEventListener("click", () => {
      const isExpanded = button.getAttribute("aria-expanded") === "true";

      if (isExpanded) {
        const currentHeight = description.getBoundingClientRect().height;

        description.style.maxHeight = `${currentHeight}px`;
        void description.offsetHeight;
        description.style.maxHeight = "";

        button.setAttribute("aria-expanded", "false");
        label.textContent = t("see_more", "Ver más...");
        chevron.classList.remove("rotate-180");
        return;
      }

      description.style.maxHeight = `${description.scrollHeight}px`;
      button.setAttribute("aria-expanded", "true");
      label.textContent = t("see_less", "Ver menos");
      chevron.classList.add("rotate-180");
    });

    description.addEventListener("transitionend", (event) => {
      if (event.propertyName !== "max-height") {
        return;
      }

      if (button.getAttribute("aria-expanded") === "true") {
        description.style.maxHeight = "none";
      }
    });
  });

  document.addEventListener("rdm:languagechange", () => {
    toggles.forEach((button) => {
      const label = button.querySelector("[data-label]");
      if (!label) {
        return;
      }

      label.textContent =
        button.getAttribute("aria-expanded") === "true"
          ? t("see_less", "Ver menos")
          : t("see_more", "Ver más...");
    });
  });
})();
