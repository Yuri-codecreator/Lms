@extends('layout', ['title' => 'Login'])

@section('content')
<h2>Login</h2>

@if ($errors->any())
    <p class="error">{{ $errors->first() }}</p>
@endif

<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>
<p><a href="{{ route('register') }}">Need an account? Register</a></p>
@endsection
