"""
Aegis Filter – Discord Bridge
==============================

Bot de Discord que modera mensajes en tiempo real delegando el análisis
al motor antispam de Aegis Filter (canal "discord", autenticado por
Integration Key contra /api/integrations/check-spam).

Arquitectura:
  [Servidor Discord] → [Discord Gateway] → [Discord Bridge (este bot)]
                                                  ↓ POST /api/integrations/check-spam
                                             [Aegis Core (Laravel)]

A diferencia de Telegram/Alexa, Discord no ofrece webhooks para mensajes
de chat normales: hace falta una conexión persistente al Gateway
(WebSocket), por eso este servicio es un cliente de larga duración en
vez de un servidor HTTP sin estado.

Curso: SI784 – Calidad y Pruebas de Software
Proyecto: Aegis Filter | Bot Moderador de Discord
"""

import asyncio
import logging
import os
import sys
from typing import Any, Optional

import discord
import httpx

# ══════════════════════════════════════════════════
# CONFIGURACIÓN
# ══════════════════════════════════════════════════

DISCORD_BOT_TOKEN: str = os.getenv("DISCORD_BOT_TOKEN", "")
AEGIS_API_URL: str = os.getenv(
    "AEGIS_API_URL", "http://app:80/api/integrations/check-spam"
)
AEGIS_INTEGRATION_KEY: str = os.getenv("AEGIS_INTEGRATION_KEY", "")
AEGIS_REQUEST_TIMEOUT: int = int(os.getenv("AEGIS_REQUEST_TIMEOUT", "10"))

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(name)s | %(levelname)s | %(message)s",
)
logger = logging.getLogger("discord-bridge")


# ══════════════════════════════════════════════════
# CLIENTE HTTP HACIA AEGIS CORE
# ══════════════════════════════════════════════════

def check_spam(content: str, author: str) -> Optional[dict[str, Any]]:
    """
    Consulta a Aegis Core si un mensaje es spam.

    Llamada síncrona (httpx.Client) — se invoca desde el loop de discord.py
    vía asyncio.to_thread() para no bloquear el procesamiento de eventos.

    Returns:
        dict con 'isSpam' (bool) si la API respondió correctamente,
        None si hubo cualquier error (fail-open: nunca bloquea mensajes
        legítimos por una caída del servicio).
    """
    if not AEGIS_INTEGRATION_KEY:
        logger.warning("AEGIS_INTEGRATION_KEY no configurada, omitiendo análisis")
        return None

    try:
        with httpx.Client(timeout=AEGIS_REQUEST_TIMEOUT) as client:
            response = client.post(
                AEGIS_API_URL,
                json={"author": author, "content": content},
                headers={
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-Integration-Key": AEGIS_INTEGRATION_KEY,
                },
            )

        if response.status_code != 200:
            logger.error(
                "Aegis Core respondió HTTP %d para el mensaje de %s",
                response.status_code, author,
            )
            return None

        return response.json()

    except httpx.HTTPError as exc:
        logger.error("Error de conexión con Aegis Core: %s", exc)
        return None


# ══════════════════════════════════════════════════
# CLIENTE DE DISCORD
# ══════════════════════════════════════════════════

intents = discord.Intents.default()
intents.message_content = True
intents.guilds = True

client = discord.Client(intents=intents)


@client.event
async def on_ready() -> None:
    logger.info("Conectado a Discord como %s (id=%s)", client.user, client.user.id)


@client.event
async def on_message(message: discord.Message) -> None:
    """
    Procesa cada mensaje nuevo en los servidores donde está el bot.

    Ignora mensajes propios, de otros bots, y mensajes directos (DM):
    la moderación aplica solo a canales de texto de servidores, igual
    que el bot de Telegram modera solo grupos (no chats privados).
    """
    if message.author.bot:
        return

    if message.guild is None:
        return

    if not message.content.strip():
        return

    result = await asyncio.to_thread(
        check_spam, message.content, str(message.author)
    )

    if result is None:
        return  # Fail-open: no se pudo verificar, no se toca el mensaje.

    if not result.get("isSpam"):
        return

    logger.warning(
        "Spam detectado de %s en #%s (razón: %s)",
        message.author, getattr(message.channel, "name", message.channel.id),
        result.get("reason"),
    )

    try:
        await message.delete()
        await message.channel.send(
            f"🛡️ **Aegis Filter** – Mensaje de {message.author.mention} eliminado "
            f"por contener spam (razón: `{result.get('reason')}`)."
        )
    except discord.Forbidden:
        logger.error(
            "Sin permisos para borrar mensajes en #%s — el bot necesita "
            "el permiso 'Manage Messages'",
            getattr(message.channel, "name", message.channel.id),
        )
    except discord.HTTPException as exc:
        logger.error("Error de Discord al borrar/advertir el mensaje: %s", exc)


def main() -> None:
    if not DISCORD_BOT_TOKEN:
        logger.error(
            "DISCORD_BOT_TOKEN no configurado. Genera un bot en "
            "https://discord.com/developers/applications y define el token "
            "en la variable de entorno DISCORD_BOT_TOKEN."
        )
        sys.exit(1)

    client.run(DISCORD_BOT_TOKEN)


if __name__ == "__main__":
    main()
