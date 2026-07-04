# Aegis Filter – Discord Bridge

Bot de Discord que modera mensajes en tiempo real delegando el análisis al
motor antispam de Aegis Filter, igual que el bot de Telegram: si un mensaje
se detecta como spam, se elimina del canal y se publica una advertencia.

A diferencia de Telegram (webhook HTTP) o Alexa (request/response HTTP),
Discord no ofrece webhooks para mensajes de chat normales — el bot necesita
mantener una conexión persistente al *Gateway* de Discord (WebSocket). Por
eso este servicio es un **proceso de larga duración** (`python main.py`),
no un servidor HTTP: no expone ningún puerto ni necesita dominio/TLS propio.

Si la API de Aegis Filter no responde (caída, timeout, credenciales mal
configuradas), el bot **no borra ningún mensaje** (fail-open).

## 1. Crear la aplicación/bot en Discord

1. Ve a https://discord.com/developers/applications y crea una nueva
   **Application** (ej. "Aegis Filter").
2. En la pestaña **Bot**:
   - Crea el bot (Add Bot, si no se creó automáticamente).
   - En **Privileged Gateway Intents**, activa **MESSAGE CONTENT INTENT**
     (obligatorio desde 2022 para que el bot pueda leer el texto de los
     mensajes; sin esto, `message.content` siempre llega vacío).
   - Copia el **Token** (botón "Reset Token" si es la primera vez) —
     este es el valor de `DISCORD_BOT_TOKEN`. Trátalo como una contraseña.
3. En la pestaña **OAuth2 > URL Generator**:
   - Scopes: `bot`.
   - Bot Permissions: **View Channels**, **Send Messages**,
     **Manage Messages** (necesario para poder borrar mensajes de otros),
     **Read Message History**.
   - Copia la URL generada y ábrela en el navegador para invitar el bot a
     tu servidor de prueba.

## 2. Emitir la Integration Key en Aegis Filter

En el panel de administración de Aegis Filter (`/admin/integration-keys`,
requiere sesión de admin):

1. Selecciona el canal `discord`.
2. (Opcional) Agrega una etiqueta, ej. "Servidor comunidad".
3. Genera la key y cópiala de inmediato — solo se muestra una vez. Este es
   el valor de `AEGIS_INTEGRATION_KEY`.

## 3. Configurar y levantar el servicio

En el `.env` del proyecto (raíz del repo):

```env
DISCORD_BOT_TOKEN=el-token-del-paso-1
DISCORD_INTEGRATION_KEY=afk_la-key-del-paso-2
```

```bash
docker compose up -d --build discord-bridge
docker compose logs -f discord-bridge
```

Deberías ver `Conectado a Discord como <nombre-del-bot>` en los logs.

## 4. Probar

Escribe un mensaje con una palabra de la lista negra (ej. "compra ahora")
en cualquier canal del servidor donde invitaste al bot. El bot debe
eliminarlo y publicar una advertencia. Un mensaje normal no debe verse
afectado.

## Revocar acceso

Si necesitas desactivar la integración, revoca la key desde
`/admin/integration-keys` sin necesidad de detener el bot: las llamadas
empezarán a fallar de forma segura (fail-open) hasta que configures una
key nueva.

## Pruebas automatizadas

```bash
pip install -r requirements-dev.txt
pytest
```

Los tests mockean tanto la llamada HTTP a Aegis Core (`respx`) como los
objetos `discord.Message`, así que no requieren un bot ni un servidor de
Discord real.

## Fuera de alcance (por ahora)

- Slash commands (`/aegis-status`, `/aegis-help`) — Discord lo soporta de
  forma nativa, pero no es necesario para la moderación automática.
- Restringir la moderación a canales específicos del servidor (hoy modera
  todos los canales de texto, igual que el bot de Telegram modera todos
  los grupos donde está presente).
