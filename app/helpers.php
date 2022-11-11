<?php

function old_str(string $key, ?string $default = null): string|null
{
    $value = old($key, $default);

    return is_string($value) ? $value : $default;
}
