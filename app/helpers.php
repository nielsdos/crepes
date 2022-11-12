<?php

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

function old_str(string $key, ?string $default = null): string|null
{
    $value = old($key, $default);

    return is_string($value) ? $value : $default;
}

/**
 * @param  Request  $request
 * @param  array<string|array<string|Rule>>  $rules
 * @param  string  $hash
 * @return array<string|int>
 *
 * @throws ValidationException
 */
function validateRequestWithHashFailureRedirect(Request $request, array $rules, string $hash): array
{
    try {
        return $request->validate($rules);
    } catch (ValidationException $exception) {
        $exception->redirectTo(url()->previous().'#'.$hash);
        throw $exception;
    }
}
