@extends('layouts.app')
@section('titleicon')
@svg('solid/pen-to-square')
@endsection
@section('title')
@if($adminView)
{{ __('acts.subscriptions') }}
@else
{{ __('acts.my_subscriptions') }}
@endif
@endsection
@section('content')
@if($adminView)
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('account.index') }}">{{ __('acts.user_management') }}</a></li>
        @if(session('account_index_q'))
        <li class="breadcrumb-item"><a href="{{ App\Http\Controllers\AccountController::routeWithQuery('account.index') }}">{{ __('acts.search_results_for', ['for' => session('account_index_q')]) }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ __('acts.subscriptions_for', ['account' => $user->email]) }}</li>
    </ol>
</nav>
@endif
@if($subscriptions->count() === 0)
    <p class="text-center">
        @if(!$adminView || Auth::id() === $user->id)
            {{ __('common.no_subscriptions_for_me') }}
        @else
            {{ __('common.no_subscriptions_for_this_person') }}
        @endif
    </p>
@else
    <div class="row mb-3">
        <div class="col">
            <div class="table-responsive">
                <table class="table table-hover table-middle">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('common.course') }}</th>
                            <th scope="col">{{ ucfirst(__('common.session_group')) }}</th>
                            <th scope="col">{{ __('common.subscribed_on') }}</th>
                            @if($showStatus)
                            <th scope="col">{{ __('common.status') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            @php
                            $course = $subscription->sessionGroup->course;
                            $groupIndex = $subscription->groupIndex();
                            @endphp
                            <tr>
                                <td><a href="{{ route('course.show', [$course->id, $course->slug]) }}#groep{{ $groupIndex }}">{{ $course->title }}</a></td>
                                <td>#{{ $groupIndex }}</td>
                                <td>@date($subscription->created_at)</td>
                                @if($showStatus)
                                <td>
                                @if($subscription->trashed())
                                    @svg('solid/toggle-off', ['class' => 'svg-inline--fa fa-lg fa-fw text-danger', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'title' => __('common.deactivated')])
                                @else
                                    @svg('solid/toggle-on', ['class' => 'svg-inline--fa fa-lg fa-fw text-success', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'title' => __('common.activated')])
                                @endif
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            {{ $subscriptions->links() }}
        </div>
    </div>
@endif
@endsection
