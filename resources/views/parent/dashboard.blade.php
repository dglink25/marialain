{{-- resources/views/parent/dashboard.blade.php --}}
@extends('layouts.parent')

@section('title', 'Tableau de bord - Espace Parent')

@section('page-title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">@if($activeAcademicYear)
                                Année académique : <strong>{{ $activeAcademicYear->name }}</strong>
                            @endif</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Message de bienvenue personnalisé avec année académique -->
    <div class="col-12">
        <div class="card bg-success text-white border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="fas fa-hand-wave fa-3x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="h3 mb-2">Bonjour, {{ auth('parent')->user()->full_name }}!</h2>
                        <p class="mb-0 opacity-75">
                            Bienvenue dans votre espace parent.
                        </p>
                    </div>
                    <div class="d-none d-md-block">
                        <img src="{{ asset('ursule/img/family-icon.png') }}" alt="" style="max-height: 80px;" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques simplifiées avec données réelles -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-child fa-2x text-success"></i>
                </div>
                <h3 class="h2 fw-bold mb-1">{{ $totalStudents }}</h3>
                <p class="text-muted mb-0">Enfant(s) inscrit(s)</p>
                @if($activeAcademicYear)
                    <small class="text-muted">{{ $activeAcademicYear->name }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-credit-card fa-2x text-danger"></i>
                </div>
                <h3 class="h2 fw-bold mb-1">{{ $paymentPercentage }}%</h3>
                <p class="text-muted mb-0">Scolarité payée</p>
                @if($paymentPercentage < 100)
                    <small class="text-warning">Paiement en cours</small>
                @else
                    <small class="text-success">À jour</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Section Enfants avec données dynamiques -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent border-0 pt-4">
                <h2 class="h4 mb-0">
                    <i class="fas fa-users me-2 text-success"></i>
                    Mes enfants
                    @if($activeAcademicYear)
                        <small class="text-muted ms-2">({{ $activeAcademicYear->name }})</small>
                    @endif
                </h2>
            </div>
            <div class="card-body">
                @forelse($students as $student)
                    @php
                        $stats = App\Http\Controllers\ParentDashboardController::getStudentStats($student, $activeAcademicYear?->id);
                        $totalPaid = $student->payments->sum('amount');
                        $schoolFees = $student->classe?->school_fees ?? 0;
                        $paymentStatus = $schoolFees > 0 ? round(($totalPaid / $schoolFees) * 100) : 0;
                    @endphp
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="border rounded-4 p-4 hover-shadow">
                                <div class="row align-items-center">
                                    <!-- Info enfant -->
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="profile-avatar" style="width: 60px; height: 60px; font-size: 1.8rem;">
                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h3 class="h5 fw-bold mb-1">{{ $student->full_name }}</h3>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    {{ $student->classe->name ?? 'Classe non assignée' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions simplifiées -->
                                    <div class="col-md-5">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('parent.child.grades', $student) }}" 
                                               class="btn btn-outline-success flex-fill"
                                               aria-label="Voir les notes de {{ $student->first_name }}">
                                                <i class="fas fa-chart-line me-1"></i>
                                                Notes
                                            </a>
                                            @if($student->classe)
                                                <a href="{{ route('parent.child.timetable', $student) }}" 
                                                   class="btn btn-outline-success flex-fill"
                                                   aria-label="Voir l'emploi du temps de {{ $student->first_name }}">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Emploi du temps
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Statut rapide -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($paymentStatus >= 100)
                                                <span class="badge bg-success p-2">
                                                    <i class="fas fa-check-circle me-1"></i>Scolarité à jour
                                                </span>
                                            @elseif($paymentStatus >= 50)
                                                <span class="badge bg-warning p-2">
                                                    <i class="fas fa-clock me-1"></i>{{ $paymentStatus }}% payé
                                                </span>
                                            @else
                                                <span class="badge bg-danger p-2">
                                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $paymentStatus }}% payé
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-child fa-4x text-muted"></i>
                        </div>
                        <h3 class="h5 mb-3">Aucun enfant inscrit</h3>
                        <p class="text-muted mb-4">
                            Vous n'avez pas encore d'enfants inscrits dans notre établissement pour l'année {{ $activeAcademicYear?->name ?? 'en cours' }}.
                        </p>
                        <a href="{{ route('parent.contact') }}" class="btn btn-success">
                            <i class="fas fa-headset me-2"></i>
                            Contacter l'administration
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section Aide et Support -->
    <div class="col-md-5">
        <div class="card h-100 bg-success text-white">
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-4">
                    <i class="fas fa-headset fa-3x mb-3"></i>
                    <h3 class="h4 mb-2">Besoin d'aide ?</h3>
                    <p class="small opacity-75">
                        Notre équipe est là pour vous accompagner
                    </p>
                </div>
                
                <div class="mt-auto">
                    <div class="bg-white bg-opacity-10 rounded-4 p-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-phone-alt fa-lg"></i>
                            <div>
                                <span class="small opacity-75">Appelez-nous</span>
                                <div class="h6 mb-0">+229 01 97 21 20 45</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-4 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-envelope fa-lg"></i>
                            <div>
                                <span class="small opacity-75">Envoyez-nous un email</span>
                                <div class="h6 mb-0">cpegmariealain@gmail.com</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.bg-opacity-20 {
    --bs-bg-opacity: 0.2;
}

.profile-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #198754, #20c997);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.3rem;
}

/* Mode contraste élevé */
@media (prefers-contrast: high) {
    .card {
        border: 2px solid black;
    }
    
    .btn {
        border: 2px solid currentColor;
    }
}

/* Pour les petits écrans */
@media (max-width: 576px) {
    .profile-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.625rem 1rem;
    }
}
</style>
@endsection