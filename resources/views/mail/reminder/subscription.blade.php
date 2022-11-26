@component('mail::message')
# {{ $greeting }}

@lang("We are sending you this email to remind you of your registration for the course \":course\".", ['course' => $course->title])


@lang('common.sessions'):
@foreach($sessions as $session)
- @dateTime($session->start) - @time($session->end) <br/>@lang('common.location'): {{ $session->location }}
@endforeach

@component('mail::button', ['url' => $url])
@lang('View course')
@endcomponent

@lang('If you no longer wish to receive these reminders, you can disable this in your account settings.')


@lang('Regards'),<br>{{ config('app.name') }}

@component('mail::subcopy')
@lang(
    "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser: [:actionURL](:actionURL)',
    [
        'actionText' => __('View course'),
        'actionURL' => $url
    ]
)
@endcomponent
@endcomponent
