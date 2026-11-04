<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/config/db.php';

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim((string) ($_POST['codigo_apartamento'] ?? ''));
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $torre = (string) ($_POST['torre'] ?? '');
    $capacidad = (int) ($_POST['capacidad_huespedes'] ?? 2);
    $numHab = (int) ($_POST['num_habitaciones'] ?? 1);
    $numBanos = (int) ($_POST['num_banos'] ?? 1);
    $imagen = trim((string) ($_POST['imagen_principal'] ?? ''));
    $galeriaRaw = trim((string) ($_POST['galeria_imagenes'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $visible = isset($_POST['visible']) ? 1 : 0;
    $adminId = (int) ($_SESSION['admin_id'] ?? 1);

    if ($codigo === '' || $titulo === '') {
        $error = 'El código y el título son obligatorios.';
    } elseif (!in_array($torre, ['Playa Salguero', 'Rodadero Sur', 'Bello Horizonte'], true)) {
        $error = 'La torre seleccionada no es válida.';
    }

    if ($error === '') {
        $galeria = $galeriaRaw;
        if ($galeriaRaw !== '') {
            $decoded = json_decode($galeriaRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $galeria = $galeriaRaw;
            } else {
                $parts = array_map('trim', explode(',', $galeriaRaw));
                $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
                $galeria = json_encode($parts);
            }
        }

        $stmt = $pdo->prepare(
            'INSERT INTO apartamentos (codigo_apartamento, titulo, torre, capacidad_huespedes, num_habitaciones, num_banos, imagen_principal, galeria_imagenes, descripcion, visible, actualizado_por, actualizado_en)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$codigo, $titulo, $torre, $capacidad, $numHab, $numBanos, $imagen, $galeria, $descripcion, $visible, $adminId]);

        registrarAuditoria($pdo, $adminId, 'crear_apartamento', 'Creacion del apartamento ' . $codigo);

        header('Location: /admin/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Apartamento | Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0F172A] text-slate-200">
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-[#0F172A] border border-[#C5A059]/30 rounded-2xl p-8">
            <h1 class="text-2xl font-bold text-white mb-6">Registrar Nuevo Apartamento</h1>

            <?php if ($error !== ''): ?>
                <div class="mb-6 p-3 rounded-lg bg-red-500/10 border border-red-500/40 text-red-300 text-sm"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Código del apartamento *</label>
                    <input type="text" name="codigo_apartamento" required
                           value="<?= htmlspecialchars((string) ($_POST['codigo_apartamento'] ?? '')) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"
                           placeholder="PSA-302">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Título *</label>
                    <input type="text" name="titulo" required
                           value="<?= htmlspecialchars((string) ($_POST['titulo'] ?? '')) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"
                           placeholder="Habitación Doble Vista Mar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Torre *</label>
                    <select name="torre" required
                            class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none">
                        <option value="">Seleccionar torre</option>
                        <option value="Playa Salguero" <?= (($POST['torre'] ?? '') === 'Playa Salguero') ? 'selected' : '' ?>>Playa Salguero</option>
                        <option value="Rodadero Sur" <?= (($POST['torre'] ?? '') === 'Rodadero Sur') ? 'selected' : '' ?>>Rodadero Sur</option>
                        <option value="Bello Horizonte" <?= (($POST['torre'] ?? '') === 'Bello Horizonte') ? 'selected' : '' ?>>Bello Horizonte</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Capacidad de huéspedes</label>
                    <input type="number" name="capacidad_huespedes" min="1" value="<?= htmlspecialchars((string) ($_POST['capacidad_huespedes'] ?? 2)) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Número de habitaciones</label>
                    <input type="number" name="num_habitaciones" min="0" value="<?= htmlspecialchars((string) ($_POST['num_habitaciones'] ?? 1)) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Número de baños</label>
                    <input type="number" name="num_banos" min="0" value="<?= htmlspecialchars((string) ($_POST['num_banos'] ?? 1)) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Imagen principal (URL)</label>
                    <input type="text" name="imagen_principal"
                           value="<?= htmlspecialchars((string) ($_POST['imagen_principal'] ?? '')) ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"
                           placeholder="https://...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Galería (JSON o rutas separadas por comas)</label>
                    <textarea name="galeria_imagenes" rows="3"
                              class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"><?= htmlspecialchars((string) ($_POST['galeria_imagenes'] ?? '')) ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="4"
                              class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"><?= htmlspecialchars((string) ($_POST['descripcion'] ?? '')) ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-300">
                        <input type="checkbox" name="visible" value="1" <?= (isset($_POST['visible']) || empty($error)) ? 'checked' : '' ?> class="w-4 h-4 accent-[#C5A059]">
                        Visible en el sitio web
                    </label>
                </div>

                <div class="md:col-span-2 flex items-center gap-4">
                    <button type="submit"
                            class="px-6 py-3 rounded-lg bg-[#C5A059] text-[#0F172A] text-sm font-bold hover:bg-[#D4B26D] transition">Guardar apartamento</button>
                    <a href="/admin/dashboard.php"
                       class="px-6 py-3 rounded-lg border border-slate-500 text-slate-300 text-sm font-bold hover:bg-slate-500/10 transition">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
