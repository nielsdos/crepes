<?php

namespace App\Services\Exports;

class UsersExportable implements Exportable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>|\Illuminate\Database\Query\Builder  $builder
     */
    public function __construct(private readonly \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $builder)
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->builder->orderBy('lastname')->orderBy('firstname')->get();
    }

    public function map(mixed $data): array
    {
        return [
            $data->lastname,
            $data->firstname,
            $data->email,
            $data->function,
            $data->member_nr,
            (bool) $data->hasVerifiedEmail(),
            ! $data->trashed(),
            __('common.role-'.$data->perms),
        ];
    }

    public function heading(): array
    {
        return [
            __('common.lastname'),
            __('common.firstname'),
            __('common.email'),
            __('common.function'),
            __('common.member_nr'),
            __('common.verified'),
            __('common.activated'),
            __('common.role'),
        ];
    }
}
