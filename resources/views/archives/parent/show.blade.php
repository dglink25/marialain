@extends('layouts.parent')

@section('title', 'Mes archives – Espace Parent')
@section('page-title', 'Archives des années passées')

@section('content')
<div class="row g-4">
    @forelse($archives as $year)
        <div class="col-md-4">
            <div class="card h-100 hover:shadow-lg transition">
                <div class="card-body text-center p-5">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                        <i class="fas fa-archive fa-2x text-success"></i>
                    </div>
                    <h2 class="h5 fw-bold mb-2">{{ $year->name }}</h2>
                    <p class="text-muted small mb-4">Données complètes de l'année archivée</p>
                    <a href="{{ route('archives.parent.show', $year->id) }}"
                       class="btn btn-success w-100">
                        <i class="fas fa-eye me-2"></i> Consulter
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
            <h3 class="h5 text-muted">Aucune archive disponible</h3>
            <p class="text-muted small">Vos données d'années passées apparaîtront ici.</p>
        </div>
    @endforelse
</div>
@endsection