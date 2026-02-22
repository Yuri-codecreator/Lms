<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LMS' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem auto; max-width: 900px; }
        .error { color: #b00020; }
        .nav { margin-bottom: 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; text-align: left; }
    </style>
</head>
<body>
@auth
    <div class="nav">
        <a href="{{ route('dashboard') }}">Dashboard</a> |
        <a href="{{ route('participants.index') }}">Participants</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
@endauth

@yield('content')
</body>
</html>
