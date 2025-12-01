<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi Tarjeta de Bingo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,800" rel="stylesheet" />

    <style>
        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        .ultimo-numero {
            animation: pulse 1s ease-in-out;
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            animation: confetti-fall 3s linear;
        }

        .ganador-content {
            animation: bounce 0.5s ease;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen p-5">
    <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-indigo-500 text-4xl font-bold mb-2.5">🎲 BINGO 🎲</h1>
            <p class="text-gray-600">Código de Juego: <strong>{{ $codigoJuego }}</strong></p>
            <p class="text-gray-600">Tarjeta: <strong>{{ $tarjeta->codigo }}</strong></p>
            <p class="text-gray-600">Jugador: <strong>{{ $tarjeta->nombre }}</strong></p>
        </div>

        <div id="ultimo-numero-container" class="hidden">
            <p class="text-center text-gray-600 mb-1.5">Último número:</p>
            <div id="ultimo-numero" class="text-7xl font-bold text-indigo-500 text-center my-5 ultimo-numero"></div>
        </div>

        <div id="grid-numeros" class="grid grid-cols-5 gap-4 my-8">
            @foreach ($tarjeta->lineas as $linea)
                @foreach (['n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'n10'] as $campo)
                    @if ($linea->$campo)
                        <div class="w-[60px] h-[60px] rounded-full flex items-center justify-center text-2xl font-bold bg-gradient-to-br from-pink-400 to-red-500 text-white transition-all duration-300 cursor-pointer border-[3px] border-transparent hover:scale-105 mx-auto"
                            data-numero="{{ $linea->$campo }}">
                            {{ $linea->$campo }}
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

        <div class="text-center mt-8">
            <div class="flex justify-between px-5">
                <div>
                    <p class="text-gray-600 m-0">Números marcados:</p>
                    <p id="contador-marcados" class="text-2xl font-bold text-indigo-500 my-1.5">0/10</p>
                </div>
                <div>
                    <p class="text-gray-600 m-0">Números sorteados:</p>
                    <p id="total-sorteados" class="text-2xl font-bold text-purple-600 my-1.5">0</p>
                </div>
            </div>
        </div>

        <div id="estado-juego" class="text-center mt-5 p-4 bg-gray-100 rounded-xl">
            <p class="m-0 text-gray-600">⏳ Esperando que comience el sorteo...</p>
        </div>
    </div>

    <!-- Modal de Ganador -->
    <div id="ganador-modal"
        class="hidden fixed top-0 left-0 w-full h-full bg-black/80 items-center justify-center z-[1000]">
        <div class="ganador-content bg-white p-12 rounded-3xl text-center">
            <h1 class="text-green-500 text-7xl m-0 mb-2.5">🎉 ¡BINGO! 🎉</h1>
            <p class="text-2xl my-5 m-0">¡Felicidades, has ganado!</p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // --- 1. CONFIGURACIÓN DE DATOS ---
            const codigoJuego = '{{ $codigoJuego }}';
            const tarjetaId = {{ $tarjeta->id }};

            const numerosTarjeta = [
                @foreach ($tarjeta->lineas as $linea)
                    @foreach (['n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'n10'] as $campo)
                        @if ($linea->$campo)
                            {{ $linea->$campo }},
                        @endif
                    @endforeach
                @endforeach
            ].map(Number);

            let numerosMarcados = [];
            let juegoFinalizado = false;

            // --- 2. FUNCIÓN DE INICIO DIFERIDO ---
            const iniciarConexion = () => {
                if (!window.Echo) {
                    console.log("Esperando a Echo...");
                    setTimeout(iniciarConexion, 100);
                    return;
                }

                console.log("Echo detectado. Suscribiendo a:", `bingo.${codigoJuego}`);

                // --- 3. LÓGICA DE ECHO ---
                window.Echo.channel(`bingo.${codigoJuego}`)
                    .listen('.numero.sorteado', (data) => {
                        console.log('Número sorteado:', data);

                        const numeroSorteado = parseInt(data.numero);

                        mostrarUltimoNumero(numeroSorteado);

                        if (document.getElementById('total-sorteados')) {
                            document.getElementById('total-sorteados').textContent = data.numerosSorteados
                                ?.length || 0;
                        }

                        if (numerosTarjeta.includes(numeroSorteado)) {
                            marcarNumero(numeroSorteado);
                        }

                        actualizarEstadoUI(true);
                    })
                    .listen('.juego.ganado', (data) => {
                        console.log('Juego ganado:', data);
                        juegoFinalizado = true;

                        if (data.tarjeta.id === tarjetaId) {
                            mostrarModalGanador();
                            lanzarConfetti();
                        } else {
                            const estadoEl = document.getElementById('estado-juego');
                            if (estadoEl) {
                                estadoEl.innerHTML =
                                    `<p class="m-0 text-red-500">❌ Juego finalizado. Ganador: ${data.tarjeta.nombre}</p>`;
                            }
                        }
                    });
            };

            iniciarConexion();

            // --- 4. FUNCIONES AUXILIARES ---
            function mostrarUltimoNumero(numero) {
                const container = document.getElementById('ultimo-numero-container');
                const elemento = document.getElementById('ultimo-numero');
                if (!container || !elemento) return;

                container.classList.remove('hidden');
                elemento.textContent = numero;
                elemento.style.animation = 'none';
                elemento.offsetHeight;
                elemento.style.animation = 'pulse 1s ease-in-out';
            }

            function marcarNumero(numero) {
                if (juegoFinalizado || numerosMarcados.includes(numero)) return;

                numerosMarcados.push(numero);

                const bola = document.querySelector(`[data-numero="${numero}"]`);
                if (bola) {
                    bola.className =
                        'w-[60px] h-[60px] rounded-full flex items-center justify-center text-2xl font-bold bg-gradient-to-br from-blue-400 to-cyan-400 text-white transition-all duration-300 cursor-pointer border-[3px] border-blue-700 scale-90';
                }

                const contador = document.getElementById('contador-marcados');
                if (contador) contador.textContent = `${numerosMarcados.length}/10`;

                if (numerosMarcados.length === 10) {
                    verificarGanador();
                }
            }

            function actualizarEstadoUI(enProgreso) {
                const el = document.getElementById('estado-juego');
                if (el && enProgreso) {
                    el.innerHTML = '<p class="m-0 text-green-600">✅ Juego en progreso...</p>';
                }
            }

            function verificarGanador() {
                if (juegoFinalizado) return;

                fetch(`/bingo/juego/${codigoJuego}/tarjeta/${tarjetaId}/verificar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.ganador) {
                            console.log('¡Verificación exitosa en servidor!');
                        }
                    })
                    .catch(error => console.error('Error verificación:', error));
            }

            function mostrarModalGanador() {
                const modal = document.getElementById('ganador-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            function lanzarConfetti() {
                for (let i = 0; i < 100; i++) {
                    setTimeout(() => {
                        const confetti = document.createElement('div');
                        confetti.className = 'confetti';
                        confetti.style.left = Math.random() * 100 + '%';
                        confetti.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                        confetti.style.animationDelay = Math.random() * 3 + 's';
                        document.body.appendChild(confetti);
                        setTimeout(() => confetti.remove(), 3000);
                    }, i * 30);
                }
            }
        });
    </script>
</body>

</html>
