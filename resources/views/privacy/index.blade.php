@extends('layouts.app')

@section('title', 'Privacy policy')
@section('titleicon')
    @svg('solid/user-secret')
@endsection
@section('content')
    {!! $privacy_policy_html !!}
@endsection
