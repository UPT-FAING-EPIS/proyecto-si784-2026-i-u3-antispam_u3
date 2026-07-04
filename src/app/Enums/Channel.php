<?php

namespace App\Enums;

/**
 * Canales de origen soportados por Aegis Filter.
 * Evita strings sueltos repetidos en controladores/modelos.
 */
enum Channel: string
{
    case Web = 'web';
    case Telegram = 'telegram';
    case Alexa = 'alexa';
    case Wordpress = 'wordpress';
    case Discord = 'discord';
}
