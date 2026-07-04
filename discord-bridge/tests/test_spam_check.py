"""
Tests para discord-bridge: check_spam() (cliente HTTP hacia Aegis Core)
y on_message() (decisión de borrar/advertir según el resultado).
"""

from unittest.mock import AsyncMock, MagicMock

import httpx
import pytest
import respx

import main
from main import check_spam, on_message


# ══════════════════════════════════════════════════
# check_spam()
# ══════════════════════════════════════════════════

def test_check_spam_returns_none_without_integration_key(monkeypatch):
    monkeypatch.setattr(main, "AEGIS_INTEGRATION_KEY", "")

    result = check_spam("cualquier mensaje", "autor")

    assert result is None


@respx.mock
def test_check_spam_parses_successful_response(monkeypatch):
    monkeypatch.setattr(main, "AEGIS_INTEGRATION_KEY", "afk_test_key")
    respx.post(main.AEGIS_API_URL).mock(
        return_value=httpx.Response(
            200,
            json={"isSpam": True, "score": 100, "reason": "blacklisted_word"},
        )
    )

    result = check_spam("compra ahora", "usuario1")

    assert result == {"isSpam": True, "score": 100, "reason": "blacklisted_word"}


@respx.mock
def test_check_spam_sends_integration_key_header(monkeypatch):
    monkeypatch.setattr(main, "AEGIS_INTEGRATION_KEY", "afk_secret")
    route = respx.post(main.AEGIS_API_URL).mock(
        return_value=httpx.Response(200, json={"isSpam": False})
    )

    check_spam("hola mundo", "usuario1")

    sent_request = route.calls.last.request
    assert sent_request.headers["X-Integration-Key"] == "afk_secret"


@respx.mock
def test_check_spam_returns_none_on_non_200(monkeypatch):
    monkeypatch.setattr(main, "AEGIS_INTEGRATION_KEY", "afk_test_key")
    respx.post(main.AEGIS_API_URL).mock(return_value=httpx.Response(403))

    result = check_spam("mensaje", "usuario1")

    assert result is None


@respx.mock
def test_check_spam_returns_none_on_connection_error(monkeypatch):
    monkeypatch.setattr(main, "AEGIS_INTEGRATION_KEY", "afk_test_key")
    respx.post(main.AEGIS_API_URL).mock(side_effect=httpx.ConnectError("boom"))

    result = check_spam("mensaje", "usuario1")

    assert result is None


# ══════════════════════════════════════════════════
# on_message()
# ══════════════════════════════════════════════════

def make_message(content="hola", is_bot=False, guild="some-guild"):
    message = MagicMock()
    message.author.bot = is_bot
    message.author.mention = "@autor"
    message.author.__str__.return_value = "autor#0001"
    message.guild = guild
    message.content = content
    message.channel.name = "general"
    message.delete = AsyncMock()
    message.channel.send = AsyncMock()
    return message


@pytest.mark.asyncio
async def test_on_message_ignores_bot_messages(monkeypatch):
    spy = MagicMock()
    monkeypatch.setattr(main, "check_spam", spy)
    message = make_message(is_bot=True)

    await on_message(message)

    spy.assert_not_called()
    message.delete.assert_not_called()


@pytest.mark.asyncio
async def test_on_message_ignores_direct_messages(monkeypatch):
    spy = MagicMock()
    monkeypatch.setattr(main, "check_spam", spy)
    message = make_message(guild=None)

    await on_message(message)

    spy.assert_not_called()
    message.delete.assert_not_called()


@pytest.mark.asyncio
async def test_on_message_ignores_empty_content(monkeypatch):
    spy = MagicMock()
    monkeypatch.setattr(main, "check_spam", spy)
    message = make_message(content="   ")

    await on_message(message)

    spy.assert_not_called()


@pytest.mark.asyncio
async def test_on_message_deletes_and_warns_when_spam(monkeypatch):
    monkeypatch.setattr(
        main, "check_spam",
        MagicMock(return_value={"isSpam": True, "reason": "blacklisted_word"}),
    )
    message = make_message(content="compra ahora esta oferta")

    await on_message(message)

    message.delete.assert_awaited_once()
    message.channel.send.assert_awaited_once()


@pytest.mark.asyncio
async def test_on_message_does_nothing_when_clean(monkeypatch):
    monkeypatch.setattr(
        main, "check_spam", MagicMock(return_value={"isSpam": False})
    )
    message = make_message(content="hola, buen día")

    await on_message(message)

    message.delete.assert_not_called()
    message.channel.send.assert_not_called()


@pytest.mark.asyncio
async def test_on_message_fail_open_when_api_unavailable(monkeypatch):
    monkeypatch.setattr(main, "check_spam", MagicMock(return_value=None))
    message = make_message(content="compra ahora esta oferta")

    await on_message(message)

    message.delete.assert_not_called()
    message.channel.send.assert_not_called()
