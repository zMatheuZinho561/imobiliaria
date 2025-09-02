<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-indigo-600">Registrar</h1>
        <?php if (!empty($erro)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $erro; ?></div>
        <?php endif; ?>
        <form method="POST" action="process_register.php" class="space-y-4">
            <input type="hidden" name="acao" value="registrar">
            <div>
                <label class="block font-medium">Nome</label>
                <input type="text" name="nome" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-medium">Senha</label>
                <input type="password" name="senha" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg">Registrar</button>
        </form>
    </div>
</body>
</html>