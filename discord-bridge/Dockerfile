########################################################
# Aegis Filter – Discord Bridge Dockerfile
# Imagen optimizada multi-stage: Python 3.11 + discord.py
#
# Curso: SI784 – Calidad y Pruebas de Software
# Proyecto: Aegis Filter | Bot Moderador de Discord
########################################################

# ─── Etapa 1: Instalar dependencias (Builder) ────────
FROM python:3.11-slim AS builder

WORKDIR /build

# Crear entorno virtual aislado
RUN python -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Instalar dependencias Python
COPY requirements.txt .
RUN pip install --no-cache-dir --upgrade pip \
    && pip install --no-cache-dir -r requirements.txt

# ─── Etapa 2: Imagen de Producción ───────────────────
FROM python:3.11-slim AS production

LABEL maintainer="AegisFilter Team <aegis@techhub.pe>"
LABEL description="Discord Bridge – Bot moderador de Discord para Aegis Filter"
LABEL version="1.0.0"

# Crear usuario no-root por seguridad (buena práctica)
RUN groupadd --gid 1000 appuser \
    && useradd --uid 1000 --gid appuser --create-home appuser

WORKDIR /app

# Copiar el entorno virtual completo desde el builder
COPY --from=builder /opt/venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Copiar código fuente de la aplicación
COPY main.py .

# Cambiar a usuario no-root
USER appuser

# Variables de entorno por defecto
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    AEGIS_API_URL="http://app:80/api/integrations/check-spam" \
    AEGIS_REQUEST_TIMEOUT="10" \
    DISCORD_BOT_TOKEN="" \
    AEGIS_INTEGRATION_KEY=""

# Sin EXPOSE/HEALTHCHECK HTTP: el bot solo mantiene una conexión saliente
# (WebSocket) al Gateway de Discord, no expone ningún servidor HTTP.

CMD ["python", "main.py"]
