<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/config/db.php';
require_once __DIR__ . '/lang/i18n.php';

$pdo = getPDO();

function public_galeria_rutas(?string $raw): array
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    $items = [];
    if (is_array($decoded)) {
        foreach ($decoded as $item) {
            $url = is_array($item) ? (string) ($item['url'] ?? '') : (string) $item;
            if ($url !== '') {
                $items[] = $url;
            }
        }
    } else {
        foreach ((preg_split('/\R/', $raw) ?: []) as $line) {
            $url = trim((string) $line);
            if ($url !== '') {
                $items[] = $url;
            }
        }
    }

    $result = [];
    foreach ($items as $item) {
        $url = trim((string) $item);
        if ($url !== '' && !in_array($url, $result, true)) {
            $result[] = $url;
        }
    }

    return $result;
}

$locations = ['Playa Salguero', 'Bello Horizonte', 'Rodadero Sur'];
$selectedLocation = '';
$selectedGuests = '';

if (isset($_GET['ubicacion']) && in_array((string) $_GET['ubicacion'], $locations, true)) {
    $selectedLocation = (string) $_GET['ubicacion'];
}

if (isset($_GET['huespedes']) && ctype_digit((string) $_GET['huespedes']) && (int) $_GET['huespedes'] > 0) {
    $selectedGuests = (string) $_GET['huespedes'];
}

$maxGuests = 0;
$statement = $pdo->query('SELECT MAX(huespedes) AS max_huespedes FROM apartamentos WHERE estado = 1');
$maxGuests = (int) ($statement->fetch()['max_huespedes'] ?? 0);
if ($maxGuests < 1) {
    $maxGuests = 1;
}

$sql = 'SELECT a.id, a.codigo_apartamento, a.titulo, a.ubicacion, a.huespedes, a.habitaciones, a.banos, a.camas, a.imagen_principal, a.galeria, a.descripcion, a.estado, a.fecha_creacion, u.nombre AS actualizado_por_nombre
        FROM apartamentos a
        LEFT JOIN usuarios u ON u.id = a.actualizado_por
        WHERE a.estado = 1';
$params = [];
if ($selectedLocation !== '') {
    $sql .= ' AND a.ubicacion = :ubicacion';
    $params['ubicacion'] = $selectedLocation;
}
if ((int) $selectedGuests > 0) {
    $sql .= ' AND a.huespedes >= :huespedes';
    $params['huespedes'] = (int) $selectedGuests;
}
$sql .= ' ORDER BY a.fecha_creacion DESC, a.id DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$apartments = $statement->fetchAll();

$amenidadesPorApartamento = [];
$apartmentIds = array_map(static function ($apartment) {
    return (int) $apartment['id'];
}, $apartments);

if ($apartmentIds !== []) {
    $placeholders = implode(',', array_fill(0, count($apartmentIds), '?'));
    $statement = $pdo->prepare(
        'SELECT aa.apartamento_id, am.nombre
         FROM apartamento_amenidades aa
         INNER JOIN amenidades am ON am.id = aa.amenidad_id
         WHERE aa.apartamento_id IN (' . $placeholders . ')'
    );
    $statement->execute($apartmentIds);
    foreach ($statement->fetchAll() as $row) {
        $apartmentId = (int) $row['apartamento_id'];
        $amenidadesPorApartamento[$apartmentId][] = (string) $row['nombre'];
    }
}

$dataApartments = [];
foreach ($apartments as $apartment) {
    $gallery = public_galeria_rutas((string) ($apartment['galeria'] ?? ''));
    if ($gallery === []) {
        $gallery = [(string) ($apartment['imagen_principal'] ?: 'assets/images/logordm.png')];
    }

    $dataApartments[] = [
        'id' => (int) $apartment['id'],
        'titulo' => (string) $apartment['titulo'],
        'ubicacion' => (string) $apartment['ubicacion'],
        'edificio' => (string) ($apartment['ubicacion'] ?: 'Reserva del Mar'),
        'capacidad_huespedes' => (int) $apartment['huespedes'],
        'huespedes' => (int) $apartment['huespedes'],
        'habitaciones' => (int) $apartment['habitaciones'],
        'banos' => (int) $apartment['banos'],
        'camas' => (int) $apartment['camas'],
        'imagen_principal' => (string) ($apartment['imagen_principal'] ?: $gallery[0]),
        'galeria' => array_map('strval', $gallery),
        'descripcion' => (string) ($apartment['descripcion'] ?? ''),
        'amenidades' => array_map('strval', $amenidadesPorApartamento[(int) $apartment['id']] ?? []),
        'amenidades_edificio' => array_map('strval', $amenidadesPorApartamento[(int) $apartment['id']] ?? []),
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-i18n="page_title_apartments">Apartamentos amoblados en Santa Marta | Reserva del Mar</title>
  <meta name="description" content="Apartamentos amoblados en Santa Marta con playa privada y vista al mar en Playa Salguero, Bello Horizonte y Rodadero Sur. Cotiza por WhatsApp." data-i18n-content="meta_description_apartments">
  <meta name="keywords" content="apartamentos amoblados en Santa Marta, arriendo vacacional Playa Salguero, apartamentos frente al mar Santa Marta, apartamentos Rodadero Sur, apartamentos Bello Horizonte, Reserva del Mar">
  <meta name="author" content="Apartamentos Reserva del Mar by Néstor Echeverry">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <link rel="canonical" href="https://apartamentosreservadelmar.com.co/apartamentos.html">
  <meta name="geo.region" content="CO-MAG">
  <meta name="geo.placename" content="Santa Marta, Magdalena, Colombia">
  <meta name="geo.position" content="11.188704;-74.233854">
  <meta name="ICBM" content="11.188704, -74.233854">
  <meta name="theme-color" content="#0F172A">
  <link rel="preload" href="assets/fonts/roboto-condensed-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="assets/css/tailwind.css?v=20261107h">
  <link rel="preload" as="image" href="assets/images/apartamentos-hero.jpeg" type="image/jpeg" fetchpriority="high">
  <link rel="icon" href="assets/images/logordm.png" type="image/png">
  <link rel="stylesheet" href="assets/css/styles.css?v=20261109a">
  <script src="assets/js/i18n.js?v=20261109b" defer></script>
  <script src="assets/js/auto-translate.js?v=20261104e" defer></script>
  <script src="assets/js/main.js?v=20261109b" defer></script>
<!-- Open Graph y Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://apartamentosreservadelmar.com.co/apartamentos.html">
    <meta property="og:title" content="Apartamentos amoblados en Santa Marta | Reserva del Mar" data-i18n-content="page_title_apartments">
    <meta property="og:description" content="Apartamentos amoblados en Santa Marta con playa privada y vista al mar en Playa Salguero, Bello Horizonte y Rodadero Sur. Cotiza por WhatsApp." data-i18n-content="meta_description_apartments">
    <meta property="og:image" content="https://apartamentosreservadelmar.com.co/assets/images/apartamentos-hero.jpeg">
    <meta property="og:image:secure_url" content="https://apartamentosreservadelmar.com.co/assets/images/apartamentos-hero.jpeg">
    <meta property="og:image:width" content="1280">
    <meta property="og:image:height" content="851">
    <meta property="og:image:alt" content="Apartamentos amoblados frente al mar en Santa Marta - Reserva del Mar">
    <meta property="og:locale" content="es_CO">
    <meta property="og:locale:alternate" content="en_US">
    <meta property="og:site_name" content="Apartamentos Reserva del Mar">
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Apartamentos amoblados en Santa Marta | Reserva del Mar" data-i18n-content="page_title_apartments">
    <meta name="twitter:description" content="Apartamentos amoblados en Santa Marta con playa privada y vista al mar en Playa Salguero, Bello Horizonte y Rodadero Sur. Cotiza por WhatsApp." data-i18n-content="meta_description_apartments">
    <meta name="twitter:image" content="https://apartamentosreservadelmar.com.co/assets/images/apartamentos-hero.jpeg">
    <meta name="twitter:image:alt" content="Apartamentos amoblados frente al mar en Santa Marta - Reserva del Mar">
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LodgingBusiness",
      "@id": "https://apartamentosreservadelmar.com.co/#lodging",
      "name": "Apartamentos Reserva del Mar by Néstor Echeverry",
      "alternateName": "Reserva del Mar Apartamentos",
      "description": "Alojamiento y apartamentos vacacionales frente al mar en Santa Marta, con sedes en Playa Salguero, Rodadero Sur y Bello Horizonte.",
      "slogan": "Alojamiento y apartamentos vacacionales frente al mar en Santa Marta",
      "url": "https://apartamentosreservadelmar.com.co/",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://apartamentosreservadelmar.com.co/apartamentos.html"
      },
      "image": [
        "https://apartamentosreservadelmar.com.co/assets/images/apartamentos-hero.jpeg",
        "https://apartamentosreservadelmar.com.co/assets/images/portada-hero.jpeg",
        "https://apartamentosreservadelmar.com.co/assets/images/amenidades-hero.jpg",
        "https://apartamentosreservadelmar.com.co/assets/images/contactanos-hero-1920.jpg"
      ],
      "logo": {
        "@type": "ImageObject",
        "url": "https://apartamentosreservadelmar.com.co/assets/images/logordm.png",
        "width": 1697,
        "height": 2400
      },
      "telephone": "+573218839636",
      "priceRange": "$$",
      "currenciesAccepted": "COP",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Playa Salguero",
        "addressLocality": "Santa Marta",
        "addressRegion": "Magdalena",
        "postalCode": "470004",
        "addressCountry": "CO"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": "11.188704",
        "longitude": "-74.233854"
      },
      "hasMap": "https://maps.app.goo.gl/pB4uRWsYJX93Zz1o8",
      "sameAs": [
        "https://maps.app.goo.gl/pB4uRWsYJX93Zz1o8",
        "https://maps.app.goo.gl/YDTNcSqBquLtaxyN6",
        "https://maps.app.goo.gl/pfgSGLJLkxF6J8ce7",
        "https://share.google/zNO8CRGQHbuWpF9g9"
      ],
      "areaServed": [
        {
          "@type": "City",
          "name": "Santa Marta"
        },
        {
          "@type": "AdministrativeArea",
          "name": "Magdalena"
        }
      ],
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday"
          ],
          "opens": "08:00",
          "closes": "20:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Saturday",
            "Sunday"
          ],
          "opens": "08:00",
          "closes": "15:00"
        }
      ],
      "checkinTime": "15:00",
      "checkoutTime": "12:00",
      "amenityFeature": [
        {
          "@type": "LocationFeatureSpecification",
          "name": "Playa privada",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Piscina",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Jacuzzi",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Rooftop",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Zona BBQ",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Wi-Fi de alta velocidad",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Gimnasio",
          "value": true
        },
        {
          "@type": "LocationFeatureSpecification",
          "name": "Parqueadero privado",
          "value": true
        }
      ],
      "founder": {
        "@type": "Person",
        "name": "Néstor Echeverry",
        "jobTitle": "Anfitrión"
      },
      "employee": {
        "@type": "Person",
        "name": "Néstor Echeverry",
        "jobTitle": "Anfitrión"
      },
      "contactPoint": [
        {
          "@type": "ContactPoint",
          "telephone": "+573218839636",
          "contactType": "reservations",
          "url": "https://wa.me/573218839636",
          "availableLanguage": [
            "Spanish",
            "English"
          ],
          "areaServed": "CO"
        }
      ],
      "potentialAction": {
        "@type": "ReserveAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "https://wa.me/573218839636?text=Hola%2C%20me%20interesa%20cotizar%20un%20apartamento%20en%20Reserva%20del%20Mar",
          "inLanguage": "es-CO",
          "actionPlatform": [
            "http://schema.org/DesktopWebPlatform",
            "http://schema.org/MobileWebPlatform"
          ]
        },
        "result": {
          "@type": "Reservation",
          "name": "Reserva de apartamento vacacional"
        }
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "5",
        "bestRating": "5",
        "worstRating": "1",
        "ratingCount": "150",
        "reviewCount": "150"
      },
      "knowsLanguage": [
        "es",
        "en"
      ],
      "brand": {
        "@type": "Brand",
        "name": "Reserva del Mar"
      },
      "additionalType": "https://schema.org/VacationRental"
    }
    </script>
  </head>
<body>
  <!-- Header flotante / transparente -->
  <header class="site-header">
    <div class="logo-container">
        <a href="index.html">
            <img src="assets/images/logordm.png" alt="Logo de Apartamentos Reserva del Mar by Néstor Echeverry">
        </a>
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="index.html" data-i18n="nav_home">Inicio</a></li>
            <li><a href="apartamentos.html" data-i18n="nav_suites">Apartamentos</a></li>
            <li><a href="amenidades.html" data-i18n="nav_amenities">Amenidades</a></li>
            <li><a href="contactanos.html" data-i18n="nav_contact">Contáctanos</a></li>
        </ul>
    </nav>
    <div class="header-actions">
        <div class="lang-dropdown">
            <button class="lang-btn" type="button" aria-haspopup="true" aria-expanded="false" data-i18n-aria-label="lang_selector" aria-label="Seleccionar idioma">
                <svg class="lang-globe" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                <span class="lang-code" data-lang-code>ES</span>
                <svg class="lang-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="lang-menu">
                <a href="?lang=es" data-lang="es" role="menuitem"><span>Español</span><span class="lang-code">ES</span></a>
                <a href="?lang=en" data-lang="en" role="menuitem"><span>English</span><span class="lang-code">EN</span></a>
            </div>
        </div>
        <a href="https://wa.me/573218839636?text=Hola%2C%20me%20gustar%C3%ADa%20obtener%20m%C3%A1s%20informaci%C3%B3n%20sobre%20las%20reservas" target="_blank" rel="noopener noreferrer" class="btn-reserva" data-i18n="btn_reserve">Reserva Aquí</a>
    </div>
</header>

  <main id="contenido" data-auto-translate-scope>
    <section class="page-hero page-hero--image page-hero--apartamentos">
      <div class="container">
        <div class="hero__copy">
          <p class="eyebrow" data-i18n="apartments_eyebrow">Catálogo completo</p>
          <h1 data-i18n="apartments_title">ENCUENTRA TU APARTAMENTO.</h1>
          <p data-i18n="apartments_lead">Explora las opciones disponibles en nuestros tres edificios.</p>
        </div>
      </div>
    </section>

    <section class="relative pb-16 font-corporate md:pb-20" aria-labelledby="catalogo-title">
      <div class="mx-auto w-[min(calc(100%-2rem),1120px)]">
        <div class="relative z-10 -mt-8 rounded-[1.75rem] border border-slate-900/5 bg-white p-5 rdm-shadow-float sm:p-6 md:-mt-14 md:p-8">
          <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="max-w-2xl">
              <p data-i18n="catalog_eyebrow" class="text-xs font-bold uppercase tracking-[0.18em] text-[#C5A059]">Disponibilidad</p>
              <h2 id="catalogo-title" data-i18n="catalog_title" class="mt-2 text-2xl font-bold leading-tight text-[#0F172A] md:text-3xl">Nuestros apartamentos</h2>
              <p data-i18n="catalog_lead" class="mt-2 text-sm leading-relaxed text-slate-600">Filtra por ubicación y número de huéspedes para encontrar tu espacio ideal.</p>
            </div>
            <span data-no-translate class="inline-flex w-fit items-center gap-2 rounded-full bg-[#F8FAFC] px-4 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-900/5">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              Santa Marta, Colombia
            </span>
          </div>

          <form id="filtro-catalogo" method="GET" action="" class="mt-6 grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-stretch" data-i18n-aria-label="filter_aria" aria-label="Filtrar apartamentos por ubicación y huéspedes">
            <label for="filtro-ubicacion" class="group flex min-h-[64px] cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 transition duration-200 hover:border-slate-300 focus-within:border-[#C5A059] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#C5A059]/10">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0 text-[#C5A059]" aria-hidden="true">
                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <span class="min-w-0 flex-1">
                <span data-i18n="filter_location" class="block text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Ubicación</span>
                <select id="filtro-ubicacion" name="ubicacion" data-no-translate data-i18n-aria-label="filter_location_aria" aria-label="Ubicación o edificio" aria-controls="catalogo-apartamentos"
                        class="mt-0.5 w-full cursor-pointer appearance-none bg-transparent pr-2 text-sm font-semibold text-[#0F172A] focus:outline-none">
                  <option value="" data-i18n="filter_all_locations">Todas las ubicaciones</option>
                  <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location) ?>" <?= $selectedLocation === $location ? 'selected' : '' ?>><?= htmlspecialchars($location) ?></option>
                  <?php endforeach; ?>
                </select>
              </span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pointer-events-none h-4 w-4 shrink-0 text-[#C5A059]" aria-hidden="true">
                <path d="m19 9-7 7-7-7" />
              </svg>
            </label>

            <label for="filtro-huespedes" class="group flex min-h-[64px] cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 transition duration-200 hover:border-slate-300 focus-within:border-[#C5A059] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#C5A059]/10">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0 text-[#C5A059]" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
              <span class="min-w-0 flex-1">
                <span data-i18n="filter_guests" class="block text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Huéspedes</span>
                <select id="filtro-huespedes" name="huespedes" data-no-translate data-i18n-aria-label="filter_guests_aria" aria-label="Rango de huéspedes" aria-controls="catalogo-apartamentos"
                        class="mt-0.5 w-full cursor-pointer appearance-none bg-transparent pr-2 text-sm font-semibold text-[#0F172A] focus:outline-none">
                  <option value="" data-i18n="filter_all_ranges">Todos los rangos</option>
                  <?php for ($guestOption = 1; $guestOption <= $maxGuests; $guestOption++): ?>
                    <option value="<?= $guestOption ?>" <?= (int) $selectedGuests === $guestOption ? 'selected' : '' ?>><?= $guestOption ?></option>
                  <?php endfor; ?>
                </select>
              </span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pointer-events-none h-4 w-4 shrink-0 text-[#C5A059]" aria-hidden="true">
                <path d="m19 9-7 7-7-7" />
              </svg>
            </label>

            <button type="submit" data-i18n="filter_search"
                    class="inline-flex min-h-[64px] items-center justify-center gap-2 rounded-2xl bg-[#C5A059] px-6 text-sm font-bold text-[#0F172A] rdm-shadow-button transition duration-300 hover:bg-[#D4B26D] focus:outline-none focus:ring-2 focus:ring-[#C5A059] focus:ring-offset-2 md:px-8">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
              </svg>
              Buscar
            </button>
          </form>
        </div>

        <div class="mt-8">
          <p id="catalogo-conteo" data-no-translate class="text-sm font-medium text-slate-500" aria-live="polite">
            <?php
            $countText = (string) t('catalog_count_many', '{n} apartamentos disponibles');
            $countText = str_replace('{n}', (string) count($apartments), $countText);
            echo $countText;
            ?>
          </p>
        </div>

        <div id="catalogo-apartamentos" class="mt-5 grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-7" aria-busy="<?= $apartments === [] ? 'false' : 'false' ?>">
          <?php if ($apartments !== []): ?>
            <?php foreach ($apartments as $apartment): ?>
              <?php
                $building = (string) ($apartment['ubicacion'] ?: 'Reserva del Mar');
                $image = (string) ($apartment['imagen_principal'] ?: 'assets/images/logordm.png');
                $gallery = public_galeria_rutas((string) ($apartment['galeria'] ?? ''));
                if ($gallery === []) {
                    $gallery = [(string) $image];
                }
              ?>
              <article class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white rdm-shadow-card rdm-card-hover ring-1 ring-slate-900/5 transition duration-300">
                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                  <img src="<?= htmlspecialchars($image) ?>" data-card-image="<?= (int) $apartment['id'] ?>" alt="<?= htmlspecialchars((string) $apartment['titulo']) ?> - Apartamento amoblado en <?= htmlspecialchars($building) ?>, Santa Marta" loading="lazy" decoding="async" class="h-full w-full object-cover object-[center_30%] transition duration-500 group-hover:scale-[1.04]">
                  <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0F172A]/35 via-transparent to-transparent"></div>
                  <span data-no-translate class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-[#0F172A]/90 px-3 py-1.5 text-xs font-bold text-white shadow-sm backdrop-blur">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 text-[#C5A059]" aria-hidden="true">
                      <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
                      <circle cx="12" cy="10" r="3" />
                    </svg>
                    <?= htmlspecialchars($building) ?>
                  </span>
                  <?php if (count($gallery) > 1): ?>
                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                      <?php foreach ($gallery as $galleryIndex => $galleryImage): ?>
                        <button type="button" class="h-12 w-12 overflow-hidden rounded-full bg-white/95 ring-2 ring-[#C5A059] shadow-lg transition duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-[#C5A059]/30" data-card-gallery-apartment="<?= (int) $apartment['id'] ?>" data-card-gallery-index="<?= (int) $galleryIndex ?>" aria-label="Ver imagen <?= (int) ($galleryIndex + 1) ?> de <?= (int) count($gallery) ?> del apartamento <?= htmlspecialchars((string) $apartment['titulo']) ?>">
                          <img src="<?= htmlspecialchars((string) $galleryImage) ?>" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        </button>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="flex flex-1 flex-col p-6">
                  <h3 class="min-h-[3.5rem] text-xl font-bold leading-snug text-[#0F172A]"><?= htmlspecialchars((string) $apartment['titulo']) ?></h3>
                  <p data-no-translate class="mt-1 truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"><?= htmlspecialchars($building) ?></p>

                  <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                      </svg>
                      <span class="text-sm font-bold text-[#0F172A]"><?= (int) $apartment['huespedes'] ?></span>
                      <span data-i18n="guests" class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]"><?= t('guests', 'Huéspedes') ?></span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                        <path d="M2 4v16" />
                        <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                        <path d="M2 17h20" />
                        <path d="M6 8v9" />
                      </svg>
                      <span class="text-sm font-bold text-[#0F172A]"><?= (int) $apartment['habitaciones'] ?></span>
                      <span data-i18n="rooms" class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]"><?= t('rooms', 'Habitaciones') ?></span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                        <path d="M10 4 8 6" />
                        <path d="M17 19v2" />
                        <path d="M2 12h20" />
                        <path d="M7 19v2" />
                        <path d="M9 5 7.62 3.62A2.12 2.12 0 0 0 4 6v6" />
                        <path d="M4 12v3a5 5 0 0 0 5 5h6a5 5 0 0 0 5-5v-3" />
                      </svg>
                      <span class="text-sm font-bold text-[#0F172A]"><?= (int) $apartment['banos'] ?></span>
                      <span data-i18n="baths" class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]"><?= t('baths', 'Baños') ?></span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-50 px-2 py-3 text-center ring-1 ring-slate-900/5">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C5A059]" aria-hidden="true">
                        <path d="M7 8V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
                        <path d="M7 8v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" />
                        <path d="M9 12h6" />
                      </svg>
                      <span class="text-sm font-bold text-[#0F172A]"><?= (int) $apartment['camas'] ?></span>
                      <span data-i18n="beds" class="text-[9px] font-semibold uppercase tracking-[0.04em] text-slate-500 sm:text-[10px]"><?= t('beds', 'Camas') ?></span>
                    </div>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($amenidadesPorApartamento[(int) $apartment['id']] ?? [] as $amenidad): ?>
                      <span class="rounded-full bg-[#C5A059]/10 px-3 py-1 text-xs font-semibold text-[#0F172A]"><?= htmlspecialchars($amenidad) ?></span>
                    <?php endforeach; ?>
                  </div>

                  <div class="mt-auto pt-6">
                    <a href="https://wa.me/573218839636?text=<?= urlencode('Hola, me interesa cotizar el apartamento ' . $apartment['titulo']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#C5A059] px-5 py-3 text-sm font-bold text-[#0F172A] rdm-shadow-button transition duration-300 hover:bg-[#D4B26D] focus:outline-none focus:ring-2 focus:ring-[#C5A059] focus:ring-offset-2" target="_blank" rel="noopener noreferrer">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#0F172A]" aria-hidden="true">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z" />
                      </svg>
                      <span data-i18n="quote_whatsapp"><?= t('quote_whatsapp', 'Cotizar por WhatsApp') ?></span>
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500" data-i18n="catalog_empty">
              <?= t('catalog_empty', 'No hay apartamentos disponibles para los filtros seleccionados.') ?>
            </p>
          <?php endif; ?>
        </div>

        <div id="catalogo-vacio" data-no-translate role="status" class="mt-5 hidden rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500"></div>

        <noscript>
          <p class="mt-6 rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm text-slate-600">
            <span data-i18n="catalog_noscript">Activa JavaScript para consultar el catálogo de apartamentos.</span>
          </p>
        </noscript>

        <script>
          window.RDM_APARTAMENTOS = <?= json_encode($dataApartments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
          document.addEventListener('click', (event) => {
            const galleryTrigger = event.target.closest('[data-card-gallery-apartment]');
            if (galleryTrigger) {
              const apartments = window.RDM_APARTAMENTOS || [];
              const apartment = apartments.find(
                (item) => String(item.id) === galleryTrigger.dataset.cardGalleryApartment
              );
              if (!apartment) return;

              const gallery = Array.isArray(apartment.galeria)
                ? apartment.galeria.map((item) => (typeof item === 'string' ? item : item?.url)).filter(Boolean)
                : [];
              const images = gallery.length > 0 ? gallery : [apartment.imagen_principal || 'assets/images/logordm.png'];
              const index = Number(galleryTrigger.dataset.cardGalleryIndex);
              const image = images[index];
              const cardImage = document.querySelector(`[data-card-image="${apartment.id}"]`);
              if (cardImage) {
                cardImage.src = image;
                cardImage.alt = `${apartment.titulo || 'Apartamento'} - imagen ${index + 1}`;
              }

              document.querySelectorAll('[data-card-gallery-apartment]').forEach((item) => {
                const isActive = item === galleryTrigger && Number(item.dataset.cardGalleryIndex) === index;
                item.setAttribute('aria-pressed', String(isActive));
                item.classList.toggle('ring-2', isActive);
                item.classList.toggle('ring-[#C5A059]', isActive);
                item.classList.toggle('ring-1', !isActive);
                item.classList.toggle('ring-slate-900/10', !isActive);
                item.classList.toggle('opacity-70', !isActive);
                item.classList.toggle('opacity-100', isActive);
              });

              return;
            }

            const trigger = event.target.closest('[data-detalle-id]');
            if (!trigger) return;
            const apartments = window.RDM_APARTAMENTOS || [];
            const apartment = apartments.find((item) => String(item.id) === trigger.dataset.detalleId);
            if (!apartment) return;
            document.dispatchEvent(new CustomEvent('apartamento:detalle', { detail: { apartment } }));
          });
          document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('filtro-catalogo');
            if (!form) return;
            form.querySelectorAll('select').forEach((select) => {
              select.addEventListener('change', () => { form.requestSubmit(); });
            });
          });
        </script>
      </div>
    </section>
  </main>


    <footer class="site-footer bg-[#0F172A] border-t border-[#C5A059]/30">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-8">
        <!-- Columna 1: Logo y descripción -->
        <div>
          <div class="footer-logo">
            <img src="./assets/images/logordm.png" alt="Logo de Apartamentos Reserva del Mar by Néstor Echeverry">
          </div>
          <p class="mt-3 text-center text-sm text-slate-400" data-i18n="footer_tagline">Más de 6 años de experiencia en Santa Marta</p>
        </div>

        <!-- Columna 2: Navegación rápida -->
        <nav aria-label="Enlaces rápidos">
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="index.html" class="hover:text-[#C5A059] transition" data-i18n="nav_home">Inicio</a></li>
            <li><a href="apartamentos.html" class="hover:text-[#C5A059] transition" data-i18n="nav_suites">Apartamentos</a></li>
            <li><a href="amenidades.html" class="hover:text-[#C5A059] transition" data-i18n="nav_amenities">Amenidades</a></li>
            <li><a href="contactanos.html" class="hover:text-[#C5A059] transition" data-i18n="nav_contact">Contáctanos</a></li>
          </ul>
        </nav>

        <!-- Columna 3: Atención y horarios -->
        <div>
          <p class="text-sm font-medium text-[#C5A059] mb-2" data-i18n="footer_hours_title">Horario de atención</p>
          <p class="text-sm text-slate-400" data-i18n="footer_hours_weekdays">Lunes a Viernes: 8:00 am - 8:00 pm</p>
          <p class="text-sm text-slate-400" data-i18n="footer_hours_weekend">Fines de Semana: 8:00 am - 3:00 pm</p>
          <p class="text-sm text-slate-400" data-i18n="footer_location">Santa Marta, Colombia</p>
          <a href="admin.php" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#C5A059] underline underline-offset-2 hover:text-[#D4B26D]" data-i18n="nav_admin">Administración</a>
        </div>
      </div>

      <!-- Barra legal inferior -->
      <div class="mt-12 pt-8 text-center text-xs text-slate-500">
        <p data-i18n="footer_rights">© 2026 Apartamentos Reserva del Mar By Nestor Echeverry. Todos los derechos reservados.</p>
        <p class="mt-1" data-i18n="footer_rnt">RNT (Registro Nacional de Turismo) · Santa Marta, Colombia</p>
        <div class="mt-2 flex justify-center gap-4">
          <a href="privacidad.html" class="hover:text-slate-300 transition" data-i18n="legal_privacy">Política de privacidad</a>
          <a href="cookies.html" class="hover:text-slate-300 transition" data-i18n="legal_cookies">Política de cookies</a>
          <a href="terminos.html" class="hover:text-slate-300 transition" data-i18n="legal_terms">Términos y condiciones</a>
        </div>
      </div>
    </div>
  </footer>

  <div
    id="modal-apartamento"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-3 font-corporate sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-apartamento-titulo"
    aria-describedby="modal-apartamento-descripcion"
    aria-hidden="true"
  >
    <div id="modal-apartamento-backdrop" class="absolute inset-0 bg-[#0F172A]/75 backdrop-blur-sm"></div>
    <div class="relative z-10 flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[1.75rem] bg-white rdm-shadow-modal ring-1 ring-white/10">
      <button
        id="modal-apartamento-cerrar"
        type="button"
        class="absolute right-4 top-4 z-30 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-[#0F172A] shadow-sm ring-1 ring-slate-900/5 transition hover:bg-white hover:text-[#C5A059] focus:outline-none focus:ring-2 focus:ring-[#C5A059] focus:ring-offset-2"
        data-i18n-aria-label="modal_close" aria-label="Cerrar detalles del apartamento"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
          <path d="M18 6 6 18" />
          <path d="m6 6 12 12" />
        </svg>
      </button>
      <div id="modal-apartamento-contenido" data-auto-translate-scope class="min-h-0 overflow-y-auto overscroll-contain"></div>
    </div>
  </div>
<!-- Cookie Consent Banner -->
<div id="cookie-banner" class="fixed bottom-4 right-4 z-50 bg-[#0F172A] border-t border-[#C5A059] rounded-lg p-5 flex items-center gap-3 max-w-sm rdm-shadow-float translate-y-2 opacity-0 transition-transform transition-opacity duration-300">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12s-4 9-8 9-8-9-8-9 4-9 8-9 8 9 8 9Z"/><circle cx="12" cy="12" r="3" /></svg>
  <div>
    <h3 class="font-medium text-slate-100">Uso de Cookies</h3>
    <p class="text-slate-400 text-sm">Usamos cookies para mejorar tu experiencia en nuestro sitio. Al hacer clic en “Aceptar todas”, acepta nuestro uso de cookies según nuestra <a href="politica-cookies.html" class="underline text-slate-400">Política de Cookies</a>.</p>
  </div>
  <button id="cookie-accept" class="mt-4 w-full rounded-md bg-[#C5A059] px-6 py-2 text-sm font-bold text-[#0F172A] hover:bg-[#D4B26D] transition">Aceptar todas</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const banner = document.getElementById('cookie-banner');
  const acceptBtn = document.getElementById('cookie-accept');

  if (localStorage.getItem('cookiesAccepted') === 'true') {
    banner.style.display = 'none';
    return;
  }

  banner.style.display = 'block';
  requestAnimationFrame(() => {
    banner.style.transform = 'translateY(0)';
    banner.style.opacity = '1';
  });

  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('cookiesAccepted', 'true');
    banner.style.transition = 'transform 300ms ease, opacity 300ms ease';
    banner.style.transform = 'translateY(2rem)';
    banner.style.opacity = '0';
    setTimeout(() => {
      banner.style.display = 'none';
    }, 350);
  });
});
</script>

</body>
</html>
