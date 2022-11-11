<?php

namespace App\Services\Settings;

interface SettingsProvider
{
    public function get(string $key, string|int|bool $default): string|int|bool;

    public function set(string $key, string $value): void;
}
