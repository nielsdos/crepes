<?php

namespace App\Services;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

final class CustomUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);

        $this->withQuery(function ($query) {
            // @phpstan-ignore-next-line
            $query->withTrashed();
        });
    }
}
