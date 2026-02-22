@extends('layout', ['title' => 'Dashboard'])

@section('content')
<h2>Welcome, {{ auth()->user()->fullname }}!</h2>
<p>Your email: {{ auth()->user()->email }}</p>
@endsection
