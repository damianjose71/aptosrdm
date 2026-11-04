/**
 * auto-translate.js
 * Traducción automática ES→EN del contenido editable.
 *
 * - Traduce los elementos marcados con data-auto-translate (hojas de texto)
 *   y todo el contenido dentro de data-auto-translate-scope.
 * - Traduce también el contenido que se crea después (tarjetas de
 *   apartamentos, modal, nuevas amenidades) mediante MutationObserver.
 * - Usa el endpoint público de Google Translate con respaldo en MyMemory.
 * - Guarda las traducciones en localStorage para no repetir peticiones.
 * - Si no hay red o ambos servicios fallan, deja el texto original en español.
 *
 * Para excluir un texto (marcas, nombres propios) usar data-no-translate.
 * Para usar una traducción manual fija, usar data-i18n.
 */
(function () {
  'use strict';

  const GOOGLE_ENDPOINT =
    'https://translate.googleapis.com/translate_a/single';
  const MYMEMORY_ENDPOINT = 'https://api.mymemory.translated.net/get';
  const SENTINEL = '[[RDM]]';
  const CACHE_PREFIX = 'rdm-tr-v3:';
  const MAX_CHUNK = 1200;
  const CHUNK_DELAY = 120;

  // Nombres propios que no deben traducirse dentro de un texto más largo.
  // Se pueden ampliar desde cualquier página con window.rdmProtectedTerms.
  const DEFAULT_PROTECTED_TERMS = [
    'Reserva del Mar Bello Horizonte',
    'Reserva del Mar II',
    'Reserva del Mar',
    'Playa Salguero',
    'Rodadero Sur',
    'Bello Horizonte',
    'Santa Marta'
  ];

  const memoryCache = new Map();
  const originals = new Map(); // Nodo -> texto original en español
  const inFlight = new Map();
  let currentLang = 'es';

  const getProtectedTerms = () =>
    Array.isArray(window.rdmProtectedTerms) && window.rdmProtectedTerms.length
      ? window.rdmProtectedTerms
      : DEFAULT_PROTECTED_TERMS;

  const protectTerms = (text) => {
    let protectedText = text;
    const replacements = [];

    getProtectedTerms().forEach((term, index) => {
      if (!term || !protectedText.includes(term)) {
        return;
      }

      const token = `[[RDMT${index}]]`;
      protectedText = protectedText.split(term).join(token);
      replacements.push({ token, term });
    });

    return { text: protectedText, replacements };
  };

  const restoreTerms = (text, replacements) => {
    let restored = text;

    replacements.forEach(({ token, term }) => {
      restored = restored.split(token).join(term);
    });

    return restored;
  };

  const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

  const getLanguage = () => {
    if (typeof window.rdmGetLanguage === 'function') {
      return window.rdmGetLanguage() === 'en' ? 'en' : 'es';
    }
    return document.documentElement.lang === 'en' ? 'en' : 'es';
  };

  const hash = (text) => {
    let value = 5381;
    for (let index = 0; index < text.length; index += 1) {
      value = ((value << 5) + value) ^ text.charCodeAt(index);
    }
    return (value >>> 0).toString(36);
  };

  const cacheKey = (text, lang) =>
    `${CACHE_PREFIX}${lang}:${hash(text)}:${text.length}`;

  const readCache = (text, lang) => {
    const key = cacheKey(text, lang);
    if (memoryCache.has(key)) {
      return memoryCache.get(key);
    }

    try {
      const stored = window.localStorage.getItem(key);
      if (stored) {
        memoryCache.set(key, stored);
        return stored;
      }
    } catch (error) {
      /* localStorage puede estar bloqueado */
    }

    return null;
  };

  const writeCache = (text, lang, translated) => {
    const key = cacheKey(text, lang);
    memoryCache.set(key, translated);
    try {
      window.localStorage.setItem(key, translated);
    } catch (error) {
      /* cuota llena o almacenamiento bloqueado */
    }
  };

  const parseGoogle = (payload) => {
    const segments = Array.isArray(payload?.[0]) ? payload[0] : [];
    const text = segments
      .map((segment) => (Array.isArray(segment) ? segment[0] : ''))
      .join('');
    return text.trim() ? text : null;
  };

  const requestGoogle = async (text, lang) => {
    const url = `${GOOGLE_ENDPOINT}?client=gtx&sl=es&tl=${lang}&dt=t&q=${encodeURIComponent(
      text
    )}`;
    const response = await fetch(url, { mode: 'cors' });
    if (!response.ok) {
      throw new Error(`Google Translate respondió ${response.status}`);
    }
    return parseGoogle(await response.json());
  };

  const requestMyMemory = async (text, lang) => {
    const url = `${MYMEMORY_ENDPOINT}?q=${encodeURIComponent(
      text
    )}&langpair=es|${lang}`;
    const response = await fetch(url, { mode: 'cors' });
    if (!response.ok) {
      throw new Error(`MyMemory respondió ${response.status}`);
    }
    const payload = await response.json();
    const translated = payload?.responseData?.translatedText;
    return translated && translated.trim() ? translated : null;
  };

  const translateOne = (text, lang) => {
    const key = cacheKey(text, lang);
    if (inFlight.has(key)) {
      return inFlight.get(key);
    }

    const promise = (async () => {
      const cached = readCache(text, lang);
      if (cached) {
        return cached;
      }

      let translated = null;
      const { text: protectedText, replacements } = protectTerms(text);

      try {
        translated = await requestGoogle(protectedText, lang);
      } catch (error) {
        translated = null;
      }

      if (translated) {
        translated = restoreTerms(translated, replacements);
      }

      if (!translated) {
        try {
          translated = await requestMyMemory(protectedText, lang);
        } catch (error) {
          translated = null;
        }
      }

      if (translated) {
        translated = restoreTerms(translated, replacements);
        writeCache(text, lang, translated);
      }

      return translated;
    })();

    inFlight.set(key, promise);
    promise.finally(() => inFlight.delete(key));
    return promise;
  };

  const translateChunk = async (texts, lang) => {
    const result = new Map();
    const missing = [];

    texts.forEach((text) => {
      const cached = readCache(text, lang);
      if (cached) {
        result.set(text, cached);
      } else if (!missing.includes(text)) {
        missing.push(text);
      }
    });

    if (!missing.length) {
      return result;
    }

    const protectedItems = missing.map((text) => {
      const { text: protectedText, replacements } = protectTerms(text);
      return { original: text, protectedText, replacements };
    });
    const joined = protectedItems
      .map((item) => item.protectedText)
      .join(`\n${SENTINEL}\n`);

    if (joined.length <= MAX_CHUNK) {
      let batch = null;

      try {
        batch = await requestGoogle(joined, lang);
      } catch (error) {
        try {
          batch = await requestMyMemory(joined, lang);
        } catch (fallbackError) {
          batch = null;
        }
      }

      if (batch) {
        const parts = batch.split(SENTINEL).map((part) => part.trim());
        if (parts.length === missing.length) {
          protectedItems.forEach((item, index) => {
            if (parts[index]) {
              const translated = restoreTerms(
                parts[index],
                item.replacements
              );
              writeCache(item.original, lang, translated);
              result.set(item.original, translated);
            }
          });
          return result;
        }
      }
    }

    // Si el lote falla o el separador se pierde, traducir uno a uno.
    for (const text of missing) {
      const translated = await translateOne(text, lang);
      if (translated) {
        result.set(text, translated);
      }
    }

    return result;
  };

  const translateTexts = async (texts, lang) => {
    const unique = [...new Set(texts.filter(Boolean))];
    const result = new Map();

    for (let index = 0; index < unique.length; index += 12) {
      const chunk = unique.slice(index, index + 12);
      const translated = await translateChunk(chunk, lang);
      translated.forEach((value, key) => result.set(key, value));

      if (index + 12 < unique.length) {
        await sleep(CHUNK_DELAY);
      }
    }

    return result;
  };

  const isSkipped = (element, { skipAuto = false } = {}) => {
    if (!element || typeof element.closest !== 'function') {
      return true;
    }

    const selector = [
      '[data-no-translate]',
      '[data-i18n]',
      '[data-i18n-aria-label]',
      '[data-i18n-placeholder]',
      '[data-i18n-content]',
      'script',
      'style',
      'noscript',
      'svg'
    ];

    if (skipAuto) {
      selector.push('[data-auto-translate]');
    }

    return Boolean(element.closest(selector.join(',')));
  };

  const isTranslatable = (text) =>
    Boolean(text && text.trim() && /[A-Za-zÁÉÍÓÚáéíóúÑñÜü]/.test(text));

  const getTextNodes = (root) => {
    const nodes = [];

    if (!root) {
      return nodes;
    }

    if (root.nodeType === Node.TEXT_NODE) {
      if (
        isTranslatable(root.nodeValue) &&
        !isSkipped(root.parentElement, { skipAuto: true })
      ) {
        nodes.push(root);
      }
      return nodes;
    }

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        if (!isTranslatable(node.nodeValue)) {
          return NodeFilter.FILTER_REJECT;
        }
        if (isSkipped(node.parentElement, { skipAuto: true })) {
          return NodeFilter.FILTER_REJECT;
        }
        return NodeFilter.FILTER_ACCEPT;
      }
    });

    let node = walker.nextNode();
    while (node) {
      nodes.push(node);
      node = walker.nextNode();
    }

    return nodes;
  };

  const getAutoElements = (root) => {
    const elements = [];

    if (!root || root.nodeType !== Node.ELEMENT_NODE) {
      return elements;
    }

    if (root.matches('[data-auto-translate]') && !isSkipped(root)) {
      elements.push(root);
    }

    root.querySelectorAll('[data-auto-translate]').forEach((element) => {
      if (!isSkipped(element)) {
        elements.push(element);
      }
    });

    return elements;
  };

  const getOriginal = (node) => {
    if (originals.has(node)) {
      return originals.get(node);
    }

    const original =
      node.nodeType === Node.TEXT_NODE ? node.nodeValue : node.textContent;
    originals.set(node, original);
    return original;
  };

  const applyTranslation = (node, translated) => {
    const original = getOriginal(node);
    const leading = (original.match(/^\s*/) || [''])[0];
    const trailing = (original.match(/\s*$/) || [''])[0];

    if (node.nodeType === Node.TEXT_NODE) {
      node.nodeValue = leading + translated + trailing;
      return;
    }

    const textNodes = Array.from(node.childNodes).filter(
      (child) => child.nodeType === Node.TEXT_NODE
    );

    if (textNodes.length) {
      textNodes[0].nodeValue = leading + translated + trailing;
      textNodes.slice(1).forEach((child) => child.remove());
    } else {
      node.appendChild(document.createTextNode(translated));
    }
  };

  const translateCollected = async (textNodes, autoElements) => {
    const texts = [
      ...textNodes.map((node) => getOriginal(node).trim()),
      ...autoElements.map((element) => getOriginal(element).trim())
    ].filter(Boolean);

    if (!texts.length) {
      return;
    }

    const translations = await translateTexts(texts, 'en');

    if (currentLang !== 'en') {
      return;
    }

    textNodes.forEach((node) => {
      const original = getOriginal(node).trim();
      const translated = translations.get(original);
      if (translated) {
        applyTranslation(node, translated);
      }
    });

    autoElements.forEach((element) => {
      const original = getOriginal(element).trim();
      const translated = translations.get(original);
      if (translated) {
        applyTranslation(element, translated);
      }
    });
  };

  const collectFromRoots = (roots) => {
    const textNodes = new Set();
    const autoElements = new Set();

    roots.forEach((root) => {
      getTextNodes(root).forEach((node) => textNodes.add(node));
      getAutoElements(root).forEach((element) => autoElements.add(element));
    });

    return {
      textNodes: [...textNodes],
      autoElements: [...autoElements]
    };
  };

  const translateDocument = async () => {
    if (currentLang !== 'en') {
      return;
    }

    const roots = [
      ...document.querySelectorAll('[data-auto-translate-scope]'),
      ...document.querySelectorAll('[data-auto-translate]')
    ];

    if (!roots.length) {
      return;
    }

    const { textNodes, autoElements } = collectFromRoots(roots);
    await translateCollected(textNodes, autoElements);
  };

  const translateNode = async (root) => {
    if (currentLang !== 'en' || !root) {
      return;
    }

    if (root.nodeType === Node.TEXT_NODE) {
      if (isSkipped(root.parentElement, { skipAuto: true })) {
        return;
      }

      const inScope = root.parentElement?.closest(
        '[data-auto-translate-scope], [data-auto-translate]'
      );
      if (inScope) {
        await translateCollected([root], []);
      }
      return;
    }

    if (root.nodeType !== Node.ELEMENT_NODE) {
      return;
    }

    const inScope =
      root.closest('[data-auto-translate-scope]') ||
      root.matches('[data-auto-translate-scope]');
    const hasAuto =
      root.matches('[data-auto-translate]') ||
      Boolean(root.querySelector('[data-auto-translate]'));

    if (!inScope && !hasAuto) {
      return;
    }

    const { textNodes, autoElements } = collectFromRoots([root]);
    await translateCollected(textNodes, autoElements);
  };

  const restore = () => {
    originals.forEach((original, node) => {
      if (node.nodeType === Node.TEXT_NODE) {
        node.nodeValue = original;
        return;
      }

      if (node.nodeType !== Node.ELEMENT_NODE) {
        return;
      }

      const textNodes = Array.from(node.childNodes).filter(
        (child) => child.nodeType === Node.TEXT_NODE
      );

      if (textNodes.length) {
        textNodes[0].nodeValue = original;
        textNodes.slice(1).forEach((child) => child.remove());
      }
    });

    originals.clear();
  };

  const setLanguage = async (lang) => {
    const normalized = lang === 'en' ? 'en' : 'es';

    if (normalized === currentLang) {
      if (normalized === 'en') {
        await translateDocument();
      }
      return;
    }

    currentLang = normalized;

    if (normalized === 'es') {
      restore();
      return;
    }

    await translateDocument();
  };

  let scheduledRoots = [];
  let scheduledFrame = null;

  const scheduleTranslation = (root) => {
    if (currentLang !== 'en') {
      return;
    }

    if (root) {
      scheduledRoots.push(root);
    }

    if (scheduledFrame) {
      return;
    }

    scheduledFrame = window.requestAnimationFrame(() => {
      const targets = scheduledRoots.length
        ? scheduledRoots
        : [document.body];
      scheduledRoots = [];
      scheduledFrame = null;
      targets.forEach((target) => translateNode(target));
    });
  };

  const observer = new MutationObserver((mutations) => {
    if (currentLang !== 'en') {
      return;
    }

    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (
          node.nodeType === Node.ELEMENT_NODE ||
          node.nodeType === Node.TEXT_NODE
        ) {
          scheduleTranslation(node);
        }
      });
    });
  });

  document.addEventListener('rdm:languagechange', (event) => {
    setLanguage(event.detail?.lang || getLanguage());
  });

  const start = () => {
    currentLang = getLanguage();
    observer.observe(document.body, { childList: true, subtree: true });

    if (currentLang === 'en') {
      // Espera a que i18n.js aplique el diccionario fijo.
      window.setTimeout(() => translateDocument(), 0);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
