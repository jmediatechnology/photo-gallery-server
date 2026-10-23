<?php

namespace App\Infrastructure\ImageDescription\Enum;

enum ImageDescriptionProvider: string
{
    case Anthropic = 'anthropic';
    case Ollama = 'ollama';
    case Fake = 'fake';
}
