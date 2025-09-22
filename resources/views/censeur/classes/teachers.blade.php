@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Titre -->
<h2 class="text-3xl font-extrabold text-between text-black-700 mb-8">
        Enseignants de la classe : {{ $class->name }}
    </h2>

    <!-- Tableau -->
    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            @if($teachers->count() > 0)
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center">
                        <thead class="table-primary sticky-top">
                            <tr>
                                <th scope="col">N°</th>
                                <th scope="col">Nom & Prénoms</th>
                                <th scope="col">Sexe</th>
                                <th scope="col">Email</th>
                                <th scope="col">Téléphone</th>
                                <th scope="col">Matières enseignées</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teachers as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">{{ $data['teacher']->name }}</td>
                                    <td>{{ $data['teacher']->gendre ?? '--' }}</td>
                                    <td class="text-break">{{ $data['teacher']->email ?? '--' }}</td>
                                    <td>{{ $data['teacher']->phone ?? '--' }}</td>
                                    <td>{{ $data['subjects']->join(', ') }}</td>
                                    <td>
                                        <a href="{{ route('enseignants.show', $data['teacher']->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                           <i class="bi bi-person-lines-fill"></i> Voir le profil
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center p-4 mb-0">
                    Aucun enseignant trouvé pour cette classe.
                </p>
            @endif
        </div>
    </div>

    <!-- Boutons -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('censeur.classes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>

        <a href="{{ route('enseignants.export', $class->id) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Télécharger PDF
        </a>
    </div>
</div>

<!-- Style personnalisé -->
<style>
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table td, .table th {
        vertical-align: middle;
    }

    h3.text-center.text-primary {
    font-size: 2.1rem;
}

</style>
@endsection
