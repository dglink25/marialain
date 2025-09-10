@extends('layouts.app')
@section('content')
    <h2>Invitations</h2>
    <form method="POST" action="{{ route('invitations.store') }}">@csrf
        <label>Email <input name="email" /></label>
        <label>Téléphone <input name="phone" /></label>
        <label>Rôle <select name="role">
            <option value="censeur">Censeur</option>
            <option value="secretaire">Secrétaire</option>
            <option value="directeur_primaire">Directeur Primaire</option>
            <option value="surveillant">Surveillant</option>
        </select></label>
        <button>Inviter</button>
    </form>

    <h3>Liste</h3>
    <table>
        <thead><tr><th>Email</th><th>Phone</th><th>Rôle</th><th>Expires</th><th>Acceptée</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($invitations as $i)
            <tr>
                <td>{{ $i->email }}</td>
                <td>{{ $i->phone }}</td>
                <td>{{ $i->role }}</td>
                <td>{{ $i->expires_at }}</td>
                <td>{{ $i->accepted ? 'oui':'non' }}</td>
                <td>
                    <form method="POST" action="{{ route('invitations.destroy',$i) }}" style="display:inline">@csrf @method('DELETE')<button>Révoquer</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $invitations->links() }}
@endsection