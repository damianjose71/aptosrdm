<?php

declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/admin/config/db.php';

function admin_flash(): array
{
    $type = (string) ($_SESSION['flash_type'] ?? '');
    $message = (string) ($_SESSION['flash_message'] ?? '');
    unset($_SESSION['flash_type'], $_SESSION['flash_message']);

    return ['type' => $type, 'message' => $message];
}

function admin_set_flash(string $type, string $message): void
{
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['admin_csrf_token'];
}

function admin_validate_csrf(): bool
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['admin_csrf_token'] ?? '');

    return $token !== '' && hash_equals($sessionToken, $token);
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        admin_redirigir('/admin.php');
    }
}

function admin_redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function admin_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_int_positive(?string $value, int $default = 0): int
{
    $int = (int) ($value ?? $default);
    return $int > 0 ? $int : $default;
}

function admin_int_nonnegative(?string $value, int $default = 0): int
{
    $int = (int) ($value ?? $default);
    return $int >= 0 ? $int : $default;
}

function admin_guardar_imagen(?array $file, string $prefix = 'apt-'): string
{
    if ($file === null || !isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        return '';
    }

    if ((int) ($file['size'] ?? 0) > 8388608) {
        throw new RuntimeException('La imagen supera el tamaño permitido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file((string) $file['tmp_name']);
    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    if (!isset($extensionMap[$mime])) {
        throw new RuntimeException('Tipo de imagen no permitido.');
    }

    $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $extensionMap[$mime];
    $directory = __DIR__ . '/assets/images/uploads';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
    $target = $directory . '/' . $filename;

    if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }

    return 'assets/images/uploads/' . $filename;
}

function admin_galeria_rutas(?string $raw): array
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
        $normalized = admin_normalizar_url($item);
        if ($normalized !== '' && !in_array($normalized, $result, true)) {
            $result[] = $normalized;
        }
    }

    return $result;
}

function admin_normalizar_url(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (
        preg_match('/^https?:\/\//i', $value) === 1
        || str_starts_with($value, '/')
        || str_starts_with($value, 'assets/')
        || str_starts_with($value, 'uploads/')
    ) {
        return $value;
    }

    return '';
}

$action = (string) ($_POST['action'] ?? '');
$flash = admin_flash();
$errors = $_SESSION['admin_errors'] ?? [];
$old = $_SESSION['admin_old'] ?? [];
$oldEditingId = (int) ($_SESSION['admin_old_edit'] ?? 0);

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();

    admin_redirigir('/admin.php');
}

if ($action === 'login_submit') {
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        admin_set_flash('error', 'Ingrese su correo electrónico y contraseña.');
        admin_redirigir('/admin.php');
    }

    $statement = $pdo->prepare(
        'SELECT id, nombre, email, password_hash, rol, estado FROM usuarios WHERE email = ? LIMIT 1'
    );
    $statement->execute([$email]);
    $user = $statement->fetch();

    if ($user && (int) $user['estado'] === 1 && password_verify($password, (string) $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_email'] = (string) $user['email'];
        $_SESSION['admin_rol'] = (string) $user['rol'];
        admin_set_flash('success', 'Sesión iniciada correctamente.');
        admin_redirigir('/admin.php');
    }

    admin_set_flash('error', 'Credenciales inválidas o cuenta inactiva.');
    admin_redirigir('/admin.php');
}

$adminId = (int) ($_SESSION['admin_id'] ?? 0);
$isAdmin = $adminId > 0;

if (!$isAdmin) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Panel Administrativo | Apartamentos Reserva del Mar</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[#0F172A] text-slate-200">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-[#C5A059]/40 bg-[#0F172A] p-8 shadow-2xl">
                    <div class="mb-8 flex flex-col items-center text-center">
                        <div class="mb-4 flex h-28 w-28 items-center justify-center rounded-full border-2 border-[#C5A059] bg-[#C5A059]/10">
                            <span class="text-3xl font-bold tracking-widest text-[#C5A059]">RDM</span>
                        </div>
                        <h1 class="text-2xl font-bold text-white">Panel Administrativo</h1>
                        <p class="mt-1 text-sm text-[#C5A059]">Apartamentos Reserva del Mar</p>
                    </div>

                    <?php if ($flash['type'] !== '' && in_array($flash['type'], ['success', 'error'], true)): ?>
                        <div class="mb-6 rounded-lg border p-3 text-sm <?= $flash['type'] === 'success' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-red-500/40 bg-red-500/10 text-red-300' ?>">
                            <?= admin_escape($flash['message']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <input type="hidden" name="action" value="login_submit">
                        <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Correo electrónico</label>
                            <input id="email" name="email" type="email" required
                                   value="<?= admin_escape((string) ($_POST['email'] ?? '')) ?>"
                                   class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"
                                   placeholder="admin@reservadelmar.com">
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-300">Contraseña</label>
                            <input id="password" name="password" type="password" required
                                   class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"
                                   placeholder="••••••••">
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-[#C5A059] px-6 py-3 text-sm font-bold text-[#0F172A] transition hover:bg-[#D4B26D]">
                            Iniciar sesión
                        </button>
                    </form>

                    <p class="mt-6 text-center text-xs text-slate-500">
                        Reserva del Mar Apartamentos · By Nestor Echeverry
                    </p>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    return;
}

$panelView = 'list';
$editing = null;
$editingId = 0;
$selectedAmenidadIds = [];
$amenidades = [];
$apartments = [];
$amenidadesPorApartamento = [];

if (isset($_GET['nuevo'])) {
    $panelView = 'new';
} elseif (isset($_GET['edit'])) {
    $panelView = 'edit';
    $editingId = (int) $_GET['id'];
} elseif (isset($_GET['panel']) && (string) $_GET['panel'] === 'amenidades') {
    $panelView = 'amenidades';
}

if ($panelView === 'edit' && $editingId > 0) {
    $statement = $pdo->prepare('SELECT * FROM apartamentos WHERE id = ? LIMIT 1');
    $statement->execute([$editingId]);
    $editing = $statement->fetch();
}

if ($panelView === 'list') {
    $statement = $pdo->query(
        'SELECT a.id, a.codigo_apartamento, a.titulo, a.ubicacion, a.huespedes, a.habitaciones, a.banos, a.camas, a.imagen_principal, a.galeria, a.estado, a.fecha_creacion, u.nombre AS actualizado_por_nombre
         FROM apartamentos a
         LEFT JOIN usuarios u ON u.id = a.actualizado_por
         ORDER BY a.fecha_creacion DESC, a.id DESC'
    );
    $apartments = $statement->fetchAll();

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
}

$amenidades = $pdo->query('SELECT id, nombre FROM amenidades ORDER BY nombre ASC')->fetchAll();

if ($panelView === 'edit' && $editing !== null) {
    $selectedAmenidadIds = [];
    $statement = $pdo->prepare(
        'SELECT aa.amenidad_id, am.nombre
         FROM apartamento_amenidades aa
         INNER JOIN amenidades am ON am.id = aa.amenidad_id
         WHERE aa.apartamento_id = ?'
    );
    $statement->execute([$editingId]);
    foreach ($statement->fetchAll() as $row) {
        $selectedAmenidadIds[] = (int) $row['amenidad_id'];
    }
}

$form = [
    'id' => 0,
    'titulo' => '',
    'ubicacion' => '',
    'huespedes' => 1,
    'habitaciones' => 1,
    'banos' => 1,
    'camas' => 1,
    'imagen_principal' => '',
    'galeria' => '',
    'descripcion' => '',
    'visible' => 1,
    'amenidades' => [],
];

if (!empty($old)) {
    $form = $old;
} elseif ($panelView === 'new') {
    // Valores por defecto para un nuevo registro.
} elseif ($panelView === 'edit' && $editing !== null) {
    $form['id'] = (int) $editing['id'];
    $form['titulo'] = (string) $editing['titulo'];
    $form['ubicacion'] = (string) $editing['ubicacion'];
    $form['huespedes'] = (int) $editing['huespedes'];
    $form['habitaciones'] = (int) $editing['habitaciones'];
    $form['banos'] = (int) $editing['banos'];
    $form['camas'] = (int) $editing['camas'];
    $form['imagen_principal'] = (string) ($editing['imagen_principal'] ?? '');
    $form['galeria'] = (string) ($editing['galeria'] ?? '');
    $form['descripcion'] = (string) ($editing['descripcion'] ?? '');
    $form['visible'] = (int) $editing['estado'];
    $form['amenidades'] = $selectedAmenidadIds;
}

if ($action === 'guardar_apartamento') {
    admin_require_login();
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $editingIdFromPost = (int) ($_POST['id'] ?? 0);
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $ubicacion = (string) ($_POST['ubicacion'] ?? '');
    $huespedes = admin_int_positive((string) ($_POST['huespedes'] ?? ''), 1);
    $habitaciones = admin_int_nonnegative((string) ($_POST['habitaciones'] ?? ''), 1);
    $banos = admin_int_nonnegative((string) ($_POST['banos'] ?? ''), 1);
    $camas = admin_int_nonnegative((string) ($_POST['camas'] ?? ''), 1);
    $imagenPrincipal = admin_normalizar_url((string) ($_POST['imagen_principal'] ?? ''));
    $galleryPaths = admin_galeria_rutas((string) ($_POST['galeria'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $visible = isset($_POST['visible']) ? 1 : 0;
    $amenidadIds = [];
    foreach ((array) ($_POST['amenidades'] ?? []) as $amenidadId) {
        $amenidadIds[] = (int) $amenidadId;
    }
    $amenidadIds = array_values(array_filter($amenidadIds, static function ($amenidadId) use ($amenidades) {
        $validAmenidadIds = array_map(static function ($amenidad) {
            return (int) $amenidad['id'];
        }, $amenidades);

        return in_array($amenidadId, $validAmenidadIds, true);
    }));

    $validationErrors = [];
    if ($titulo === '') {
        $validationErrors[] = 'El título del apartamento es obligatorio.';
    }
    if (!in_array($ubicacion, ['Playa Salguero', 'Bello Horizonte', 'Rodadero Sur'], true)) {
        $validationErrors[] = 'La ubicación seleccionada no es válida.';
    }
    if ($huespedes < 1) {
        $validationErrors[] = 'La capacidad de huéspedes debe ser mayor a cero.';
    }

    if ($validationErrors !== []) {
        $_SESSION['admin_errors'] = $validationErrors;
        $oldForm = [
            'id' => $editingIdFromPost,
            'titulo' => $titulo,
            'ubicacion' => $ubicacion,
            'huespedes' => $huespedes,
            'habitaciones' => $habitaciones,
            'banos' => $banos,
            'camas' => $camas,
            'imagen_principal' => $imagenPrincipal,
            'galeria' => (string) ($_POST['galeria'] ?? ''),
            'descripcion' => $descripcion,
            'visible' => $visible,
            'amenidades' => $amenidadIds,
        ];
        $_SESSION['admin_old'] = $oldForm;
        $_SESSION['admin_old_edit'] = $editingIdFromPost;

        admin_redirigir($editingIdFromPost > 0 ? '/admin.php?panel=edit&id=' . $editingIdFromPost : '/admin.php?panel=nuevo');
    }

    $codigo = trim((string) ($_POST['codigo_apartamento'] ?? ''));
    if ($editingIdFromPost <= 0 && $codigo === '') {
        $codigo = 'APT-' . strtoupper(substr(md5($titulo . '|' . microtime()), 0, 6));
    }
    $execute = static function () use ($pdo, $editingIdFromPost, $codigo, $titulo, $ubicacion, $huespedes, $habitaciones, $banos, $camas, $imagenPrincipal, $galleryPaths, $descripcion, $visible, $amenidadIds, $adminId): void {
        if ($editingIdFromPost > 0) {
            $statement = $pdo->prepare(
                'UPDATE apartamentos
                 SET titulo = ?, ubicacion = ?, huespedes = ?, habitaciones = ?, banos = ?, camas = ?, imagen_principal = ?, galeria = ?, descripcion = ?, estado = ?, actualizado_por = ?, actualizado_en = NOW()
                 WHERE id = ?'
            );
            $statement->execute([$titulo, $ubicacion, $huespedes, $habitaciones, $banos, $camas, $imagenPrincipal, json_encode($galleryPaths, JSON_UNESCAPED_SLASHES), $descripcion, $visible, $adminId, $editingIdFromPost]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO apartamentos (codigo_apartamento, titulo, ubicacion, huespedes, habitaciones, banos, camas, imagen_principal, galeria, descripcion, estado, actualizado_por, actualizado_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $statement->execute([$codigo, $titulo, $ubicacion, $huespedes, $habitaciones, $banos, $camas, $imagenPrincipal, json_encode($galleryPaths, JSON_UNESCAPED_SLASHES), $descripcion, $visible, $adminId]);
        }

        $apartmentId = $editingIdFromPost > 0 ? $editingIdFromPost : (int) $pdo->lastInsertId();
        $statement = $pdo->prepare('DELETE FROM apartamento_amenidades WHERE apartamento_id = ?');
        $statement->execute([$apartmentId]);
        $statement = $pdo->prepare('INSERT INTO apartamento_amenidades (apartamento_id, amenidad_id) VALUES (?, ?)');
        foreach ($amenidadIds as $amenidadId) {
            $statement->execute([$apartmentId, $amenidadId]);
        }
    };

    try {
        if (
            isset($_FILES['imagen_principal_file'])
            && is_array($_FILES['imagen_principal_file'])
            && isset($_FILES['imagen_principal_file']['tmp_name'])
            && is_uploaded_file((string) $_FILES['imagen_principal_file']['tmp_name'])
        ) {
            $imagenPrincipal = admin_guardar_imagen($_FILES['imagen_principal_file']);
        }

        if (isset($_FILES['galeria_files']) && is_array($_FILES['galeria_files']) && isset($_FILES['galeria_files'][0]['tmp_name'])) {
            $galleryFiles = $_FILES['galeria_files'];
            for ($galleryIndex = 0; $galleryIndex < count($galleryFiles); $galleryIndex++) {
                if (!isset($galleryFiles[$galleryIndex]['tmp_name']) || !is_uploaded_file((string) $galleryFiles[$galleryIndex]['tmp_name'])) {
                    continue;
                }
                $uploadedGalleryPath = admin_guardar_imagen($galleryFiles[$galleryIndex]);
                if ($uploadedGalleryPath !== '') {
                    $galleryPaths[] = $uploadedGalleryPath;
                }
            }
        }

        $execute();
        $executeId = $editingIdFromPost > 0 ? $editingIdFromPost : (int) $pdo->lastInsertId();
        registrarAuditoria($pdo, $adminId, $editingIdFromPost > 0 ? 'editar_apartamento' : 'crear_apartamento', ($editingIdFromPost > 0 ? 'Edición de apartamento ' : 'Creación de apartamento ') . $titulo);
        unset($_SESSION['admin_errors'], $_SESSION['admin_old'], $_SESSION['admin_old_edit']);
        admin_set_flash('success', 'Apartamento guardado correctamente.');
        admin_redirigir($editingIdFromPost > 0 ? '/admin.php?panel=edit&id=' . $executeId : '/admin.php');
    } catch (Throwable $exception) {
        $_SESSION['admin_errors'] = ['error' => 'No fue posible guardar el apartamento. Verifique las imágenes y los datos.'];
        $oldForm = [
            'id' => $editingIdFromPost,
            'titulo' => $titulo,
            'ubicacion' => $ubicacion,
            'huespedes' => $huespedes,
            'habitaciones' => $habitaciones,
            'banos' => $banos,
            'camas' => $camas,
            'imagen_principal' => $imagenPrincipal,
            'galeria' => (string) ($_POST['galeria'] ?? ''),
            'descripcion' => $descripcion,
            'visible' => $visible,
            'amenidades' => $amenidadIds,
        ];
        $_SESSION['admin_old'] = $oldForm;
        $_SESSION['admin_old_edit'] = $editingIdFromPost;
        admin_redirigir($editingIdFromPost > 0 ? '/admin.php?panel=edit&id=' . $editingIdFromPost : '/admin.php?panel=nuevo');
    }
}

if ($action === 'toggle_estado') {
    admin_require_login();
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $targetId = (int) ($_POST['id'] ?? 0);
    $statement = $pdo->prepare(
        'UPDATE apartamentos
         SET estado = 1 - estado, actualizado_por = ?, actualizado_en = NOW()
         WHERE id = ?'
    );
    $statement->execute([$adminId, $targetId]);
    registrarAuditoria($pdo, $adminId, 'cambiar_estado_apartamento', 'Cambio de visibilidad del apartamento ID ' . $targetId);
    admin_set_flash('success', 'Visibilidad actualizada correctamente.');
    admin_redirigir('/admin.php');
}

if ($action === 'eliminar_apartamento') {
    admin_require_login();
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $targetId = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM apartamentos WHERE id = ?')->execute([$targetId]);
    registrarAuditoria($pdo, $adminId, 'eliminar_apartamento', 'Eliminación del apartamento ID ' . $targetId);
    admin_set_flash('success', 'Apartamento eliminado correctamente.');
    admin_redirigir('/admin.php');
}

if ($action === 'crear_amenidad') {
    admin_require_login();
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    if ($nombre !== '') {
        $pdo->prepare('INSERT INTO amenidades (nombre) VALUES (?)')->execute([$nombre]);
        registrarAuditoria($pdo, $adminId, 'crear_amenidad', 'Creación de amenidad ' . $nombre);
        admin_set_flash('success', 'Amenidad creada correctamente.');
    } else {
        admin_set_flash('error', 'El nombre de la amenidad es obligatorio.');
    }
    admin_redirigir('/admin.php?panel=amenidades');
}

if ($action === 'eliminar_amenidad') {
    admin_require_login();
    if (!admin_validate_csrf()) {
        admin_set_flash('error', 'La sesión o el token de seguridad no son válidos.');
        admin_redirigir('/admin.php');
    }

    $amenidadId = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM amenidades WHERE id = ?')->execute([$amenidadId]);
    registrarAuditoria($pdo, $adminId, 'eliminar_amenidad', 'Eliminación de amenidad ID ' . $amenidadId);
    admin_set_flash('success', 'Amenidad eliminada correctamente.');
    admin_redirigir('/admin.php?panel=amenidades');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración | Apartamentos Reserva del Mar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0F172A] text-slate-200">
    <header class="border-b border-[#C5A059]/30 bg-[#0F172A]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <div>
                <h1 class="text-xl font-bold text-white">Panel Administrativo</h1>
                <p class="text-sm text-[#C5A059]">Apartamentos Reserva del Mar</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-300"><?= admin_escape((string) ($_SESSION['admin_email'] ?? '')) ?></span>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                    <button type="submit" class="rounded-lg border border-[#C5A059] px-4 py-2 text-sm font-bold text-[#C5A059] transition hover:bg-[#C5A059] hover:text-[#0F172A]">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-white">Apartamentos</h2>
            <div class="flex items-center gap-3">
                <a href="/admin.php?panel=nuevo" class="rounded-lg bg-[#C5A059] px-4 py-2 text-sm font-bold text-[#0F172A] transition hover:bg-[#D4B26D]">+ Nuevo apartamento</a>
                <a href="/admin.php?panel=amenidades" class="rounded-lg border border-[#C5A059] px-4 py-2 text-sm font-bold text-[#C5A059] transition hover:bg-[#C5A059] hover:text-[#0F172A]">Amenidades</a>
            </div>
        </div>

        <?php if ($flash['type'] !== '' && in_array($flash['type'], ['success', 'error'], true)): ?>
            <div class="mb-6 rounded-lg border p-3 text-sm <?= $flash['type'] === 'success' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-red-500/40 bg-red-500/10 text-red-300' ?>">
                <?= admin_escape($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($panelView === 'list'): ?>
            <div class="overflow-hidden rounded-2xl border border-[#C5A059]/30 bg-[#0F172A]">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-[#C5A059]/30 bg-[#0F172A] text-left text-slate-300">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Código</th>
                                <th class="px-4 py-3">Título</th>
                                <th class="px-4 py-3">Ubicación</th>
                                <th class="px-4 py-3">Huéspedes</th>
                                <th class="px-4 py-3">Hab.</th>
                                <th class="px-4 py-3">Baños</th>
                                <th class="px-4 py-3">Camas</th>
                                <th class="px-4 py-3">Amenidades</th>
                                <th class="px-4 py-3">Visible</th>
                                <th class="px-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C5A059]/20 text-slate-300">
                            <?php if ($apartments === []): ?>
                                <tr>
                                    <td colspan="11" class="px-4 py-6 text-center text-slate-400">No hay apartamentos registrados.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($apartments as $apartment): ?>
                                <tr>
                                    <td class="px-4 py-3 font-bold text-[#C5A059]"><?= (int) $apartment['id'] ?></td>
                                    <td class="px-4 py-3"><?= admin_escape((string) $apartment['codigo_apartamento']) ?></td>
                                    <td class="px-4 py-3"><?= admin_escape((string) $apartment['titulo']) ?></td>
                                    <td class="px-4 py-3"><?= admin_escape((string) $apartment['ubicacion']) ?></td>
                                    <td class="px-4 py-3"><?= (int) $apartment['huespedes'] ?></td>
                                    <td class="px-4 py-3"><?= (int) $apartment['habitaciones'] ?></td>
                                    <td class="px-4 py-3"><?= (int) $apartment['banos'] ?></td>
                                    <td class="px-4 py-3"><?= (int) $apartment['camas'] ?></td>
                                    <td class="px-4 py-3 text-xs">
                                        <?php
                                            $names = $amenidadesPorApartamento[(int) $apartment['id']] ?? [];
                                            echo admin_escape(implode(', ', $names));
                                        ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-1 text-xs font-bold <?= (int) $apartment['estado'] === 1 ? 'border border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border border-slate-500/40 bg-slate-500/10 text-slate-300' ?>">
                                            <?= (int) $apartment['estado'] === 1 ? 'Visible' : 'Oculto' ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a href="/admin.php?panel=edit&id=<?= (int) $apartment['id'] ?>" class="font-bold text-[#C5A059] hover:text-[#D4B26D]">Editar</a>
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="toggle_estado">
                                                <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                                                <input type="hidden" name="id" value="<?= (int) $apartment['id'] ?>">
                                                <button type="submit" class="rounded-lg border border-[#C5A059] px-3 py-1 text-xs font-bold text-[#C5A059] transition hover:bg-[#C5A059] hover:text-[#0F172A]">
                                                    <?= (int) $apartment['estado'] === 1 ? 'Ocultar' : 'Mostrar' ?>
                                                </button>
                                            </form>
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="eliminar_apartamento">
                                                <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                                                <input type="hidden" name="id" value="<?= (int) $apartment['id'] ?>">
                                                <button type="submit" class="rounded-lg border border-red-500/50 px-3 py-1 text-xs font-bold text-red-300 transition hover:bg-red-500/10">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($panelView === 'new' || $panelView === 'edit'): ?>
            <div class="rounded-2xl border border-[#C5A059]/30 bg-[#0F172A] p-8">
                <h1 class="mb-6 text-2xl font-bold text-white"><?= $panelView === 'new' ? 'Registrar Nuevo Apartamento' : 'Editar Apartamento' ?></h1>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
                        <?php foreach ($errors as $error): ?>
                            <p><?= admin_escape((string) $error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <input type="hidden" name="action" value="guardar_apartamento">
                    <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Título *</label>
                        <input type="text" name="titulo" required value="<?= admin_escape((string) $form['titulo']) ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"
                               placeholder="Habitación Doble Vista Mar">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Ubicación *</label>
                        <select name="ubicacion" required
                                class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40">
                            <option value="">Seleccionar ubicación</option>
                            <?php foreach (['Playa Salguero', 'Bello Horizonte', 'Rodadero Sur'] as $location): ?>
                                <option value="<?= admin_escape($location) ?>" <?= (string) $form['ubicacion'] === $location ? 'selected' : '' ?>><?= admin_escape($location) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Capacidad de huéspedes</label>
                        <input type="number" name="huespedes" min="1" value="<?= (int) $form['huespedes'] ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Habitaciones</label>
                        <input type="number" name="habitaciones" min="0" value="<?= (int) $form['habitaciones'] ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Baños</label>
                        <input type="number" name="banos" min="0" value="<?= (int) $form['banos'] ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Camas</label>
                        <input type="number" name="camas" min="0" value="<?= (int) $form['camas'] ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Imagen principal (URL)</label>
                        <input type="text" name="imagen_principal" value="<?= admin_escape((string) $form['imagen_principal']) ?>"
                               class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"
                               placeholder="https://ejemplo.com/foto.jpg">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Subir imagen principal</label>
                        <input type="file" name="imagen_principal_file" accept="image/*" class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">Galería secundaria (una URL por línea)</label>
                        <textarea name="galeria" rows="6" class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"><?= admin_escape((string) $form['galeria']) ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">Subir galería secundaria</label>
                        <input type="file" name="galeria_files[]" accept="image/*" multiple class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">Descripción</label>
                        <textarea name="descripcion" rows="6" class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40"><?= admin_escape((string) $form['descripcion']) ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3">
                            <input type="checkbox" name="visible" value="1" <?= (int) $form['visible'] === 1 ? 'checked' : '' ?> class="h-4 w-4 accent-[#C5A059]">
                            <span class="text-sm font-medium text-slate-300">Visible en el sitio web</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">Amenidades</label>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                            <?php foreach ($amenidades as $amenidad): ?>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-600/60 bg-[#0F172A] px-3 py-2">
                                    <input type="checkbox" name="amenidades[]" value="<?= (int) $amenidad['id'] ?>" <?= in_array((int) $amenidad['id'], $form['amenidades'], true) ? 'checked' : '' ?> class="h-4 w-4 accent-[#C5A059]">
                                    <span class="text-sm text-slate-300"><?= admin_escape((string) $amenidad['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 md:col-span-2">
                        <button type="submit" class="rounded-lg bg-[#C5A059] px-6 py-3 text-sm font-bold text-[#0F172A] transition hover:bg-[#D4B26D]">
                            Guardar apartamento
                        </button>
                        <a href="/admin.php" class="rounded-lg border border-slate-500 px-6 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-500/10">Cancelar</a>
                    </div>
                </form>
            </div>
        <?php elseif ($panelView === 'amenidades'): ?>
            <div class="rounded-2xl border border-[#C5A059]/30 bg-[#0F172A] p-8">
                <h1 class="mb-6 text-2xl font-bold text-white">Amenidades</h1>

                <form method="POST" action="" class="mb-8 grid max-w-md gap-4 md:grid-cols-[1fr_auto]">
                    <input type="hidden" name="action" value="crear_amenidad">
                    <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Nombre de la amenidad</label>
                        <input type="text" name="nombre" class="w-full rounded-lg border border-slate-600/60 bg-[#0F172A] px-4 py-3 text-white outline-none focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40" placeholder="Piscina">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="rounded-lg bg-[#C5A059] px-6 py-3 text-sm font-bold text-[#0F172A] transition hover:bg-[#D4B26D]">Crear amenidad</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-[#C5A059]/30 bg-[#0F172A] text-left text-slate-300">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C5A059]/20 text-slate-300">
                            <?php foreach ($amenidades as $amenidad): ?>
                                <tr>
                                    <td class="px-4 py-3 font-bold text-[#C5A059]"><?= (int) $amenidad['id'] ?></td>
                                    <td class="px-4 py-3"><?= admin_escape((string) $amenidad['nombre']) ?></td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="">
                                            <input type="hidden" name="action" value="eliminar_amenidad">
                                            <input type="hidden" name="csrf_token" value="<?= admin_csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= (int) $amenidad['id'] ?>">
                                            <button type="submit" class="rounded-lg border border-red-500/50 px-3 py-1 text-xs font-bold text-red-300 transition hover:bg-red-500/10">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
