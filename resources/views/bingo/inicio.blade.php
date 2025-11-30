<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bingo Online</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[linear-gradient(135deg,#667eea_0%,#764ba2_100%)] min-h-screen flex items-center justify-center font-sans">

    <div class="bg-white p-12 rounded-[20px] text-center shadow-[0_20px_60px_rgba(0,0,0,0.3)] max-w-lg w-full">
        <h1 class="text-[#667eea] text-5xl font-bold mb-4">🎲 Bingo Online 🎲</h1>
        <p class="text-gray-500 text-lg mb-8">Elige una opción para comenzar</p>

        <form action="{{ route('bingo.juego.crear') }}" method="POST" class="my-8">
            @csrf
            <button type="submit"
                class="bg-[linear-gradient(135deg,#667eea_0%,#764ba2_100%)] text-white border-none py-4 px-10 text-lg rounded-full cursor-pointer m-2 inline-block transition-transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                🎰 Crear Nuevo Juego
            </button>
        </form>
    </div>

</body>

</html>
