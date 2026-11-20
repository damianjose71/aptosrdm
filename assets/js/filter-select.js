/**
 * filter-select.js
 * Mejora progresiva de los selects del catálogo. Conserva el select nativo
 * para el envío del formulario y muestra una lista con el estilo del sitio.
 */

(() => {
  "use strict";

  const SELECTOR = "[data-rdm-select]";

  const initSelect = (wrapper) => {
    if (!wrapper || wrapper.dataset.rdmSelectReady === "true") {
      return;
    }

    const select = wrapper.querySelector("select");
    const trigger = wrapper.querySelector("[data-rdm-select-trigger]");
    const panel = wrapper.querySelector("[data-rdm-select-panel]");
    const valueElement = wrapper.querySelector("[data-rdm-select-value]");

    if (!select || !trigger || !panel || !valueElement) {
      return;
    }

    wrapper.dataset.rdmSelectReady = "true";
    trigger.setAttribute("aria-controls", panel.id);

    const syncValue = () => {
      const selectedOption = select.options[select.selectedIndex] || null;
      valueElement.textContent = selectedOption
        ? selectedOption.textContent.trim()
        : "";

      panel.querySelectorAll('[role="option"]').forEach((option) => {
        option.setAttribute(
          "aria-selected",
          option.dataset.value === select.value ? "true" : "false"
        );
      });
    };

    const renderOptions = () => {
      panel.innerHTML = "";

      Array.from(select.options).forEach((option) => {
        if (option.disabled) {
          return;
        }

        const item = document.createElement("button");
        item.type = "button";
        item.className = "rdm-select__option";
        item.dataset.value = option.value;
        item.setAttribute("role", "option");
        item.setAttribute("aria-selected", "false");
        item.tabIndex = -1;
        item.textContent = option.textContent.trim();
        item.addEventListener("click", () => choose(option.value));
        panel.append(item);
      });

      syncValue();
    };

    const close = (returnFocus = false) => {
      panel.hidden = true;
      wrapper.classList.remove("is-open");
      trigger.setAttribute("aria-expanded", "false");

      if (returnFocus) {
        trigger.focus({ preventScroll: true });
      }
    };

    const focusOption = (option) => {
      if (!option) {
        return;
      }

      option.focus({ preventScroll: true });

      const optionTop = option.offsetTop;
      const optionBottom = optionTop + option.offsetHeight;
      if (optionTop < panel.scrollTop) {
        panel.scrollTop = optionTop;
      } else if (optionBottom > panel.scrollTop + panel.clientHeight) {
        panel.scrollTop = optionBottom - panel.clientHeight;
      }
    };

    const closeOthers = () => {
      document.querySelectorAll(`${SELECTOR}.is-open`).forEach((other) => {
        if (other === wrapper) {
          return;
        }

        other.classList.remove("is-open");
        const otherPanel = other.querySelector("[data-rdm-select-panel]");
        const otherTrigger = other.querySelector("[data-rdm-select-trigger]");
        if (otherPanel) {
          otherPanel.hidden = true;
        }
        if (otherTrigger) {
          otherTrigger.setAttribute("aria-expanded", "false");
        }
      });
    };

    const open = () => {
      if (!panel.hidden) {
        return;
      }

      renderOptions();
      closeOthers();
      panel.hidden = false;
      wrapper.classList.add("is-open");
      trigger.setAttribute("aria-expanded", "true");

      const selected =
        panel.querySelector('[role="option"][aria-selected="true"]') ||
        panel.querySelector('[role="option"]');
      focusOption(selected);
    };

    const choose = (value) => {
      const changed = select.value !== value;
      select.value = value;
      syncValue();
      close(true);

      if (changed) {
        select.dispatchEvent(new Event("change", { bubbles: true }));
      }
    };

    trigger.addEventListener("click", () => {
      if (panel.hidden) {
        open();
      } else {
        close(true);
      }
    });

    trigger.addEventListener("keydown", (event) => {
      if (["ArrowDown", "ArrowUp", "Enter", " "].includes(event.key)) {
        event.preventDefault();
        open();
      } else if (event.key === "Escape") {
        event.preventDefault();
        close();
      }
    });

    panel.addEventListener("keydown", (event) => {
      const options = Array.from(panel.querySelectorAll('[role="option"]'));
      const currentIndex = options.indexOf(document.activeElement);

      if (event.key === "ArrowDown") {
        event.preventDefault();
        options[Math.min(currentIndex + 1, options.length - 1)]?.focus();
      } else if (event.key === "ArrowUp") {
        event.preventDefault();
        options[Math.max(currentIndex - 1, 0)]?.focus();
      } else if (event.key === "Home") {
        event.preventDefault();
        options[0]?.focus();
      } else if (event.key === "End") {
        event.preventDefault();
        options[options.length - 1]?.focus();
      } else if (event.key === "Escape") {
        event.preventDefault();
        close(true);
      } else if (event.key === "Tab") {
        window.setTimeout(() => close(), 0);
      }
    });

    wrapper.addEventListener("focusout", (event) => {
      if (!wrapper.contains(event.relatedTarget)) {
        close();
      }
    });

    select.addEventListener("change", syncValue);
    select.addEventListener("rdm:select-sync", syncValue);

    const observer = new MutationObserver(() => {
      const wasOpen = !panel.hidden;
      renderOptions();

      if (wasOpen) {
        const selected =
          panel.querySelector('[role="option"][aria-selected="true"]') ||
          panel.querySelector('[role="option"]');
        focusOption(selected);
      }
    });
    observer.observe(select, {
      childList: true,
      subtree: true,
      characterData: true,
    });

    document.addEventListener("rdm:languagechange", renderOptions);

    select.setAttribute("tabindex", "-1");
    select.setAttribute("aria-hidden", "true");
    wrapper.classList.add("rdm-select--enhanced");
    trigger.hidden = false;
    valueElement.hidden = false;
    renderOptions();
  };

  const initAll = () => {
    document.querySelectorAll(SELECTOR).forEach(initSelect);
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll, { once: true });
  } else {
    initAll();
  }

  document.addEventListener("click", (event) => {
    document.querySelectorAll(`${SELECTOR}.is-open`).forEach((wrapper) => {
      if (wrapper.contains(event.target)) {
        return;
      }

      wrapper.classList.remove("is-open");
      const panel = wrapper.querySelector("[data-rdm-select-panel]");
      const trigger = wrapper.querySelector("[data-rdm-select-trigger]");
      if (panel) {
        panel.hidden = true;
      }
      if (trigger) {
        trigger.setAttribute("aria-expanded", "false");
      }
    });
  });
})();
