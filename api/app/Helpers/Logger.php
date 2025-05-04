<?php

namespace App\Helpers;

enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
}

class Logger
{
    public static function log($message, $context, LogLevel $level = LogLevel::INFO): void
    {
        $method = $level->value;
        \Log::$method($message, [
            'context' => $context,
            'user' => (auth()->user()?->id ?? '') . request()->ip(),
            'url' => request()->method() . ': ' . request()->url(),
        ]);
    }

    public static function error($message, $context): void
    {
        self::log($message, $context, LogLevel::ERROR);
    }

    public static function warning($message, $context): void
    {
        self::log($message, $context, LogLevel::WARNING);
    }

    public static function info($message, $context): void
    {
        self::log($message, $context, LogLevel::INFO);
    }
}
