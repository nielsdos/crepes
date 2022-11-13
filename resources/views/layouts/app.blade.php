<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @if(! empty($metaTagDescription))<meta name="description" content="{{ $metaTagDescription }}">@endif
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title') - {{ config('app.title') }}</title>
	<link rel="dns-prefetch" href="https://fonts.gstatic.com">
	<script src="{{ url(mix('js/app.js')) }}" defer></script>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400&display=swap" rel="stylesheet">
    </noscript>
	<link href="{{ url(mix('css/app.css')) }}" rel="stylesheet">
    @isset($extraStyle)
    <link href="{!! $extraStyle !!}" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="{!! $extraStyle !!}" rel="stylesheet">
    </noscript>
    @endif
    <!-- This software is licensed under AGPLv3. Source is available at https://github.com/nielsdos/crepes -->
</head>
<body>
	@include('partials.navbar')
	<main class="py-4 container">
		@if(View::hasSection('titleicon'))
			<div class="d-flex">
				<div>
					<h4 class="mb-4 title iconcontainer">@yield('titleicon') @yield('title')</h4>
				</div>
				@if(View::hasSection('buttons'))
					<div class="ms-auto titlebuttons">@yield('buttons')</div>
				@endif
			</div>
		@endif
		@yield('content')
	</main>
	<footer>
		<div class="container">
			<span class="text-muted">&copy; {{ config('app.name') }}</span>
			<div class="float-end"><a href="{{ route('privacy') }}" target="_blank">Privacy policy</a></div>
		</div>
	</footer>
</body>
</html>
