{{-- resources/views/parent/dashboard.blade.php --}}
@extends('layouts.parent')

@section('title', 'Tableau de bord - Espace Parent')

@section('page-title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active">Accueil</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Carte de bienvenue -->
    <div class="col-12">
        <div class="card border-0 bg-gradient-orange text-white overflow-hidden" style="background: linear-gradient(135deg, #ff6b35, #ff8c5a); border-radius: 20px;">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="fas fa-school fa-6x"></i>
                </div>
                <h4 class="fw-bold mb-2 animate__animated animate__fadeInDown">
                    Bonjour, {{ auth('parent')->user()->full_name }}!
                </h4>
                <p class="mb-0 opacity-75 animate__animated animate__fadeInUp">
                    Bienvenue dans votre espace parent. Suivez la scolarité de vos enfants en temps réel.
                </p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 hover-card">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-child fa-2x text-primary"></i>
                </div>
                <h3 class="fw-bold mb-0">{{ auth('parent')->user()->students->count() }}</h3>
                <p class="text-muted mb-0">Enfants inscrits</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 hover-card">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-star fa-2x text-success"></i>
                </div>
                <h3 class="fw-bold mb-0">12</h3>
                <p class="text-muted mb-0">Nouveautés</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 hover-card">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-calendar fa-2x text-warning"></i>
                </div>
                <h3 class="fw-bold mb-0">3</h3>
                <p class="text-muted mb-0">Événements à venir</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 hover-card">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-credit-card fa-2x text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0">75%</h3>
                <p class="text-muted mb-0">Paiements effectués</p>
            </div>
        </div>
    </div>

    <!-- Liste des enfants -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-users me-2 text-orange"></i>Mes enfants</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse(auth('parent')->user()->students as $student)
                        <div class="col-md-6">
                            <div class="card border-0 bg-light hover-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="profile-avatar me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1">{{ $student->full_name }}</h5>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-graduation-cap me-1"></i>{{ $student->classe->name ?? 'Non assigné' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-4 text-center">
                                            <div class="bg-white rounded-3 p-2">
                                                <small class="text-muted d-block">Moyenne</small>
                                                <strong class="text-orange">14.5</strong>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="bg-white rounded-3 p-2">
                                                <small class="text-muted d-block">Absences</small>
                                                <strong class="text-warning">2</strong>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="bg-white rounded-3 p-2">
                                                <small class="text-muted d-block">Rang</small>
                                                <strong class="text-success">8<small class="text-muted">/25</small></strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('parent.child.grades', $student) }}" class="btn btn-sm btn-outline-orange flex-fill">
                                            <i class="fas fa-chart-line me-1"></i>Notes
                                        </a>
                                        <a href="{{ route('parent.child.attendance', $student) }}" class="btn btn-sm btn-outline-orange flex-fill">
                                            <i class="fas fa-calendar-check me-1"></i>Présences
                                        </a>
                                        <a href="{{ route('parent.child.payments', $student) }}" class="btn btn-sm btn-outline-orange flex-fill">
                                            <i class="fas fa-credit-card me-1"></i>Paiements
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <img src="{{ asset('ursule/img/empty.svg') }}" alt="Aucun enfant" style="max-width: 200px;" class="mb-3">
                            <h5>Aucun enfant trouvé</h5>
                            <p class="text-muted">Vous n'avez pas encore d'enfants inscrits dans notre établissement.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières notes et calendrier -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-star me-2 text-orange"></i>Dernières notes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Élève</th>
                                <th>Matière</th>
                                <th>Note</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="profile-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem;">JD</div>
                                        <span>Jean Dupont</span>
                                    </div>
                                </td>
                                <td>Mathématiques</td>
                                <td><span class="badge bg-success">16/20</span></td>
                                <td>15/03/2024</td>
                                <td><a href="#" class="text-orange"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="profile-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem;">MM</div>
                                        <span>Marie Martin</span>
                                    </div>
                                </td>
                                <td>Français</td>
                                <td><span class="badge bg-warning">12/20</span></td>
                                <td>14/03/2024</td>
                                <td><a href="#" class="text-orange"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-orange"></i>Calendrier</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex align-items-center">
                        <div class="bg-orange text-white rounded-3 p-2 me-3 text-center" style="min-width: 50px;">
                            <div class="small">MAR</div>
                            <div class="fw-bold">20</div>
                        </div>
                        <div>
                            <h6 class="mb-1">Composition 1er trimestre</h6>
                            <small class="text-muted">Début des examens</small>
                        </div>
                    </div>
                    <div class="list-group-item d-flex align-items-center">
                        <div class="bg-info text-white rounded-3 p-2 me-3 text-center" style="min-width: 50px;">
                            <div class="small">VEN</div>
                            <div class="fw-bold">23</div>
                        </div>
                        <div>
                            <h6 class="mb-1">Réunion parents-profs</h6>
                            <small class="text-muted">Salle polyvalente</small>
                        </div>
                    </div>
                    <div class="list-group-item d-flex align-items-center">
                        <div class="bg-success text-white rounded-3 p-2 me-3 text-center" style="min-width: 50px;">
                            <div class="small">LUN</div>
                            <div class="fw-bold">26</div>
                        </div>
                        <div>
                            <h6 class="mb-1">Journée sportive</h6>
                            <small class="text-muted">Stade de l'école</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.bg-gradient-orange {
    background: linear-gradient(135deg, #ff6b35, #ff8c5a);
}

.btn-outline-orange {
    color: #ff6b35;
    border-color: #ff6b35;
    background: transparent;
    transition: all 0.3s ease;
}

.btn-outline-orange:hover {
    background: #ff6b35;
    color: white;
    border-color: #ff6b35;
}

.opacity-10 {
    opacity: 0.1;
}

.opacity-75 {
    opacity: 0.75;
}
</style>
@endsection