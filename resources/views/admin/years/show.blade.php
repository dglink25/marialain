@extends('layouts.app')
@section('content')
<h2>Année scolaire: {{ $year->name }}</h2>

<h3>Classes</h3>
<a href="{{ route('admin.classes.create') }}">Ajouter une Classe</a>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Secteur</th>
            <th>Level</th>
            <th>Série</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classes as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->sector }}</td>
                <td>{{ $c->level }}</td>
                <td>{{ $c->series ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h3>Acteurs</h3>
@isset($year)
    <a href="{{ route('admin.invitations.create', ['year_id' => $year->id]) }}">
        Inviter un acteur pour cette année
    </a>
@endisset
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Email</th>
            <th>Rôle</th>
            <th>Invité par</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invitations as $i)
            <tr>
                <td>{{ $i->email }}</td>
                <td>{{ $i->role }}</td>
                <td>{{ $i->createdBy->name ?? '-' }}</td>
                <td>{{ $i->accepted ? 'Acceptée' : 'En attente' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('admin.years.index') }}">Retour aux années scolaires</a>
@endsection
