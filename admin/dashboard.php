<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

$stmt = $pdo->query(
    'SELECT a.*, u.nombre AS actualizado_por_nombre
     FROM apartamentos a
     LEFT JOIN usuarios u ON u.id = a.actualizado_por
     ORDER BY a.torre ASC, a.codigo_apartamento ASC'
);
$apartamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Apartamentos Reserva del Mar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0F172A] text-slate-200">
    <header class="bg-[#0F172A] border-b border-[#C5A059]/30">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">Panel de Administración</h1>
                <p class="text-[#C5A059] text-xs">Apartamentos Reserva del Mar</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-300"><?= htmlspecialchars((string) ($_SESSION['admin_email'] ?? '')) ?></span>
                <a href="/admin/auth/logout.php"
                   class="px-4 py-2 rounded-lg border border-[#C5A059] text-[#C5A059] text-sm font-bold hover:bg-[#C5A059] hover:text-[#0F172A] transition">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-white">Apartamentos registrados</h2>
            <a href="/admin/crear.php"
               class="px-4 py-2 rounded-lg bg-[#C5A059] text-[#0F172A] text-sm font-bold hover:bg-[#D4B26D] transition">+ Nuevo apartamento</a>
        </div>

        <div class="bg-[#0F172A] border border-[#C5A059]/30 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#0F172A] border-b border-[#C5A059]/30">
                        <tr class="text-left text-slate-300">
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">Torre</th>
                            <th class="px-4 py-3">Capacidad</th>
                            <th class="px-4 py-3">Hab.</th>
                            <th class="px-4 py-3">Baños</th>
                            <th class="px-4 py-3">Visible</th>
                            <th class="px-4 py-3">Actualizado</th>
                            <th class="px-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C5A059]/20">
                        <?php if (empty($apartamentos)): ?>
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-slate-400">No hay apartamentos registrados.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($apartamentos as $a): ?>
                            <tr class="text-slate-300">
                                <td class="px-4 py-3 font-bold text-[#C5A059]"><?= htmlspecialchars((string) $a['codigo_apartamento']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars((string) $a['titulo']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars((string) $a['torre']) ?></td>
                                <td class="px-4 py-3"><?= (int) $a['capacidad_huespedes'] ?></td>
                                <td class="px-4 py-3"><?= (int) $a['num_habitaciones'] ?></td>
                                <td class="px-4 py-3"><?= (int) $a['num_banos'] ?></td>
                                <td class="px-4 py-3">
                                    <?php if ((int) $a['visible'] === 1): ?>
                                        <span class="px-2 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 text-xs font-bold">Visible</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full bg-slate-500/10 border border-slate-500/40 text-slate-300 text-xs font-bold">Oculto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs"><?= htmlspecialchars((string) ($a['actualizado_en'] ?? '-')) ?> · <?= htmlspecialchars((string) ($a['actualizado_por_nombre'] ?? '-')) ?></td>
                                <td class="px-4 py-3">
                                    <a href="/admin/editar.php?codigo=<?= urlencode((string) $a['codigo_apartamento']) ?>" class="text-[#C5A059] hover:text-[#D4B26D] font-bold">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
