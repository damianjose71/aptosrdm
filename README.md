# Apartamentos Reserva del Mar

Base de un sitio web estático para el catálogo de apartamentos turísticos de **Apartamentos Reserva del Mar**, en Santa Marta, Colombia. El proyecto contempla tres edificios independientes con amenidades diferenciadas y está preparado para gestionar contenido con Decap CMS.

La base incluye el header flotante y transparente con navegación de escritorio, menú móvil accesible y llamada a la acción hacia WhatsApp. El catálogo de apartamentos se renderiza dinámicamente desde `data/apartamentos.json` e incluye una barra de búsqueda con filtros combinados por ubicación y rango de huéspedes. Las tarjetas muestran métricas resumidas y abren un modal tipo marketplace con galería, descripción completa, servicios y cotización por WhatsApp.

## Estructura

```text
/apartamentos-reserva-del-mar
├── /admin
│   ├── index.html
│   └── config.yml
├── /assets
│   ├── /css
│   │   └── styles.css
│   ├── /fonts
│   │   ├── roboto-condensed-latin.woff2
│   │   ├── LICENSE.txt
│   │   └── README.md
│   ├── /js
│   │   ├── main.js
│   │   ├── filter.js
│   │   ├── contact.js
│   │   └── contact-map.js
│   └── /images
│       ├── /amenidades
│       │   ├── /playa-salguero
│       │   ├── /rodadero-sur
│       │   └── /bello-horizonte
│       ├── /uploads
│       └── logo-placeholder.svg
├── /data
│   └── apartamentos.json
├── index.html
├── apartamentos.html
├── apartamentos.php
├── .htaccess
├── amenidades.html
├── contactanos.html
└── README.md
```

## Páginas

- `index.html`: landing page.
- `apartamentos.html`: catálogo público. En GitHub Pages se alimenta de `data/apartamentos.json`; en Hostinger se reescribe a `apartamentos.php` mediante `.htaccess`.
- `apartamentos.php`: catálogo dinámico conectado a MySQL. Requiere PHP y la base de datos configurada.
- `amenidades.html`: amenidades segmentadas por edificio, con navegación por anclas y tarjetas estáticas. Las imágenes se organizan en `assets/images/amenidades/{edificio}/`.
- `contactanos.html`: canales de contacto, formulario que prepara la consulta en WhatsApp, mapa embebido de Google Maps con cambio de edificio y preguntas frecuentes.
- `admin/index.html`: acceso al panel de Decap CMS.

El formulario de contacto no requiere backend: valida los campos y abre WhatsApp con el nombre, teléfono, edificio de interés y mensaje del usuario ya redactados.

El mapa de contacto usa los embeds oficiales de Google Maps. Los botones de Playa Salguero, Rodadero Sur y Bello Horizonte actualizan el `src` del iframe sin recargar ni abandonar la página, y el enlace superior se sincroniza con la ubicación activa.

Los llamados a la acción de amenidades enlazan a `apartamentos.html?edificio=playa-salguero`, `?edificio=rodadero-sur` o `?edificio=bello-horizonte`; el catálogo aplica ese filtro automáticamente al cargar.

Los enlaces públicos usan `apartamentos.html` para que funcionen en GitHub Pages. En Hostinger, `.htaccess` reescribe esa ruta a `apartamentos.php`, que consulta MySQL y respeta la visibilidad y los filtros del panel. Si el hosting no procesa `.htaccess`, los enlaces deben cambiarse a `apartamentos.php`.

## Modelo de contenido

`data/apartamentos.json` contiene dos bloques:

- `edificios`: ID, nombre, ubicación y listado de amenidades del edificio.
- `apartamentos`: ID, título, edificio/zona, capacidad de huéspedes, habitaciones, baños, imagen principal, galería, descripción, características y disponibilidad.

Las tarjetas usan `edificio`, `capacidad_huespedes`, `habitaciones`, `banos` e `imagen_principal`. El modal normaliza la galería con formato `{ "url": "..." }` y muestra la descripción, las características y las amenidades del edificio.

El esquema editable está definido en `admin/config.yml`. Incluye los campos solicitados para el modal y dos campos adicionales (`edificios` y `caracteristicas`) necesarios para no perder contenido al guardar desde el CMS. Las imágenes subidas desde el CMS se almacenan en `assets/images/uploads`.

## Desarrollo local

El sitio no requiere compilación. Tailwind CSS se carga mediante su CDN para prototipado; para producción se recomienda generar una hoja de estilos compilada. La tipografía corporativa (Roboto Condensed) se sirve localmente desde `assets/fonts` para no depender de una CDN externa. Para evitar restricciones de `file://`, sirve la carpeta con un servidor local:

```bash
python3 -m http.server 8080
```

Luego abre `http://localhost:8080`.

## Configuración de Decap CMS

El panel está configurado con `test-repo` para abrir la interfaz sin autenticación. Este modo **no lee ni guarda** `data/apartamentos.json`; solo permite validar la interfaz y los campos.

Para publicar cambios reales en el repositorio `damianjose71/aptosrdm`:

1. Cambia `backend.name` a `github` y descomenta `repo`, `branch`, `base_url` y `auth_endpoint` (bloque incluido en `admin/config.yml`).
2. Configura un proveedor OAuth para GitHub. La configuración base usa Netlify como proveedor de autenticación.
3. Accede al panel en `/admin/`, realiza los cambios y publícalos desde el CMS.

Decap CMS confirma los cambios en el repositorio de GitHub, no directamente en Hostinger. Para que el sitio publicado se actualice, configura un despliegue automático desde el repositorio (Git o FTP) o vuelve a cargar los archivos después de cada publicación.

## Despliegue en Hostinger

1. Sube el contenido de esta carpeta a `public_html`.
2. Verifica que la carpeta `assets/images/uploads` tenga permisos de escritura si el flujo de publicación lo requiere.
3. Comprueba que las rutas de imágenes y del JSON coincidan con la URL final.
4. Habilita HTTPS y actualiza `site_url` en `admin/config.yml` si agregas esa opción.
5. Conserva el archivo `.htaccess` y ejecuta `admin/setup.php` una vez para crear las tablas y el administrador. En Hostinger, `apartamentos.html` se sirve internamente como `apartamentos.php` con los datos de MySQL.

## Pendientes de implementación

- Optimización de imágenes y metadatos SEO.
- Compilación de Tailwind CSS para producción.
