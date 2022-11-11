@extends('layouts.app')
@section('title', __('acts.user_management'))
@section('titleicon')
@svg('solid/users')
@endsection
@section('buttons')
<div class="btn-group">
    <a class="btn btn-light" href="{{ route('account.export.csv') }}" target="_blank">@svg('solid/file-lines', 'fa-1x') {{ __('acts.export_csv') }}</a>
    <a class="btn btn-success" href="{{ route('account.export.xlsx') }}" target="_blank">@svg('solid/file-excel', 'fa-1x') {{ __('acts.export_excel') }}</a>
</div>
@endsection
@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('account.index') }}">{{ __('acts.user_management') }}</a></li>
        @isset($q)
        <li class="breadcrumb-item active" aria-current="page">{{ __('acts.search_results_for', ['for' => $q]) }}</li>
        @endisset
    </ol>
</nav>
@include('partials.messages')
<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">{{ __('acts.delete_account') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('acts.delete_account_confirmation') }}
                    <br>
                    {{ __('acts.action_not_undoable') }}
                </p>
            </div>
            <div class="modal-footer">
                <button id="confirmRemove" type="button" class="btn btn-danger">{{ __('common.yes') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.no') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-sm-8 offset-sm-2">
        <form action="{{ route('account.index') }}">
            <div class="input-group">
                <input type="text" id="q" name="q" class="form-control{{ $hasSyntaxError ? ' is-invalid' : '' }}" value="{{ $q }}" placeholder="{{ __('acts.user_search_placeholder') }}" autofocus>
                <button class="btn btn-ico btn-primary" type="submit">@svg('solid/magnifying-glass', 'ico-l--1')</button>
                @if($hasSyntaxError)
                    <span class="invalid-feedback" role="alert">{{ __('acts.search_invalid_syntax') }}</span>
                @endif
            </div>
            <span class="text-secondary small-msg">{{ __('acts.search_pattern_matching_hint') }}</span>
        </form>
    </div>
</div>
<script>!function(){var e="";document.getElementById("confirmRemove").addEventListener("click",function(t){document.getElementById(e).submit()},!1),document.getElementById("removeModal").addEventListener("show.bs.modal",function(t){e=t.relatedTarget.dataset.id},!1),document.getElementById("removeModal").addEventListener("hide.bs.modal",function(t){e=""},!1);var t=document.getElementById("q");t.setSelectionRange&&t.setSelectionRange(t.value.length,t.value.length)}();</script>
<div class="row mb-3">
    <div class="col">
        <div class="table-responsive">
            <table class="table table-hover table-middle">
                <thead>
                    <tr>
                        <th scope="col">{{ __('common.lastname') }}</th>
                        <th scope="col">{{ __('common.firstname') }}</th>
                        <th scope="col">{{ __('common.email') }}</th>
                        <th scope="col">{{ __('common.function') }}</th>
                        <th scope="col">{{ __('common.member_nr') }}</th>
                        <th scope="col">{{ __('common.status') }}</th>
                        <th scope="col">{{ __('common.role') }}</th>
                        <th scope="col">{{ __('acts.acts') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->lastname }}</td>
                            <td>{{ $user->firstname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                            @if($user->function)
                                {{ $user->function }}
                            @else
                                <em>{{ __('common.not_provided') }}</em>
                            @endif
                            </td>
                            <td>
                            @if($user->member_nr)
                                {{ $user->member_nr }}
                            @else
                                <em>{{ __('common.not_provided') }}</em>
                            @endif
                            </td>
                            <td class="cursor-default">
                                @if($user->hasVerifiedEmail())
                                @svg('solid/check', 'fa-1x text-success', ['data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => __('common.verified')])
                                @else
                                @svg('solid/x', 'fa-1x text-danger', ['data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => __('common.not_verified')])
                                @endif
                                @if($user->trashed())
                                @svg('solid/toggle-off', 'fa-1x text-danger', ['data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => __('common.deactivated')])
                                @else
                                @svg('solid/toggle-on', 'fa-1x text-success', ['data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => __('common.activated')])
                                @endif
                            </td>
                            <td>{{ __('common.role-' . $user->perms) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('account.edit', $user) }}" class="btn btn-sm btn-ico btn-primary">
                                        @svg('solid/pencil', 'ico-sm--1')
                                    </a>
                                    <a href="{{ route('subscriptions.show', $user) }}" class="btn btn-sm btn-ico btn-light">
                                        @svg('solid/pen-to-square', 'ico-sm--1')
                                    </a>
                                    @if($user->id !== Auth::id())
                                        <form method="post" id="delete-{{ $user->id }}" action="{{ route('account.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <a href="#removeModal" class="btn btn-sm btn-ico btn-danger" data-bs-toggle="modal" data-bs-target="#removeModal" data-id="delete-{{ $user->id }}">
                                            @svg('solid/trash-can', 'ico-sm--1')
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->count() === 0)
            <p class="text-center mt-3">
                @if($users->currentPage() > $users->lastPage())
                    {{ __('common.no_results_after_last_page') }}
                @else
                    {{ __('common.no_results') }}
                @endif
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col">
        {{ $users->appends(request()->input())->links() }}
    </div>
</div>
@endsection
