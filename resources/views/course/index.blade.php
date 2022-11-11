@extends('layouts.app')
@section('title', __('acts.courses'))
@section('titleicon')
@svg('solid/book')
@endsection
@section('buttons')

<div class="d-flex">
	<div class="dropdown me-1">
		<button class="btn btn-primary dropdown-toggle" id="yearDropDown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		{{ $yearDisplay }}
		</button>

		<div class="dropdown-menu" aria-labelledby="yearDropDown">
			@foreach($years as list($_year, $_yearDisplay))
			<a class="dropdown-item" href="{{ route('course.index') }}/?y={{ $_year }}">{{ $_yearDisplay }}</a>
			@endforeach
		</div>
	</div>

	<button class="btn btn-ico btn-light question-btn" data-bs-toggle="collapse" data-bs-target="#help" aria-expanded="false" aria-controls="help">
        @svg('solid/circle-question')
	</button>
</div>
@endsection
@section('content')
@include('partials.messages')
<div class="collapse" id="help">
    <div class="shadow-sm rounded bd-callout bd-callout-primary">
        <h4>Help</h4>
        {!! \Illuminate\Support\Str::markdown(__('common.course_help'), ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
    </div>
	<br>
</div>

@if($pastCourses->count() === 0 && $futureCourses->count() === 0)
<p class="text-center">{{ __('common.no_courses_yet') }}</p>
@else

@if($futureCourses->count() > 0)
<div class="row row-cols-1 row-cols-md-2 g-4 mb-4-5">
	@foreach($futureCourses as $course)
		@include('partials.course-card-full', ['course' => $course, 'extra' => false])
	@endforeach
</div>
@endif

@if($pastCourses->count() > 0)
@if($futureCourses->count() > 0)
<div class="row">
    <div class="col">
        <h4 class="smalltitle mb-3">@svg('solid/clock', 'fa-xs') {{ __('common.past_courses') }}</h4>
    </div>
</div>
@endif

<div class="row row-cols-1 row-cols-md-2 g-4">
	@foreach($pastCourses as $course)
		@include('partials.course-card-full', ['course' => $course, 'extra' => false])
	@endforeach
</div>
@endif

@endif

@include('partials.overflow-tooltip')

@endsection
