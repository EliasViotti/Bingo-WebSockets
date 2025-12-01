<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bingo Online</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,800" rel="stylesheet" />
</head>

<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white p-12 rounded-3xl text-center shadow-2xl">
        <h1 class="text-indigo-500 text-5xl font-bold my-8">🎲 Bingo Online 🎲</h1>
        <p class="text-gray-600 text-lg my-4.5">Elige una opción para comenzar</p>

        <form action="{{ route('bingo.juego.crear') }}" method="POST" class="my-7.5">
            @csrf
            <button type="submit"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-none px-10 py-4 text-lg rounded-full cursor-pointer mx-2.5 inline-block hover:shadow-xl hover:-translate-y-1 transition-all">
                🎰 Crear Nuevo Juego
            </button>
        </form>
    </div>
</body>

</html>
