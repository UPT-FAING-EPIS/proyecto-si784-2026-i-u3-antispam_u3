<center>

![Logo UPT](../media/logo-upt.png)

**UNIVERSIDAD PRIVADA DE TACNA**

**FACULTAD DE INGENIERÍA**

**Escuela Profesional de Ingeniería de Sistemas**

**Proyecto Antispam**

Curso: Base de Datos II

Docente: Patrick Cuadros Quiroga

Integrantes:
* Jahuira Pilco, Dayan Elvis (2022075749)
* Mamani Cori, Cristhian Carlos (2023077282)

Tacna – Perú


2026

</center>

---

Sistema Antispam  
**Documento de Arquitectura de Software**  
Versión 1.0

---

## CONTROL DE VERSIONES

| Versión | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
|--------|----------|-------------|-------------|-------|--------|
| 1.0 | Cristhian M. | Dayan J. | Patrick C. | 14/04/2026 | Versión Original |

---

## INDICE GENERAL
1. INTRODUCCIÓN
    1.1. Propósito (Diagrama 4+1)
    1.2. Alcance
    1.3. Definición, siglas y abreviaturas
    1.4. Organización del documento
2. OBJETIVOS Y RESTRICCIONES ARQUITECTONICAS
    2.1.1. Requerimientos Funcionales
    2.1.2. Requerimientos No Funcionales – Atributos de Calidad
3. REPRESENTACIÓN DE LA ARQUITECTURA DEL SISTEMA
    3.1. Vista de Caso de uso
    3.2. Vista Lógica
    3.3. Vista de Implementación (vista de desarrollo)
    3.4. Vista de procesos
    3.5. Vista de Despliegue (vista física)
4. ATRIBUTOS DE CALIDAD DEL SOFTWARE
5. BIBLIOGRAFÍA
6. WEBGRAFÍA

---

## 1. INTRODUCCIÓN

**1.1. Propósito (Diagrama 4+1)**
El presente documento tiene como propósito definir la arquitectura de software del sistema "Aegis Filter" utilizando el modelo de vistas 4+1 (Lógica, Implementación, Procesos, Despliegue y Casos de Uso). Presenta una visión global del diseño, justificando cómo las decisiones arquitectónicas satisfacen los requerimientos funcionales de detección de spam y las prioridades de alto rendimiento, modularidad y fácil despliegue en la nube.

**1.2. Alcance**
Este documento se centra en la arquitectura del backend en Laravel 11 ("Aegis Core") y su despliegue contenedorizado, así como en los canales de integración que delegan el análisis antispam a este núcleo: el formulario web del foro, un bot de Telegram, una Alexa Skill, un plugin de WordPress y un bot de Discord. Cada canal externo se implementa como un cliente desacoplado (microservicio o plugin) que consume la API HTTP del núcleo, autenticado mediante Integration Keys. Incluye la vista lógica (MVC y Servicios), la vista de despliegue (Terraform en Azure + Docker Compose + Caddy) y la estructura de datos (MySQL).

**1.3. Definición, siglas y abreviaturas**
* **API:** Interfaz de Programación de Aplicaciones.
* **BDD:** Desarrollo Guiado por Comportamiento (Behavior-Driven Development).
* **Bridge:** Microservicio satélite que traduce el protocolo de un canal externo (Alexa, Discord) al API HTTP del Aegis Core.
* **Docker:** Plataforma de contenedorización de software.
* **Gateway (Discord):** Conexión WebSocket persistente que Discord usa para entregar mensajes en tiempo real (no usa webhooks HTTP).
* **IaC:** Infraestructura como Código (uso de Terraform).
* **Integration Key:** Credencial por canal (hash SHA-256) que autentica las llamadas externas al endpoint `/api/integrations/check-spam`.
* **MVC:** Patrón de arquitectura Modelo-Vista-Controlador.
* **NSG:** Grupo de Seguridad de Red (Azure).
* **Webhook:** Mecanismo HTTP usado por Telegram para notificar mensajes nuevos al backend.

**1.4. Organización del documento**
El documento está organizado en cuatro secciones principales: Objetivos y restricciones (define qué se debe cumplir), Representación de la arquitectura (donde se exponen los diagramas 4+1), y finalmente los atributos de calidad del software.

---

## 2. OBJETIVOS Y RESTRICCIONES ARQUITECTONICAS

### 2.1. Priorización de requerimientos

**Requerimientos Funcionales**

| ID | Descripcion | Prioridad |
|---|---|---|
| RF-01 | Interceptar las peticiones POST de comentarios antes de interactuar con la base de datos. | Alta |
| RF-02 | Validar el texto del comentario contra expresiones regulares para detectar múltiples URLs. | Alta |
| RF-03 | Evaluar el texto contra una lista negra de palabras ofensivas almacenadas en el sistema. | Alta |
| RF-04 | Bloquear peticiones sospechosas retornando un estado HTTP 403. | Alta |
| RF-05 | Almacenar métricas de los comentarios permitidos y rechazados. | Media |

**Requerimientos No Funcionales – Atributos de Calidad**

| ID | Descripcion | Prioridad |
|---|---|---|
| RNF-01 | Disponibilidad: El sistema debe operar en la nube de Azure mediante contenedores para asegurar un 99.9% de uptime. | Alta |
| RNF-02 | Rendimiento: El análisis heurístico de cada comentario no debe superar los 500ms para evitar cuellos de botella en la web. | Alta |
| RNF-03 | Seguridad: La base de datos debe estar aislada en una red virtual, impidiendo el acceso desde el exterior (puerto 3306 cerrado). | Alta |
| RNF-04 | Mantenibilidad: El código debe adherirse a los estándares PSR-12 y utilizar inyección de dependencias para facilitar futuras actualizaciones. | Media |

### 2.2. Restricciones
* Tecnológicas: El desarrollo debe utilizar estrictamente PHP 8.2+ y Laravel 11.
* Infraestructura: La máquina virtual en producción está restringida al plan Standard_B1ms de Azure por motivos de presupuesto académico, limitando los recursos a 1 vCPU y 2 GB de RAM.
* Despliegue: Prohibido el acceso manual FTP al servidor; todo cambio en producción debe realizarse a través del pipeline de GitHub Actions.

---

## 3. REPRESENTACIÓN DE LA ARQUITECTURA DEL SISTEMA

### 3.1. Vista de Caso de uso

**3.1.1. Diagramas de Casos de uso**
```mermaid
flowchart LR
    %% Actores
    Visitante((Visitante Web))
    Admin((Administrador))
    Telegram((Usuario Telegram))
    Alexa((Usuario Alexa))
    WP((Comentarista WordPress))
    Discord((Usuario Discord))

    %% Casos de uso - canal público
    UC1([Enviar comentario en el foro])
    UC2([Analizar mensaje de voz - Alexa Skill])
    UC3([Escribir mensaje en grupo de Telegram])
    UC4([Publicar comentario en WordPress])
    UC5([Escribir mensaje en servidor Discord])

    %% Caso de uso núcleo
    UCCORE([Analizar contenido contra reglas antispam])

    %% Casos de uso administrativos
    UC6([Gestionar lista negra de palabras])
    UC7([Emitir / revocar Integration Keys])
    UC8([Aprobar o eliminar comentarios])
    UC9([Configurar límite de URLs])
    UC10([Consultar bitácora de auditoría])

    Visitante --- UC1
    Telegram --- UC3
    Alexa --- UC2
    WP --- UC4
    Discord --- UC5

    UC1 -.->|include| UCCORE
    UC2 -.->|include| UCCORE
    UC3 -.->|include| UCCORE
    UC4 -.->|include| UCCORE
    UC5 -.->|include| UCCORE

    Admin --- UC6
    Admin --- UC7
    Admin --- UC8
    Admin --- UC9
    Admin --- UC10

    classDef usecase fill:#fff9c4,stroke:#fbc02d,stroke-width:2px,color:#000
    classDef core fill:#ffe0b2,stroke:#e65100,stroke-width:3px,color:#000
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10 usecase
    class UCCORE core
```

> Los cinco canales (Web, Telegram, Alexa, WordPress, Discord) son interfaces distintas hacia el mismo caso de uso núcleo "Analizar contenido contra reglas antispam", implementado una sola vez en `SpamFilterService` y reutilizado por todos.

### 3.2. Vista Lógica

**3.2.1. Diagrama de Subsistemas (paquetes)**

```mermaid
flowchart TD
    classDef layer fill:#e3f2fd,stroke:#1e88e5,stroke-width:2px,color:#000
    classDef db fill:#e8f5e9,stroke:#43a047,stroke-width:2px,color:#000
    classDef external fill:#fff3e0,stroke:#f57c00,stroke-width:2px,color:#000

    subgraph Canales ["Canales de entrada (desacoplados)"]
        UI[Vistas Blade - Foro Web]:::external
        TG[Telegram Webhook]:::external
        AX[Alexa Bridge - FastAPI]:::external
        WP[Plugin WordPress]:::external
        DC[Discord Bridge - discord.py]:::external
    end

    subgraph Aplicacion ["Capa de Aplicación (Aegis Core - Laravel)"]
        MW[Middleware: auth / throttle / VerifyIntegrationKey]:::layer
        C[Controllers]:::layer
        S[SpamFilterService]:::layer
        M[Models ORM]:::layer
    end

    subgraph Persistencia ["Capa de Persistencia (Docker)"]
        BD[(Base de Datos MySQL)]:::db
    end

    UI -->|POST /comentarios| MW
    TG -->|POST /api/telegram/webhook| MW
    AX -->|POST /api/analyze| MW
    WP -->|POST /api/integrations/check-spam<br/>header X-Integration-Key| MW
    DC -->|POST /api/integrations/check-spam<br/>header X-Integration-Key| MW

    MW --> C
    C -->|Inyección de Dependencias| S
    C -->|Mapeo de Datos| M
    M -->|Consultas SQL| BD
```

**3.2.2. Diagrama de Secuencia — Canal Web (vista de diseño)**
```mermaid
sequenceDiagram
    autonumber
    actor Usuario
    participant Controller as CommentController
    participant Service as SpamFilterService
    participant Model as Comment
    participant Log as AnalysisLog
    participant BD as MySQL

    Usuario->>Controller: POST /comentarios (author, content)
    activate Controller

    Controller->>Service: analyze(content, author, 'web')
    activate Service
    Service->>Service: checkBlacklistedWords(content)
    Service->>Service: checkExcessiveUrls(content)
    Service-->>Controller: {isSpam, reason, score, detail}
    deactivate Service

    Controller->>Model: create(author, content, status, spam_reason)
    activate Model
    Model->>BD: INSERT INTO comments
    BD-->>Model: OK
    deactivate Model

    Controller->>Log: record(result, 'web', author, content, ip)
    Log->>BD: INSERT INTO analysis_logs

    alt isSpam = true
        Controller-->>Usuario: Comentario marcado como spam (no visible)
    else isSpam = false
        Controller-->>Usuario: Comentario publicado (pendiente/aprobado)
    end
    deactivate Controller
```

**3.2.3. Diagrama de Secuencia — Canales externos integrados (WordPress / Discord)**
```mermaid
sequenceDiagram
    autonumber
    participant Bridge as Plugin/Bot externo<br/>(WordPress o Discord Bridge)
    participant MW as VerifyIntegrationKey
    participant Controller as CommentController
    participant Service as SpamFilterService
    participant Log as AnalysisLog
    participant BD as MySQL

    Bridge->>MW: POST /api/integrations/check-spam<br/>header X-Integration-Key
    activate MW
    MW->>BD: findActiveByPlainKey(key)
    alt Key inválida o revocada
        MW-->>Bridge: 401 / 403
    else Key válida
        MW->>BD: UPDATE last_used_at
        MW->>Controller: forward (request.integration_channel)
        deactivate MW
        activate Controller
        Controller->>Service: analyze(content, author, channel)
        Service-->>Controller: {isSpam, score, reason}
        Controller->>Log: record(result, channel, author, content, ip)
        Log->>BD: INSERT INTO analysis_logs
        Controller-->>Bridge: 200 {isSpam, score, reason}
        deactivate Controller
    end
```

> El bot de Discord (conectado al Gateway por WebSocket) y el plugin de WordPress (hook `pre_comment_approved`) son los únicos que llaman a este endpoint genérico; Alexa Bridge usa `/api/analyze` con la misma lógica interna pero sin requerir Integration Key (validación propia por firma de Amazon).

**3.2.4. Diagrama de Objetos**
```mermaid
classDiagram
    class comentarioRecibido {
        author: "SpamBot99"
        content: "Gana dinero facil, compra ahora http://spam.com"
        ip_address: "192.168.1.50"
    }

    class resultadoAnalisis {
        isSpam: true
        reason: "blacklisted_word"
        score: 100
    }

    class comentarioGuardado {
        status: "spam"
        spam_reason: "blacklisted_word"
    }

    class logAuditoria {
        channel: "web"
        is_spam: true
        score: 100
    }

    comentarioRecibido --> resultadoAnalisis : SpamFilterService.analyze()
    resultadoAnalisis --> comentarioGuardado : Comment.create()
    resultadoAnalisis --> logAuditoria : AnalysisLog.record()
```

**3.2.5. Diagrama de Clases**
```mermaid
classDiagram
    class Channel {
        <<enum>>
        Web
        Telegram
        Alexa
        Wordpress
        Discord
    }

    class CommentController {
        - spamFilter: SpamFilterService
        + store(request: Request)
        + checkSpam(request: Request)
        + analyzeText(request: Request)
        + integrationCheckSpam(request: Request)
    }

    class TelegramController {
        - spamFilter: SpamFilterService
        - bot: TelegramBotService
        + handleWebhook(request: Request)
        + setupWebhook(request: Request)
    }

    class VerifyIntegrationKey {
        <<middleware>>
        + handle(request, next)
    }

    class SpamFilterService {
        + analyze(content, author, channel) array
        - checkBlacklistedWords(content) array
        - checkExcessiveUrls(content) array
        - getMaxAllowedUrls() int
    }

    class Comment {
        + author: string
        + content: string
        + status: enum
        + spam_reason: string
        + approved() Builder
        + spam() Builder
    }

    class BlacklistWord {
        + word: string
        + is_active: bool
        + active() Builder
    }

    class IntegrationKey {
        + channel: string
        + key_hash: string
        + key_prefix: string
        + is_active: bool
        + generate(channel, label, user)$ array
        + findActiveByPlainKey(key)$ IntegrationKey
    }

    class AnalysisLog {
        + channel: string
        + is_spam: bool
        + score: int
        + record(result, channel, author, content, ip)$ void
    }

    class TelegramMessage {
        + chat_id: int
        + status: enum
        + action_taken: string
    }

    class Setting {
        + key: string
        + value: string
        + get(key, default)$ mixed
    }

    class User {
        + name: string
        + email: string
    }

    CommentController ..> SpamFilterService : usa
    CommentController ..> Comment : crea
    CommentController ..> AnalysisLog : registra
    TelegramController ..> SpamFilterService : usa
    TelegramController ..> TelegramMessage : registra
    VerifyIntegrationKey ..> IntegrationKey : valida
    VerifyIntegrationKey ..> Channel : asigna
    SpamFilterService ..> BlacklistWord : consulta
    SpamFilterService ..> Setting : lee max_allowed_urls
    IntegrationKey ..> Channel : pertenece a
    User "1" --> "*" BlacklistWord : crea
    User "1" --> "*" IntegrationKey : emite
```

**3.2.6. Diagrama de Base de datos (modelo relacional)**
```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar email
        varchar password
    }

    COMMENTS {
        bigint id PK
        varchar author
        varchar email
        text content
        enum status "pending, approved, spam"
        varchar spam_reason
        varchar ip_address
        timestamp created_at
    }

    BLACKLIST_WORDS {
        bigint id PK
        varchar word
        boolean is_active
        bigint created_by FK
    }

    INTEGRATION_KEYS {
        bigint id PK
        varchar channel
        varchar label
        varchar key_hash
        varchar key_prefix
        boolean is_active
        timestamp last_used_at
        bigint created_by FK
    }

    ANALYSIS_LOGS {
        bigint id PK
        varchar channel
        varchar author
        text content
        boolean is_spam
        varchar reason
        smallint score
        varchar ip_address
    }

    TELEGRAM_MESSAGES {
        bigint id PK
        bigint chat_id
        bigint user_id
        varchar username
        text content
        enum status "approved, spam"
        varchar action_taken
    }

    SETTINGS {
        bigint id PK
        varchar key
        varchar value
        enum type "int, bool, json, string"
    }

    USERS ||--o{ BLACKLIST_WORDS : "crea"
    USERS ||--o{ INTEGRATION_KEYS : "emite"
```

> `COMMENTS`, `ANALYSIS_LOGS`, `TELEGRAM_MESSAGES` y `SETTINGS` son tablas independientes (sin FK hacia `USERS`); se relacionan lógicamente por `channel`, no por clave foránea.

### 3.3. Vista de Implementación (vista de desarrollo)

**3.3.1. Diagrama de arquitectura software (paquetes)**
```mermaid
flowchart TD
    classDef layer fill:#f5f5f5,stroke:#333,stroke-width:2px,color:#000
    classDef module fill:#e1f5fe,stroke:#0288d1,stroke-width:2px,color:#000
    classDef external fill:#fff3e0,stroke:#f57c00,stroke-width:2px,color:#000

    subgraph UI ["Paquete: UI (Presentación)"]
        V[Views / Blade Templates]:::module
    end

    subgraph App ["Paquete: App\\Http + App\\Services (Aegis Core)"]
        MW[Http\\Middleware\\VerifyIntegrationKey]:::module
        C[Http\\Controllers]:::module
        S[Services\\SpamFilterService]:::module
        M[Models]:::module
        E[Enums\\Channel]:::module
    end

    subgraph DB ["Paquete: Infraestructura"]
        O[Eloquent ORM]:::module
        D[(MySQL Database)]:::layer
    end

    subgraph Bridges ["Paquetes externos (repos independientes)"]
        AB[alexa-bridge/ - Python FastAPI]:::external
        DB2[discord-bridge/ - Python discord.py]:::external
        WPP[wordpress-plugin/aegis-filter/ - PHP]:::external
    end

    Cliente((Navegador / Bot)):::external -->|Rutas web.php / api.php| MW
    AB -->|HTTP POST /api/analyze| MW
    DB2 -->|HTTP POST /api/integrations/check-spam| MW
    WPP -->|HTTP POST /api/integrations/check-spam| MW
    MW --> C
    C -->|Retorna| V
    C -->|Inyecta| S
    C -->|Usa| M
    M -->|Usa| E
    M -->|Hereda| O
    O -->|Query SQL| D
```

**3.3.2. Diagrama de arquitectura del sistema (Diagrama de componentes)**
```mermaid
flowchart TD
    classDef external fill:#fff3e0,stroke:#f57c00,stroke-width:2px,color:#000
    classDef comp fill:#e1f5fe,stroke:#0288d1,stroke-width:2px,color:#000
    classDef db fill:#e8f5e9,stroke:#43a047,stroke-width:2px,color:#000
    classDef note fill:#fff9c4,stroke:#fbc02d,stroke-dasharray: 5 5,color:#000

    Navegador([Navegador Web]):::external
    TelegramAPI([Telegram Bot API]):::external
    AlexaSrv([Amazon Alexa Service]):::external
    WPSite([Sitio WordPress externo]):::external
    DiscordGW([Discord Gateway]):::external

    subgraph Docker ["Docker Host (VPS Debian, Docker Compose)"]
        direction TB
        Caddy[Caddy 2 - Reverse Proxy<br/>TLS automático - Puertos 80/443]:::comp
        App[app: Laravel 11 + PHP-Apache<br/>Puerto interno 80]:::comp
        AlexaBridge[alexa-bridge: FastAPI<br/>Puerto interno 8080]:::comp
        DiscordBridge[discord-bridge: discord.py<br/>sin puerto - conexión saliente]:::comp
        PMA[phpmyadmin]:::comp
        DB[(db: MySQL 8<br/>Puerto interno 3306)]:::db
    end

    Navegador -->|HTTPS| Caddy
    Caddy -->|HTTP interno| App
    TelegramAPI -->|Webhook HTTPS| Caddy
    AlexaSrv -->|HTTPS /alexa| Caddy
    Caddy -->|proxy| AlexaBridge
    WPSite -->|HTTPS + X-Integration-Key| Caddy
    AlexaBridge -->|HTTP /api/analyze| App
    DiscordBridge -->|HTTP /api/integrations/check-spam| App
    DiscordGW -.->|WebSocket persistente| DiscordBridge
    App -->|PDO MySQL| DB
    PMA -->|PDO MySQL| DB

    Nota[Nota: el puerto 3306 solo es<br/>accesible dentro de la red Docker<br/>interna, no se expone a internet.]:::note
    DB -.-> Nota
```

### 3.4. Vista de procesos

**3.4.1. Diagrama de Procesos del sistema (diagrama de actividad)**
```mermaid
flowchart TD
    classDef startEnd fill:#cfd8dc,stroke:#455a64,stroke-width:2px,color:#000
    classDef process fill:#e1f5fe,stroke:#0288d1,stroke-width:2px,color:#000
    classDef decision fill:#ffd54f,stroke:#f57f17,stroke-width:2px,color:#000
    classDef error fill:#ffccbc,stroke:#d84315,stroke-width:2px,color:#000
    classDef success fill:#c8e6c9,stroke:#388e3c,stroke-width:2px,color:#000

    A([Inicio: mensaje recibido desde<br/>cualquier canal]):::startEnd --> B{¿Canal requiere<br/>Integration Key?}:::decision
    B -- "Sí (WordPress / Discord)" --> B2[VerifyIntegrationKey: validar header]:::process
    B2 --> B3{¿Key válida y activa?}:::decision
    B3 -- No --> E1[Retornar HTTP 401/403]:::error
    B3 -- Sí --> C[SpamFilterService: normalizar contenido]:::process
    B -- "No (Web / Telegram / Alexa)" --> C

    C --> D[Validar contra lista negra]:::process
    D --> D2{¿Contiene palabra\nprohibida?}:::decision
    D2 -- Sí --> F[score=100, reason=blacklisted_word]:::error
    D2 -- No --> G[Contar URLs con Regex]:::process
    G --> G2{¿URLs > límite\nconfigurado?}:::decision
    G2 -- Sí --> F2[score=80, reason=too_many_urls]:::error
    G2 -- No --> H[isSpam=false]:::success

    F --> I[Registrar en AnalysisLog]:::process
    F2 --> I
    H --> I
    I --> J{¿Canal persiste\nregistro propio?}:::decision
    J -- "Sí (Web→Comment, Telegram→TelegramMessage)" --> K[Guardar/actualizar registro]:::process
    J -- "No (WordPress/Discord/Alexa)" --> L[Responder JSON al canal]:::process
    K --> M([Fin]):::startEnd
    L --> M
    E1 --> M
```

### 3.5. Vista de Despliegue (vista física)

**3.5.1. Diagrama de despliegue**
```mermaid
flowchart TD
    classDef cloud fill:#e3f2fd,stroke:#1e88e5,stroke-width:2px,color:#000,stroke-dasharray: 5 5
    classDef node fill:#fff,stroke:#333,stroke-width:2px,color:#000
    classDef db fill:#e8f5e9,stroke:#43a047,stroke-width:2px,color:#000
    classDef external fill:#fff3e0,stroke:#f57c00,stroke-width:2px,color:#000

    Internet((Internet Pública))
    Telegram[Telegram Bot API]:::external
    Alexa[Amazon Alexa Service]:::external
    Discord[Discord Gateway]:::external
    WordPress[Sitios WordPress externos]:::external

    subgraph Azure ["Microsoft Azure Cloud (aprovisionado con Terraform)"]
        NSG{Network Security Group<br/>Inbound: 80, 443, 22}:::node

        subgraph VM ["Máquina Virtual (Standard_B1ms) - dominio sytes.net"]
            subgraph DockerNet ["Docker Compose Network (aegisfilter_network)"]
                Caddy[Contenedor: caddy<br/>Reverse proxy + TLS Let's Encrypt]:::node
                App[Contenedor: app<br/>Laravel 11 / PHP 8.2 Apache]:::node
                AlexaBr[Contenedor: alexa-bridge<br/>FastAPI]:::node
                DiscordBr[Contenedor: discord-bridge<br/>discord.py]:::node
                DB[(Contenedor: db<br/>MySQL 8)]:::db
            end
        end
    end

    Internet -->|HTTPS / SSH| NSG
    Telegram -.->|Webhook HTTPS| NSG
    Alexa -.->|HTTPS| NSG
    WordPress -.->|HTTPS + Integration Key| NSG
    NSG -->|Tráfico filtrado 80/443| Caddy
    Caddy -->|proxy interno| App
    Caddy -->|proxy interno| AlexaBr
    DiscordBr -.->|WebSocket saliente| Discord
    AlexaBr -->|HTTP interno| App
    DiscordBr -->|HTTP interno| App
    App -->|Conexión interna aislada<br/>puerto 3306 no expuesto| DB
```

---

## 4. ATRIBUTOS DE CALIDAD DEL SOFTWARE

**Escenario de Funcionalidad**
El sistema demuestra su funcionalidad al interceptar exitosamente el 100% de las peticiones que cumplan con las reglas de negocio (ej. superar las 2 URLs) y bloqueándolas antes de alcanzar la base de datos.

**Escenario de Usabilidad**
Al ser un servicio de backend, la usabilidad se enfoca en el desarrollador y el administrador. Se garantiza mediante un código limpio, variables de entorno claras en el .env y un despliegue sin fricciones con comandos automatizados (Docker/Terraform).

**Escenario de confiabilidad**
El sistema previene inyecciones y ataques mediante la validación previa con expresiones regulares. La capa de datos en Azure está resguardada por un NSG (Network Security Group) que bloquea todo el tráfico no autorizado al puerto 3306.

**Escenario de rendimiento**
El motor de validación SpamFilterService es altamente eficiente, capaz de evaluar la heurística del texto y las listas negras devolviendo una respuesta en tiempos inferiores a 500 ms.

**Escenario de mantenibilidad**
La arquitectura separada en capas (Controlador -> Servicio -> Modelo) facilita la extensibilidad. Nuevas reglas anti-spam pueden añadirse al servicio sin necesidad de reescribir la lógica de la API ni la estructura de la base de datos.

---

## 5. BIBLIOGRAFÍA
* Laravel. (2026). Laravel 11.x Documentation: Architecture Concepts, Middleware, Service Container.
* Mermaid.js. (2026). Mermaid Diagram Syntax Documentation.
* Kruchten, P. (1995). *Architectural Blueprints — The "4+1" View Model of Software Architecture*. IEEE Software.
* Discord. (2026). Discord Developer Portal: Gateway and Privileged Intents Documentation.
* Amazon. (2026). Alexa Skills Kit (ASK) SDK for Python Documentation.
* WordPress. (2026). Plugin Handbook: `pre_comment_approved` Filter Reference.
* Caddy. (2026). Caddyfile Documentation: Automatic HTTPS.

## 6. WEBGRAFÍA
* Documentación oficial de FastAPI: https://fastapi.tiangolo.com/
* Documentación de discord.py: https://discordpy.readthedocs.io/
* Documentación de Docker Compose: https://docs.docker.com/compose/
* Guías de Microsoft Azure NSG: https://learn.microsoft.com/azure/virtual-network/network-security-groups-overview
