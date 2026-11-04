/**
 * i18n.js
 * Traducciones ES/EN en cliente para el sitio estático.
 *
 * La carpeta lang/*.php se conserva como fuente canónica para un despliegue
 * con PHP. Como GitHub Pages no ejecuta PHP, este archivo replica el mismo
 * diccionario en JavaScript para que el cambio de idioma funcione sin romper
 * el HTML.
 */
(function () {
  'use strict';

  const translations = {
    es: {
      nav_home: 'Inicio',
      nav_suites: 'Apartamentos',
      nav_amenities: 'Amenidades',
      nav_contact: 'Contáctanos',
      nav_admin: 'Administración',
      btn_reserve: 'Reserva Aquí',
      btn_quote: 'Solicitar cotización',
      capacity: 'Capacidad',
      guests: 'Huéspedes',
      hero_badge: 'Apartamentos de Playa',
      hero_title: 'Reserva del Mar',
      hero_subtitle: 'Experiencias vacacionales excepcionales en Santa Marta',
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
      lang_selector: 'Seleccionar idioma',
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
        'Resuelve tus dudas sobre apartamentos, edificios y disponibilidad.'
    },
    en: {
      nav_home: 'Home',
      nav_suites: 'Apartments',
      nav_amenities: 'Amenities',
      nav_contact: 'Contact Us',
      nav_admin: 'Administration',
      btn_reserve: 'Book Now',
      btn_quote: 'Request a Quote',
      capacity: 'Capacity',
      guests: 'Guests',
      hero_badge: 'Beach Apartments',
      hero_title: 'Reserva del Mar',
      hero_subtitle: 'Exceptional vacation experiences in Santa Marta',
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
      lang_selector: 'Select language',
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
      contact_title: 'LET\'S TALK ABOUT YOUR NEXT STAY.',
      contact_lead: 'Get answers about apartments, buildings and availability.'
    }
  };

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

  const applyLanguage = (lang) => {
    const normalized = lang === 'en' ? 'en' : 'es';
    const dictionary = translations[normalized];

    document.documentElement.lang = normalized;

    document.querySelectorAll('[data-i18n]').forEach((element) => {
      const key = element.getAttribute('data-i18n');
      const value = dictionary[key];
      if (typeof value === 'string') {
        element.textContent = value;
      }
    });

    document
      .querySelectorAll('[data-i18n-aria-label]')
      .forEach((element) => {
        const key = element.getAttribute('data-i18n-aria-label');
        const value = dictionary[key];
        if (typeof value === 'string') {
          element.setAttribute('aria-label', value);
        }
      });

    document
      .querySelectorAll('[data-i18n-placeholder]')
      .forEach((element) => {
        const key = element.getAttribute('data-i18n-placeholder');
        const value = dictionary[key];
        if (typeof value === 'string') {
          element.setAttribute('placeholder', value);
        }
      });

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

    try {
      localStorage.setItem('rdm-language', normalized);
    } catch (error) {
      /* localStorage puede estar bloqueado */
    }

    document.dispatchEvent(
      new CustomEvent('rdm:languagechange', { detail: { lang: normalized } })
    );
  };

  window.rdmSetLanguage = applyLanguage;
  window.rdmGetLanguage = getInitialLanguage;
  window.rdmTranslations = translations;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      applyLanguage(getInitialLanguage());
    });
  } else {
    applyLanguage(getInitialLanguage());
  }
})();
