<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Si ya existe una sesión activa, redirige al panel
if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Por favor ingrese su correo electrónico y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nombre, email, password_hash, rol, estado FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && (int) $user['estado'] === 1 && password_verify($password, $user['password_hash'])) {
            // Credenciales validas: crear sesion
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_rol'] = $user['rol'];

            registrarAuditoria($pdo, (int) $user['id'], 'login', 'Inicio de sesion exitoso');
            header('Location: /admin/dashboard.php');
            exit;
        }

        if ($user) {
            registrarAuditoria($pdo, (int) $user['id'], 'login_failed', 'Intento de inicio de sesion fallido');
        }

        $error = 'Credenciales invalidas o cuenta inactiva.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Reserva del Mar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0F172A] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="bg-[#0F172A] border border-[#C5A059]/40 rounded-2xl p-8 shadow-2xl">
            <!-- Espacio para el logotipo corporativo -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-28 h-28 rounded-full bg-[#C5A059]/10 border-2 border-[#C5A059] flex items-center justify-center mb-4">
                    <span class="text-[#C5A059] text-3xl font-bold tracking-widest">RDM</span>
                </div>
                <h1 class="text-2xl font-bold text-white">Panel Administrativo</h1>
                <p class="text-[#C5A059] text-sm mt-1">Apartamentos Reserva del Mar</p>
                <p class="text-slate-400 text-xs mt-2">Gestion de apartamentos y operacion</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mb-6 p-3 rounded-lg bg-red-500/10 border border-red-500/40 text-red-300 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Correo electrónico</label>
                    <input id="email" name="email" type="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white placeholder-slate-500 focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"
                           placeholder="admin@reservadelmar.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-lg bg-[#0F172A] border border-slate-600/60 px-4 py-3 text-white placeholder-slate-500 focus:border-[#C5A059] focus:ring-2 focus:ring-[#C5A059]/40 outline-none"
                           placeholder="••••••••">
                </div>
                <button type="submit"
                        class="w-full rounded-lg bg-[#C5A059] px-6 py-3 text-sm font-bold text-[#0F172A] hover:bg-[#D4B26D] transition">
                    Iniciar sesión
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500">Reserva del Mar Apartamentos - By Nestor Echeverry</p>
        </div>
    </div>
</body>
</html>
