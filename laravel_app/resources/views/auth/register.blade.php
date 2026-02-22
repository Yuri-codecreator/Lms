@extends('layout', ['title' => 'Register'])

@section('content')
<h2>Create an Account</h2>

@if ($errors->any())
    <p class="error">{{ $errors->first() }}</p>
@endif

<form method="POST" action="{{ route('register.store') }}">
    @csrf
    <input type="text" name="fullname" placeholder="Full Name" value="{{ old('fullname') }}" required><br><br>
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required><br><br>
    <input type="password" name="password" placeholder="Password (min 8 chars)" required><br><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br><br>
    <button type="submit">Register</button>
</form>
<p><a href="{{ route('login') }}">Already registered? Login</a></p>
@endsection
