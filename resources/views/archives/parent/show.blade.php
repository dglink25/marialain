@extends('layouts.parent')

@section('title', 'Archives ' . $year->name . ' – Espace Parent')
@section('page-title', 'Archives ' . $year->name)

@section('content')
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('archives.parent.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour aux archives
    </a>
</div>

@if($records->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-folder-open fa-3x mb-3"></i>
        <p>Aucun enfant trouvé pour cette année archivée.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($records as $record)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-user-graduate fa-lg text-success"></i>
                            </div>
                            <div>
                                <h2 class="h5 fw-bold mb-0">{{ $record->last_name }} {{ $record->first_name }}</h2>
                                <p class="text-muted small mb-0">{{ $record->classe->name ?? '--' }} · {{ $record->entity->name ?? '--' }}</p>
                            </div>
                        </div>

                        {{-- Résultats académiques --}}
                        @if($record->moy_annuelle !== null)
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge {{ $record->moy_annuelle >= 10 ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2">
                                    Moy. annuelle : {{ number_format($record->moy_annuelle, 2, ',', '') }}/20
                                </span>
                                @if($record->rang_annuel)
                                    <span class="badge bg-secondary">Rang : {{ $record->rang_annuel }}ᵉ</span>
                                @endif
                            </div>
                        @endif

                        {{-- Statut délibération --}}
                        @if($record->statut_deliberation === 'passed')
                            <div class="alert alert-success py-2 mb-3 small">
                                <i class="fas fa-check-circle me-1"></i>
                                <strong>Admis(e)</strong>
                                @if($record->nextClass && $record->nextAcademicYear)
                                    → {{ $record->nextClass->name }} ({{ $record->nextAcademicYear->name }})
                                @endif
                            </div>
                        @elseif($record->statut_deliberation === 'repeated')
                            <div class="alert alert-danger py-2 mb-3 small">
                                <i class="fas fa-redo me-1"></i> <strong>Redoublant(e)</strong>
                            </div>
                        @endif

                        {{-- Moyennes par trimestre --}}
                        <div class="row g-2 mb-3 text-center">
                            @foreach([1,2,3] as $t)
                                @php $moy = $record->{'moy_trimestre_'.$t}; @endphp
                                <div class="col-4">
                                    <div class="border rounded p-2 small">
                                        <div class="fw-bold {{ $moy !== null && $moy >= 10 ? 'text-success' : 'text-danger' }}">
                                            {{ $moy !== null ? number_format($moy, 2, ',', '') : '–' }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.7rem">Trimestre {{ $t }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('archives.parent.child', [$year->id, $record->student_id]) }}"
                           class="btn btn-success w-100">
                            <i class="fas fa-eye me-2"></i> Détails complets
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection