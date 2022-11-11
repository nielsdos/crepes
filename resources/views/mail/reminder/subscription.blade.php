@component('mail::message')
# {{ $greeting }}

We sturen u dit bericht om u eraan te herinneren dat u bent ingeschreven voor de cursus "{{ $course->title }}".

Sessies:
@foreach($sessions as $session)
- @dateTime($session->start) - @time($session->end) <br/>locatie: {{ $session->location }}
@endforeach

@component('mail::button', ['url' => $url])
Cursus bekijken
@endcomponent

Indien u deze berichten niet meer wenst te ontvangen, dan kunt u uw voorkeuren aanpassen in uw accountinstellingen.

@lang('Regards'),<br>{{ config('app.name') }}

@component('mail::subcopy')
@lang(
    "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser: [:actionURL](:actionURL)',
    [
        'actionText' => 'Cursus bekijken',
        'actionURL' => $url
    ]
)
@endcomponent
@endcomponent
