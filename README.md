# Bingo en Tiempo Real (Laravel + Echo + Reverb)

Este proyecto es una aplicación de **bingo en tiempo real**, desarrollada con **Laravel**, utilizando **WebSockets** para sincronizar las jugadas entre todos los participantes sin necesidad de recargar la página.

---

## ⚙️ ¿Cómo funciona?

### 🔌 Comunicación en tiempo real

La aplicación usa:

* **Laravel Echo** → cliente que escucha eventos del servidor.
* **Laravel Reverb** → servidor WebSocket nativo de Laravel.

Cuando el administrador del bingo **lanza un nuevo número**, se dispara un evento en Laravel que se envía a Reverb, y Echo lo recibe automáticamente en todos los navegadores conectados.

---

## 🔄 Sincronización instantánea

Cada vez que se realiza una acción:

1. Se genera un **evento de Laravel** (`NumeroSorteado`).
2. El evento se transmite por un **canal de broadcasting**.
3. Todos los jugadores reciben la actualización **al instante**, sin refrescar.

Esto permite que todos vean:

* Nueva bolilla  
* Jugadas anteriores
* Estado del juego  
* Se corta al generar un ganador

---

## 🎮 Flujo simple del Bingo

1. El admin inicia un juego desde el panel.  
2. Los usuarios que deseen participar generan una tarjeta con 10 numeros únicos.
3. Los jugadores tienen sus tarjetas vinculadas a ese juego.
4. Cada bolilla lanzada se transmite por WebSocket.  
5. Todos los clientes actualizan su cartón en vivo.  
6. Cuando un cartón es ganador, se emite un evento final (`JuegoGanado`).  

---

## 🧩 Tecnologías principales

* **Laravel 12**  
* **Laravel Reverb** (WebSockets)  
* **Laravel Echo**  
* **Blade / JavaScript**  
* **SQLite**  
* **TailwindCSS** 

---
