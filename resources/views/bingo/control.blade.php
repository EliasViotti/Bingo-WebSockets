<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Control del Bingo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,800" rel="stylesheet" />
</head>

<body class="bg-gradient-to-br from-teal-500 to-green-400 min-h-screen p-5">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-3xl p-8 shadow-2xl mb-5 text-center">
            <h1 class="text-teal-600 text-5xl font-bold m-0">🎰 CONTROL DE BINGO 🎰</h1>
            <p class="text-xl text-gray-600 my-2.5">
                Código del Juego: <strong>{{ $juego->codigo }}</strong>
            </p>
            <p class="text-gray-400">Comparte este código con los jugadores para que se unan</p>
            <div class="mt-4">
                <a href="/bingo/tarjeta/{{ $juego->codigo }}" target="_blank"
                    class="inline-block bg-indigo-500 text-white px-5 py-2.5 rounded-full no-underline hover:bg-indigo-600 transition">
                    🎫 Abrir Nueva Tarjeta
                </a>
            </div>
        </div>

        <!-- Panel Principal -->
        <div class="bg-white rounded-3xl p-8 shadow-2xl mb-5">
            <div class="text-center">
                <h2 class="text-gray-800 text-2xl mb-5">Sorteo Actual</h2>

                <div id="bola-container" class="hidden">
                    <div id="bola-numero"
                        class="bola-gigante w-64 h-64 rounded-full bg-gradient-to-br from-pink-400 to-red-500 flex items-center justify-center text-9xl font-bold text-white mx-auto my-8 shadow-2xl">
                        ?
                    </div>
                </div>

                <div id="mensaje-inicial" class="py-12 text-gray-400 text-xl">
                    Presiona el botón para comenzar el sorteo
                </div>

                <button id="btn-sortear" onclick="sortearNumero()"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-none px-16 py-5 text-2xl font-bold rounded-full cursor-pointer transition-all shadow-lg hover:shadow-xl hover:-translate-y-1 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none">
                    🎲 SORTEAR NÚMERO
                </button>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-3 gap-5 my-5">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-5 rounded-2xl text-center">
                    <div>Números Sorteados</div>
                    <div id="total-sorteados" class="text-5xl font-bold my-2.5">0</div>
                    <div>de 100</div>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-5 rounded-2xl text-center">
                    <div>Último Número</div>
                    <div id="ultimo-stat" class="text-5xl font-bold my-2.5">-</div>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-5 rounded-2xl text-center">
                    <div>Estado</div>
                    <div id="estado-juego" class="text-2xl mt-5">
                        {{ $juego->estado === 'esperando' ? '⏳ Esperando' : ($juego->estado === 'jugando' ? '▶️ Jugando' : '✅ Finalizado') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Anuncio de Ganador -->
        <div id="ganador-anuncio"
            class="hidden bg-gradient-to-r from-green-500 to-lime-400 text-white p-8 rounded-2xl text-center text-2xl my-5">
            <h2 class="m-0 mb-2.5">🎉 ¡TENEMOS UN GANADOR! 🎉</h2>
            <p id="ganador-info" class="m-0 text-3xl"></p>
        </div>

        <!-- Panel de Números Sorteados -->
        <div class="bg-white rounded-3xl p-8 shadow-2xl">
            <h2 class="text-gray-800 text-2xl mb-5">Números Sorteados</h2>
            <div id="grid-sorteados" class="grid gap-2.5 mt-5"
                style="grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));">
                <!-- Los números se agregarán aquí dinámicamente -->
            </div>
            <p id="sin-numeros" class="text-center text-gray-400 py-8">
                Aún no se han sorteado números
            </p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // --- 1. DATOS INICIALES ---
            const codigoJuego = '{{ $juego->codigo }}';
            let numerosSorteados = @json($juego->numeros_sorteados ?? []);
            let juegoFinalizado = {{ $juego->estado === 'finalizado' ? 'true' : 'false' }};

            // --- 2. ESTADO INICIAL ---
            if (numerosSorteados.length > 0) {
                const ultimo = numerosSorteados[numerosSorteados.length - 1];
                mostrarNumeroSorteado(ultimo, false);
                actualizarGridSorteados();
            }

            if (juegoFinalizado) {
                bloquearJuego();
            }

            window.juegoConfig = {
                codigoJuego: codigoJuego,
                numerosSorteados: numerosSorteados,
                juegoFinalizado: juegoFinalizado,
            };

            // --- 3. CONEXIÓN WEBSOCKET ---
            const iniciarConexionControl = () => {
                if (!window.Echo) {
                    console.log("[Control] Esperando a Echo...");
                    setTimeout(iniciarConexionControl, 100);
                    return;
                }

                console.log("[Control] Escuchando canal:", `bingo.${codigoJuego}`);

                window.Echo.channel(`bingo.${codigoJuego}`)
                    .listen('.numero.sorteado', (data) => {
                        console.log("⚡ Evento Recibido:", data);

                        const nuevoNumero = parseInt(data.numero);

                        if (!numerosSorteados.includes(nuevoNumero)) {
                            numerosSorteados.push(nuevoNumero);
                            mostrarNumeroSorteado(nuevoNumero, true);
                            actualizarGridSorteados();
                        }

                        const btn = document.getElementById('btn-sortear');
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = '🎲 SORTEAR NÚMERO';
                        }
                    })
                    .listen('.juego.ganado', (data) => {
                        console.log("Ganador detectado:", data);
                        juegoFinalizado = true;
                        bloquearJuego();
                        alert(`¡JUEGO FINALIZADO!\nGanador: ${data.tarjeta.nombre}`);
                    });
            };

            iniciarConexionControl();

            // --- 4. FUNCIÓN SORTEAR ---
            window.sortearNumero = function() {
                if (juegoFinalizado) return;

                const boton = document.getElementById('btn-sortear');
                boton.disabled = true;
                boton.textContent = '🎲 Sorteando...';

                fetch(`/bingo/juego/${codigoJuego}/sortear`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Orden enviada. Esperando WebSocket...', data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al sortear número');
                        boton.disabled = false;
                        boton.textContent = '🎲 SORTEAR NÚMERO';
                    });
            };

            // --- 5. FUNCIONES DE UI ---
            function mostrarNumeroSorteado(numero, animar = true) {
                const msgInicial = document.getElementById('mensaje-inicial');
                if (msgInicial) msgInicial.classList.add('hidden');

                const bolaContainer = document.getElementById('bola-container');
                const bolaNumerElement = document.getElementById('bola-numero');

                if (bolaContainer) bolaContainer.classList.remove('hidden');

                if (bolaNumerElement) {
                    bolaNumerElement.textContent = numero;

                    if (animar) {
                        bolaNumerElement.style.animation = 'none';
                        bolaNumerElement.offsetHeight;
                        bolaNumerElement.style.animation = 'aparecer 0.5s ease';
                    }
                }

                const statUltimo = document.getElementById('ultimo-stat');
                const statTotal = document.getElementById('total-sorteados');
                const statEstado = document.getElementById('estado-juego');

                if (statUltimo) statUltimo.textContent = numero;
                if (statTotal) statTotal.textContent = numerosSorteados.length;
                if (statEstado) statEstado.textContent = '▶️ Jugando';
            }

            function actualizarGridSorteados() {
                const grid = document.getElementById('grid-sorteados');
                const sinNumeros = document.getElementById('sin-numeros');

                if (!grid) return;

                if (numerosSorteados.length === 0) {
                    if (sinNumeros) sinNumeros.classList.remove('hidden');
                    grid.innerHTML = '';
                    return;
                }

                if (sinNumeros) sinNumeros.classList.add('hidden');
                grid.innerHTML = '';

                numerosSorteados.forEach((numero, index) => {
                    const div = document.createElement('div');
                    div.className =
                        'w-[50px] h-[50px] rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-600 text-lg numero-sorteado';

                    if (index === numerosSorteados.length - 1) {
                        div.className =
                            'w-[50px] h-[50px] rounded-full bg-gradient-to-br from-pink-400 to-red-500 flex items-center justify-center font-bold text-white text-lg numero-sorteado ultimo';
                    }
                    div.textContent = numero;
                    grid.appendChild(div);
                });
            }

            function bloquearJuego() {
                const btn = document.getElementById('btn-sortear');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = '🏁 JUEGO FINALIZADO';
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                const estado = document.getElementById('estado-juego');
                if (estado) estado.textContent = '🏁 Finalizado';
            }
        });
    </script>
</body>

</html>
