
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-indigo-600">Login</h1>
        <?php if (!empty($erro)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $erro; ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION["msg"])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?php echo $_SESSION["msg"]; unset($_SESSION["msg"]); ?></div>
        <?php endif; ?>
        <form method="POST" action="login_ex.php" class="space-y-4">
            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300">
            </div>
            <div>
                <label class="block font-medium">Senha</label>
                <input type="password" name="senha" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">Entrar</button>
        </form>
        <p class="mt-4 text-center text-sm">Não tem conta? <a href="register.php" class="text-indigo-600 hover:underline">Registrar</a></p>
    </div>
</body>
</html>