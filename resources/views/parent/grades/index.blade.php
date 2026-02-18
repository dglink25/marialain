{{-- resources/views/parent/grades/index.blade.php --}}
@extends('layouts.parent')

@section('title', 'Notes de ' . $student->full_name)

@section('page-title', 'Bulletin de notes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $student->full_name }}</li>
@endsection

@section('content')
<div class="container-fluid p-3 p-md-4 bg-light min-vh-100">
    <!-- En-tête avec informations élève -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h2 class="h3 mb-2 fw-bold">{{ $student->full_name }}</h2>
                            <p class="mb-1">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Classe: {{ $student->classe->name }}
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Année scolaire: {{ $activeYear->name }}
                            </p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par trimestre -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <ul class="nav nav-pills nav-fill" id="trimestreTabs" role="tablist">
                        @foreach([1, 2, 3] as $t)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $t == 1 ? 'active' : '' }} text-success" 
                                        id="trimestre{{ $t }}-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#trimestre{{ $t }}" 
                                        type="button" 
                                        role="tab"
                                        aria-selected="{{ $t == 1 ? 'true' : 'false' }}">
                                    <i class="fas fa-star me-2"></i>
                                    Trimestre {{ $t }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu des trimestres -->
    <div class="tab-content" id="trimestreTabsContent">
        @foreach([1, 2, 3] as $trimestre)
            <div class="tab-pane fade {{ $trimestre == 1 ? 'show active' : '' }}" 
                 id="trimestre{{ $trimestre }}" 
                 role="tabpanel">
                
                @php
                    $trimestreStats = $stats[$trimestre] ?? null;
                @endphp

                @if(!$trimestreStats || empty($trimestreStats['subjects']))
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucune note disponible pour le trimestre {{ $trimestre }}.
                    </div>
                @else
                    <!-- Tableau des notes -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-table me-2 text-success"></i>
                                Détail des notes - Trimestre {{ $trimestre }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0" style="min-width: 1400px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle" style="width: 50px;">N°</th>
                                            <th rowspan="2" class="align-middle" style="min-width: 200px;">Matières</th>
                                            <th rowspan="2" class="align-middle" style="min-width: 150px;">Enseignant</th>
                                            <th rowspan="2" class="text-center align-middle" style="width: 60px;">Coef</th>
                                            <th colspan="5" class="text-center">Interrogations</th>
                                            <th rowspan="2" class="text-center align-middle" style="min-width: 80px;">Moy I</th>
                                            <th colspan="2" class="text-center">Devoirs</th>
                                            <th rowspan="2" class="text-center align-middle" style="min-width: 80px;">Moy/20</th>
                                            <th rowspan="2" class="text-center align-middle" style="min-width: 80px;">Moy Coef</th>
                                            <th rowspan="2" class="text-center align-middle" style="min-width: 60px;">Rang</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center" style="min-width: 50px;">I1</th>
                                            <th class="text-center" style="min-width: 50px;">I2</th>
                                            <th class="text-center" style="min-width: 50px;">I3</th>
                                            <th class="text-center" style="min-width: 50px;">I4</th>
                                            <th class="text-center" style="min-width: 50px;">I5</th>
                                            <th class="text-center" style="min-width: 50px;">D1</th>
                                            <th class="text-center" style="min-width: 50px;">D2</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjects as $index => $classSubject)
                                            @php
                                                $subjectStats = $trimestreStats['subjects'][$classSubject->subject_id] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $classSubject->subject->name }}</strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $classSubject->teacher->name }}</small>
                                                </td>
                                                <td class="text-center">{{ $classSubject->coefficient }}</td>
                                                
                                                @if($subjectStats)
                                                    @php
                                                        $interros = $subjectStats['interrogations'] ?? collect();
                                                        $devoirs = $subjectStats['devoirs'] ?? collect();
                                                    @endphp
                                                    
                                                    <!-- Interrogations I1 à I5 -->
                                                    @for($i = 0; $i < 5; $i++)
                                                        <td class="text-center">
                                                            @if(isset($interros[$i]))
                                                                <span class="badge bg-light text-dark">{{ $interros[$i] }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    @endfor
                                                    
                                                    <!-- Moyenne Interrogation -->
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $subjectStats['moyenne_interro'] }}</span>
                                                    </td>
                                                    
                                                    <!-- Devoirs D1 et D2 -->
                                                    @for($i = 0; $i < 2; $i++)
                                                        <td class="text-center">
                                                            @if(isset($devoirs[$i]))
                                                                <span class="badge bg-warning">{{ $devoirs[$i] }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    @endfor
                                                    
                                                    <!-- Moyenne sur 20 -->
                                                    <td class="text-center">
                                                        <strong class="text-success">{{ $subjectStats['moyenne_sur_20'] }}</strong>
                                                    </td>
                                                    
                                                    <!-- Moyenne Coef -->
                                                    <td class="text-center">
                                                        <strong class="text-primary">{{ $subjectStats['moyenne_coeff'] }}</strong>
                                                    </td>
                                                    
                                                    <!-- Rang (simplifié) -->
                                                    <td class="text-center">
                                                        @if($subjectStats['rang'])
                                                            <span class="badge bg-secondary">{{ $subjectStats['rang'] }}<sup>ème</sup></span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    
                                                @else
                                                    <td colspan="13" class="text-center text-muted">
                                                        Aucune note
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="13" class="text-end fw-bold">Moyenne Générale</td>
                                            <td class="text-center fw-bold text-success fs-5">
                                                {{ number_format($trimestreStats['moyenne_generale'], 2) }}
                                            </td>
                                            <td class="text-center"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Informations conduite et punitions -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Note de conduite</h6>
                                            <h3 class="mb-0">{{ number_format($trimestreStats['conduite_finale'], 2) }}/20</h3>
                                            <small class="text-white-50">
                                                Base: {{ number_format($trimestreStats['conduite_base'], 2) }} | 
                                                Punition reçu: {{ $trimestreStats['total_punishment_hours']/2 }} pts
                                            </small>
                                        </div>
                                        <i class="fas fa-gavel fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Total coefficients</h6>
                                            <h3 class="mb-0">{{ $trimestreStats['total_coeff'] }}</h3>
                                            <small>Matières notées: {{ $trimestreStats['matieres_avec_notes'] }}</small>
                                        </div>
                                        <i class="fas fa-calculator fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Rang</h6>
                                            <h3 class="mb-0">
                                                 @if(isset($stats[$trimestre]['rang_general']))
                                                    {{ $stats[$trimestre]['rang_general'] }}/{{ $effectif }}
                                                @else
                                                    -/{{ $effectif }}
                                                @endif
                                            </h3>
                                            <small>Effectif: {{ $effectif }} élèves</small>
                                        </div>
                                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comparaisons -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-success text-white">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-arrow-up fa-2x mb-3"></i>
                                    <h4 class="h5 mb-2">Plus forte moyenne</h4>
                                    <h2 class="display-6 fw-bold mb-0">
                                        {{ number_format($classAverages[$trimestre]['plus_forte'] ?? 0, 2) }}
                                    </h2>
                                    <small class="text-white-50">Trimestre {{ $trimestre }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-warning text-white">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-arrow-down fa-2x mb-3"></i>
                                    <h4 class="h5 mb-2">Plus faible moyenne</h4>
                                    <h2 class="display-6 fw-bold mb-0">
                                        {{ number_format($classAverages[$trimestre]['plus_faible'] ?? 0, 2) }}
                                    </h2>
                                    <small class="text-white-50">Trimestre {{ $trimestre }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-info text-white">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-chart-bar fa-2x mb-3"></i>
                                    <h4 class="h5 mb-2">Moyenne de la classe</h4>
                                    <h2 class="display-6 fw-bold mb-0">
                                        {{ number_format($classAverages[$trimestre]['moyenne_classe'] ?? 0, 2) }}
                                    </h2>
                                    <small class="text-white-50">Trimestre {{ $trimestre }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Punitions reçues -->
    @if($punishments->isNotEmpty())
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    Punitions reçues ({{ $punishments->sum('hours') }} heures au total)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Motif</th>
                                <th>Heures</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($punishments as $punishment)
                                <tr>
                                    <td>{{ $punishment->date_punishment->format('d/m/Y') }}</td>
                                    <td>{{ $punishment->reason }}</td>
                                    <td>{{ $punishment->hours }} heure(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total des heures</td>
                                <td class="fw-bold">{{ $punishments->sum('hours') }} heures</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Styles personnalisés */
.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #198754, #157347);
    color: white;
}

.nav-pills .nav-link {
    color: #198754;
    border-radius: 50px;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: rgba(25, 135, 84, 0.1);
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: rgba(25, 135, 84, 0.05);
    transition: background-color 0.3s ease;
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.bg-opacity-50 {
    opacity: 0.5;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .table {
        font-size: 0.85rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    .nav-pills .nav-link {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    
    h2.h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .table thead th {
        font-size: 0.7rem;
    }
}
</style>
@endsection