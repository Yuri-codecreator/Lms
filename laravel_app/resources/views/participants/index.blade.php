@extends('layout', ['title' => 'Participants'])

@section('content')
<h2>Participants</h2>

<form method="GET" action="{{ route('participants.index') }}">
    <input type="text" name="search" placeholder="Search by name/email" value="{{ request('search') }}">
    <input type="number" name="perpage" min="1" max="100" value="{{ request('perpage', 20) }}">
    <button type="submit">Filter</button>
</form>

<p>Total users: {{ $users->total() }}</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
    @forelse($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->fullname }}</td>
            <td>{{ $user->email }}</td>
        </tr>
    @empty
        <tr><td colspan="3">No users found.</td></tr>
    @endforelse
    </tbody>
</table>

{{ $users->links() }}
@endsection
