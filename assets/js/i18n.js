/**
 * i18n.js
 * Traducciones ES/EN de la interfaz fija (nav, botones, formularios, footer).
 *
 * La carpeta lang/*.php se conserva como fuente canónica para un despliegue
 * con PHP. Como GitHub Pages no ejecuta PHP, este archivo replica el mismo
 * diccionario en JavaScript para que el cambio de idioma funcione sin romper
 * el HTML.
 *
 * El contenido editable (descripciones, amenidades, apartamentos) se traduce
 * automáticamente en assets/js/auto-translate.js.
 */
(function () {
  'use strict';

  const translations = {
    es: {
      // Navegación y cabecera
      nav_home: 'Inicio',
      nav_suites: 'Apartamentos',
      nav_amenities: 'Amenidades',
      nav_contact: 'Contáctanos',
      nav_admin: 'Administración',
      btn_reserve: 'Reserva Aquí',
      btn_details: 'Ver Detalles',
      lang_selector: 'Seleccionar idioma',

      // Hero
      hero_badge: 'Apartamentos de Playa',
      hero_title: 'Reserva del Mar',
      hero_subtitle: 'Experiencias vacacionales excepcionales en Santa Marta',
      home_eyebrow: 'Santa Marta · Colombia',
      home_title: 'EL ESPACIO PERFECTO PARA TUS MEJORES VACACIONES.',
      home_lead:
        'Con más de 6 años brindando experiencias excepcionales, cuidamos cada detalle de tu visita para que solo te preocupes por disfrutar y relajarte.',
      home_cta: 'Explorar apartamentos',
      apartments_eyebrow: 'Catálogo completo',
      apartments_title: 'ENCUENTRA TU APARTAMENTO.',
      apartments_lead:
        'Explora las opciones disponibles en nuestros tres edificios.',
      amenities_eyebrow: 'Tres experiencias',
      amenities_title: 'AMENIDADES PARA CADA EDIFICIO.',
      amenities_lead:
        'Consulta los servicios y espacios que diferencian a cada una de nuestras ubicaciones.',
      contact_eyebrow: 'Estamos para ayudarte',
      contact_title: 'HABLEMOS DE TU PRÓXIMA ESTADÍA.',
      contact_lead:
        'Resuelve tus dudas sobre apartamentos, edificios y disponibilidad.',

      // Catálogo / filtros
      catalog_eyebrow: 'Disponibilidad',
      catalog_title: 'Nuestros apartamentos',
      catalog_lead:
        'Filtra por ubicación y número de huéspedes para encontrar tu espacio ideal.',
      catalog_loading: 'Cargando apartamentos...',
      catalog_unavailable: 'Catálogo no disponible',
      catalog_empty:
        'No hay apartamentos disponibles para la ubicación y el rango de huéspedes seleccionados.',
      catalog_error:
        'No fue posible cargar los apartamentos. Intenta nuevamente más tarde.',
      catalog_count_one: '1 apartamento disponible',
      catalog_count_many: '{n} apartamentos disponibles',
      catalog_noscript:
        'Activa JavaScript para consultar el catálogo de apartamentos.',
      filter_aria: 'Filtrar apartamentos por ubicación y huéspedes',
      filter_location: 'Ubicación',
      filter_location_aria: 'Ubicación o edificio',
      filter_all_locations: 'Todas las ubicaciones',
      filter_guests: 'Huéspedes',
      filter_guests_aria: 'Rango de huéspedes',
      filter_all_ranges: 'Todos los rangos',
      filter_search: 'Buscar',
      range_1_2: '1 a 2 huéspedes',
      range_3_4: '3 a 4 huéspedes',
      range_5_6: '5 a 6 huéspedes',
      range_7: '7 o más huéspedes',

      // Etiquetas de tarjetas y acciones
      capacity: 'Capacidad',
      guests: 'Huéspedes',
      rooms: 'Habitaciones',
      baths: 'Baños',
      beds: 'Camas',
      btn_quote: 'Solicitar cotización',
      see_more: 'Ver más...',
      see_less: 'Ver menos',
      see_amenities: 'Ver Amenidades',
      see_apartments: 'Ver apartamentos',
      quote_whatsapp: 'Cotizar por WhatsApp',
      write_whatsapp: 'Escribir por WhatsApp',
      send_whatsapp: 'Enviar por WhatsApp',
      open_maps: 'Abrir en Google Maps',
      map_faq_other: '¿Tienes otra pregunta? Escríbenos',

      // Formulario de contacto
      form_eyebrow: 'Escríbenos',
      form_title: 'Cuéntanos qué necesitas',
      form_lead:
        'Déjanos tus datos y el motivo de tu consulta. Al enviar, abriremos WhatsApp con el mensaje listo para que lo revises y envíes.',
      form_name: 'Nombre completo',
      form_name_placeholder: 'Tu nombre y apellido',
      form_phone: 'Teléfono / WhatsApp',
      form_building: 'Edificio de interés',
      form_building_placeholder: 'Selecciona un edificio',
      form_general: 'General',
      form_message: 'Mensaje',
      form_message_placeholder:
        'Cuéntanos fechas aproximadas, número de huéspedes o cualquier duda que tengas.',
      form_disclaimer:
        'Sin costos ni compromiso. Tus datos solo se usan para responder tu consulta.',
      form_status: 'Abriendo WhatsApp con tu mensaje listo para enviar.',

      // Cookies
      cookie_title: 'Uso de Cookies',
      cookie_accept: 'Aceptar todas',

      // Modal de apartamento
      modal_published: 'Publicación',
      modal_description: 'Descripción',
      modal_features: 'Características y servicios',
      modal_building_amenities: 'Amenidades del edificio',
      modal_direct: 'Atención directa del equipo Reserva del Mar',
      modal_features_empty:
        'Consulta con nuestro equipo las características disponibles.',
      modal_no_description: 'Descripción no disponible.',
      modal_close: 'Cerrar detalles del apartamento',

      // Footer
      footer_tagline: 'Más de 6 años de experiencia en Santa Marta',
      footer_hours_title: 'Horario de atención',
      footer_hours_weekdays: 'Lunes a Viernes: 8:00 am - 8:00 pm',
      footer_hours_weekend: 'Fines de Semana: 8:00 am - 3:00 pm',
      footer_location: 'Santa Marta, Colombia',
      footer_rights:
        '© 2026 Apartamentos Reserva del Mar By Nestor Echeverry. Todos los derechos reservados.',
      footer_rnt: 'RNT (Registro Nacional de Turismo) · Santa Marta, Colombia',
      legal_privacy: 'Política de privacidad',
      legal_cookies: 'Política de cookies',
      legal_terms: 'Términos y condiciones',

      // Amenidades de Playa Salguero
      amenity_pool: 'Piscina',
      amenity_bar_cafe: 'Bar & Cafetería',
      amenity_sports: 'Área Deportiva',
      amenity_kids: 'Salón Infantil',
      amenity_restaurant: 'Restaurante',
      amenity_pools_jacuzzis: 'Piscinas & Jacuzzis',
      amenity_bar: 'Bar',
      amenity_bbq: 'Zona BBQ',
      amenity_rooftop: 'Rooftop',
      amenity_rooftop_bar: 'Bar Rooftop',

      // Google Reviews
      google_reviews_title: 'Lo que dicen nuestros huéspedes en Google',
      google_reviews_subtitle:
        '★ 5.0 Excelencia en Servicio • Más de 150 reseñas reales en Google Maps',
      google_reviews_cta: 'Ver las 150+ reseñas oficiales en Google Maps',
      google_reviews_carousel_label: 'Reseñas de huéspedes en Google Maps',
      review_stars_label: '5 de 5 estrellas',
      review_verified_google: 'Reseña verificada en Google',

      // Metadatos
      page_title_home:
        'Apartamentos Reserva del Mar | Alquiler Vacacional en Santa Marta',
      page_title_apartments:
        'Apartamentos Reserva del Mar | Alquiler Vacacional en Santa Marta',
      page_title_amenities: 'Apartamentos Reserva del Mar · Santa Marta',
      page_title_contact: 'Contáctanos | Apartamentos Reserva del Mar',
      meta_description:
        'Disfruta de la mejor experiencia vacacional en Santa Marta. Apartamentos exclusivos frente al mar en Playa Salguero, Rodadero Sur y Bello Horizonte.',
      meta_contact_description:
        'Contacta a Apartamentos Reserva del Mar por WhatsApp, teléfono o email. Formulario directo, mapa de Santa Marta y preguntas frecuentes sobre reservas.',
      og_title: 'Apartamentos Reserva del Mar · Santa Marta',
      og_description:
        'El espacio perfecto para tus mejores vacaciones. Apartamentos frente al Caribe con atención 24/7 y amenidades tipo resort.',
      twitter_title: 'Apartamentos Reserva del Mar · Santa Marta',
      twitter_description:
        'Experiencias vacacionales excepcionales en Playa Salguero, Rodadero Sur y Bello Horizonte.'
    },
    en: {
      // Navigation and header
      nav_home: 'Home',
      nav_suites: 'Apartments',
      nav_amenities: 'Amenities',
      nav_contact: 'Contact Us',
      nav_admin: 'Administration',
      btn_reserve: 'Book Now',
      btn_details: 'View Details',
      lang_selector: 'Select language',

      // Hero
      hero_badge: 'Beach Apartments',
      hero_title: 'Reserva del Mar',
      hero_subtitle: 'Exceptional vacation experiences in Santa Marta',
      home_eyebrow: 'Santa Marta · Colombia',
      home_title: 'THE PERFECT SPACE FOR YOUR BEST VACATIONS.',
      home_lead:
        'With more than 6 years creating exceptional experiences, we take care of every detail of your visit so you only have to enjoy and relax.',
      home_cta: 'Explore apartments',
      apartments_eyebrow: 'Full catalog',
      apartments_title: 'FIND YOUR APARTMENT.',
      apartments_lead: 'Explore the options available in our three buildings.',
      amenities_eyebrow: 'Three experiences',
      amenities_title: 'AMENITIES FOR EVERY BUILDING.',
      amenities_lead:
        'Check the services and spaces that set each of our locations apart.',
      contact_eyebrow: 'We are here to help',
      contact_title: "LET'S TALK ABOUT YOUR NEXT STAY.",
      contact_lead:
        'Get answers about apartments, buildings and availability.',

      // Catalog / filters
      catalog_eyebrow: 'Availability',
      catalog_title: 'Our apartments',
      catalog_lead:
        'Filter by location and number of guests to find your ideal space.',
      catalog_loading: 'Loading apartments...',
      catalog_unavailable: 'Catalog unavailable',
      catalog_empty:
        'No apartments available for the selected location and guest range.',
      catalog_error:
        'We could not load the apartments. Please try again later.',
      catalog_count_one: '1 apartment available',
      catalog_count_many: '{n} apartments available',
      catalog_noscript: 'Enable JavaScript to browse the apartment catalog.',
      filter_aria: 'Filter apartments by location and guests',
      filter_location: 'Location',
      filter_location_aria: 'Location or building',
      filter_all_locations: 'All locations',
      filter_guests: 'Guests',
      filter_guests_aria: 'Guest range',
      filter_all_ranges: 'All ranges',
      filter_search: 'Search',
      range_1_2: '1 to 2 guests',
      range_3_4: '3 to 4 guests',
      range_5_6: '5 to 6 guests',
      range_7: '7+ guests',

      // Card labels and actions
      capacity: 'Capacity',
      guests: 'Guests',
      rooms: 'Rooms',
      baths: 'Bathrooms',
      beds: 'Beds',
      btn_quote: 'Request a Quote',
      see_more: 'See more...',
      see_less: 'See less',
      see_amenities: 'View Amenities',
      see_apartments: 'View apartments',
      quote_whatsapp: 'Request a quote via WhatsApp',
      write_whatsapp: 'Message us on WhatsApp',
      send_whatsapp: 'Send via WhatsApp',
      open_maps: 'Open in Google Maps',
      map_faq_other: 'Have another question? Write to us',

      // Contact form
      form_eyebrow: 'Write to us',
      form_title: 'Tell us what you need',
      form_lead:
        'Leave your details and the reason for your inquiry. When you submit, we will open WhatsApp with the message ready for you to review and send.',
      form_name: 'Full name',
      form_name_placeholder: 'Your first and last name',
      form_phone: 'Phone / WhatsApp',
      form_building: 'Building of interest',
      form_building_placeholder: 'Select a building',
      form_general: 'General',
      form_message: 'Message',
      form_message_placeholder:
        'Tell us approximate dates, number of guests or any questions you have.',
      form_disclaimer:
        'No costs or commitment. Your data is only used to answer your inquiry.',
      form_status: 'Opening WhatsApp with your message ready to send.',

      // Cookies
      cookie_title: 'Cookie use',
      cookie_accept: 'Accept all',

      // Apartment modal
      modal_published: 'Listing',
      modal_description: 'Description',
      modal_features: 'Features and services',
      modal_building_amenities: 'Building amenities',
      modal_direct: 'Direct support from the Reserva del Mar team',
      modal_features_empty:
        'Ask our team about the available features.',
      modal_no_description: 'Description not available.',
      modal_close: 'Close apartment details',

      // Footer
      footer_tagline: '6+ years of experience in Santa Marta',
      footer_hours_title: 'Opening hours',
      footer_hours_weekdays: 'Monday to Friday: 8:00 am - 8:00 pm',
      footer_hours_weekend: 'Weekends: 8:00 am - 3:00 pm',
      footer_location: 'Santa Marta, Colombia',
      footer_rights:
        '© 2026 Apartamentos Reserva del Mar By Nestor Echeverry. All rights reserved.',
      footer_rnt: 'RNT (National Tourism Registry) · Santa Marta, Colombia',
      legal_privacy: 'Privacy policy',
      legal_cookies: 'Cookie policy',
      legal_terms: 'Terms and conditions',

      // Playa Salguero amenities
      amenity_pool: 'Pool',
      amenity_bar_cafe: 'Bar & Café',
      amenity_sports: 'Sports Area',
      amenity_kids: 'Kids Club',
      amenity_restaurant: 'Restaurant',
      amenity_pools_jacuzzis: 'Pools & Jacuzzis',
      amenity_bar: 'Bar',
      amenity_bbq: 'BBQ Area',
      amenity_rooftop: 'Rooftop',
      amenity_rooftop_bar: 'Rooftop Bar',

      // Google Reviews
      google_reviews_title: 'What our guests say on Google',
      google_reviews_subtitle:
        '★ 5.0 Service Excellence • 150+ real reviews on Google Maps',
      google_reviews_cta: 'See the 150+ official reviews on Google Maps',
      google_reviews_carousel_label: 'Guest reviews on Google Maps',
      review_stars_label: '5 out of 5 stars',
      review_verified_google: 'Verified Google review',

      // Metadata
      page_title_home:
        'Apartamentos Reserva del Mar | Vacation Rentals in Santa Marta',
      page_title_apartments:
        'Apartments | Apartamentos Reserva del Mar',
      page_title_amenities: 'Amenities | Apartamentos Reserva del Mar',
      page_title_contact: 'Contact Us | Apartamentos Reserva del Mar',
      meta_description:
        'Enjoy the best vacation experience in Santa Marta. Exclusive beachfront apartments in Playa Salguero, Rodadero Sur and Bello Horizonte.',
      meta_contact_description:
        'Contact Apartamentos Reserva del Mar via WhatsApp, phone or email. Direct form, Santa Marta map and frequently asked questions about bookings.',
      og_title: 'Apartamentos Reserva del Mar · Santa Marta',
      og_description:
        'The perfect space for your best vacation. Beachfront apartments with 24/7 support and resort-style amenities.',
      twitter_title: 'Apartamentos Reserva del Mar · Santa Marta',
      twitter_description:
        'Exceptional vacation experiences in Playa Salguero, Rodadero Sur and Bello Horizonte.'
    }
  };

  let currentLanguage = 'es';

  const getInitialLanguage = () => {
    const params = new URLSearchParams(window.location.search);
    const fromQuery = params.get('lang');
    if (fromQuery === 'es' || fromQuery === 'en') {
      return fromQuery;
    }

    try {
      const stored = localStorage.getItem('rdm-language');
      if (stored === 'es' || stored === 'en') {
        return stored;
      }
    } catch (error) {
      /* localStorage puede estar bloqueado */
    }

    return 'es';
  };

  // Reemplaza solo los nodos de texto directos para no destruir iconos SVG
  // que acompañan a los botones y enlaces.
  const setElementText = (element, value) => {
    const textNodes = Array.from(element.childNodes).filter(
      (node) => node.nodeType === Node.TEXT_NODE
    );

    if (!textNodes.length) {
      element.appendChild(document.createTextNode(value));
      return;
    }

    const first = textNodes[0];
    const original = first.nodeValue || '';
    const leading = (original.match(/^\s*/) || [''])[0];
    const trailing = (original.match(/\s*$/) || [''])[0];

    first.nodeValue = leading + value + trailing;
    textNodes.slice(1).forEach((node) => node.remove());
  };

  const applyToElement = (element) => {
    const dictionary = translations[currentLanguage] || translations.es;

    const key = element.getAttribute('data-i18n');
    if (key && typeof dictionary[key] === 'string') {
      setElementText(element, dictionary[key]);
    }

    const ariaKey = element.getAttribute('data-i18n-aria-label');
    if (ariaKey && typeof dictionary[ariaKey] === 'string') {
      element.setAttribute('aria-label', dictionary[ariaKey]);
    }

    const placeholderKey = element.getAttribute('data-i18n-placeholder');
    if (placeholderKey && typeof dictionary[placeholderKey] === 'string') {
      element.setAttribute('placeholder', dictionary[placeholderKey]);
    }

    const contentKey = element.getAttribute('data-i18n-content');
    if (contentKey && typeof dictionary[contentKey] === 'string') {
      element.setAttribute('content', dictionary[contentKey]);
    }
  };

  const applyLanguage = (lang) => {
    const normalized = lang === 'en' ? 'en' : 'es';
    currentLanguage = normalized;
    const dictionary = translations[normalized];

    document.documentElement.lang = normalized;

    document
      .querySelectorAll(
        '[data-i18n], [data-i18n-aria-label], [data-i18n-placeholder], [data-i18n-content]'
      )
      .forEach(applyToElement);

    const langCode = document.querySelector('[data-lang-code]');
    if (langCode) {
      langCode.textContent = normalized === 'en' ? 'EN' : 'ES';
    }

    document.querySelectorAll('[data-lang]').forEach((option) => {
      option.setAttribute(
        'aria-current',
        option.dataset.lang === normalized ? 'true' : 'false'
      );
    });

    const langButton = document.querySelector('.lang-btn');
    if (langButton && dictionary.lang_selector) {
      langButton.setAttribute('aria-label', dictionary.lang_selector);
    }

    try {
      localStorage.setItem('rdm-language', normalized);
    } catch (error) {
      /* localStorage puede estar bloqueado */
    }

    // Mantiene la URL sincronizada para que al recargar o compartir el enlace
    // se conserve el idioma elegido.
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('lang', normalized);
      window.history.replaceState(null, '', url.toString());
    } catch (error) {
      /* history puede no estar disponible en algunos contextos */
    }

    document.dispatchEvent(
      new CustomEvent('rdm:languagechange', { detail: { lang: normalized } })
    );

    return normalized;
  };

  // Aplica el diccionario a elementos que se crean después (tarjetas,
  // modal, resultados de filtros).
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType !== Node.ELEMENT_NODE) {
          return;
        }

        if (
          node.matches(
            '[data-i18n], [data-i18n-aria-label], [data-i18n-placeholder], [data-i18n-content]'
          )
        ) {
          applyToElement(node);
        }

        node
          .querySelectorAll(
            '[data-i18n], [data-i18n-aria-label], [data-i18n-placeholder], [data-i18n-content]'
          )
          .forEach(applyToElement);
      });
    });
  });

  window.rdmSetLanguage = applyLanguage;
  window.rdmGetLanguage = () => currentLanguage;
  window.rdmTranslations = translations;
  window.rdmT = (key, fallback = '') => {
    const dictionary = translations[currentLanguage] || translations.es;
    return typeof dictionary[key] === 'string' ? dictionary[key] : fallback;
  };

  const start = () => {
    applyLanguage(getInitialLanguage());
    observer.observe(document.body, { childList: true, subtree: true });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
