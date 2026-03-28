@extends('layouts.app')

@section('content')

@auth
<!-- Header Principal épuré -->
<div class="mb-10" data-aos="fade-down">
    <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-200 p-8 md:p-10">
        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-xl bg-blue-600 flex items-center justify-center shadow-md">
                    <i class="fas fa-user-circle text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-1">Bienvenue, <span class="font-semibold">{{ auth()->user()->name ?? 'Utilisateur' }}</span></h1>
                    <p class="text-gray-500 text-sm">Dernière connexion à {{ now()->format('d/m/Y H:i') }} (GMT)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message d'information -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-info-circle text-amber-600 text-lg"></i>
        </div>
        <div class="text-sm text-gray-700">
            <span class="font-medium text-amber-800">Mises à jour en cours</span>
            <p class="mt-1">Des améliorations sont actuellement déployées sur la plateforme. En cas de difficulté, contactez le support via :</p>
            <div class="flex gap-4 mt-3">
                <a href="https://wa.me/29994119476" class="flex items-center gap-2 text-gray-600 hover:text-green-600 transition-colors">
                    <i class="fab fa-whatsapp text-lg"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="mailto:dondiegue21@gmail.com" class="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="far fa-envelope text-lg"></i>
                    <span>Email</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Contenu principal -->
<div class="flex flex-col lg:flex-row gap-8">
    <!-- Contenu principal -->
    <div class="lg:w-3/4">
        @switch(auth()->user()->id)
            @case(3)
                <!-- Directeur Primaire -->
                <div class="space-y-8">
                    <!-- Statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Élèves inscrits</p>
                                    <p class="text-3xl font-semibold text-gray-900 mt-2">{{ $primaryStudentsCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fas fa-users text-xl text-blue-600"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500">Section primaire</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Enseignants</p>
                                    <p class="text-3xl font-semibold text-gray-900 mt-2">{{ $primaryTeacherCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                                    <i class="fas fa-chalkboard-teacher text-xl text-emerald-600"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500">Équipe pédagogique</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Classes</p>
                                    <p class="text-3xl font-semibold text-gray-900 mt-2">{{ $primaryClassCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-violet-50 flex items-center justify-center">
                                    <i class="fas fa-school text-xl text-violet-600"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500">Répartition des classes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modules de gestion -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Gestion primaire</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <a href="{{ route('primaire.enseignants.enseignants') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-200 hover:shadow-md transition-all group">
                                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4 group-hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-user-tie text-xl text-blue-600"></i>
                                </div>
                                <h3 class="font-medium text-gray-900 mb-2">Enseignants</h3>
                                <p class="text-sm text-gray-500 mb-4">Gérer les affectations et le suivi</p>
                                <span class="text-sm text-blue-600 group-hover:text-blue-700 flex items-center gap-1">
                                    Accéder
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </a>

                            <a href="{{ route('primaire.classe.classes') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-emerald-200 hover:shadow-md transition-all group">
                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center mb-4 group-hover:bg-emerald-100 transition-colors">
                                    <i class="fas fa-door-open text-xl text-emerald-600"></i>
                                </div>
                                <h3 class="font-medium text-gray-900 mb-2">Classes</h3>
                                <p class="text-sm text-gray-500 mb-4">Consulter et organiser les classes</p>
                                <span class="text-sm text-emerald-600 group-hover:text-emerald-700 flex items-center gap-1">
                                    Accéder
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </a>

                            <a href="{{ url('/primaire/ecoliers/liste') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-violet-200 hover:shadow-md transition-all group">
                                <div class="w-12 h-12 rounded-lg bg-violet-50 flex items-center justify-center mb-4 group-hover:bg-violet-100 transition-colors">
                                    <i class="fas fa-user-graduate text-xl text-violet-600"></i>
                                </div>
                                <h3 class="font-medium text-gray-900 mb-2">Élèves</h3>
                                <p class="text-sm text-gray-500 mb-4">Suivi des inscriptions et résultats</p>
                                <span class="text-sm text-violet-600 group-hover:text-violet-700 flex items-center gap-1">
                                    Accéder
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                @break

            @case(4)
                <!-- Censeur -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="flex items-start gap-6">
                        <div class="w-16 h-16 rounded-xl bg-cyan-50 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-2xl text-cyan-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Gestion académique</h2>
                            <p class="text-gray-500 mb-6">Supervision des classes et suivi pédagogique</p>
                            <a href="{{ route('censeur.classes.index') }}" class="inline-flex items-center gap-2 bg-cyan-600 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-cyan-700 transition-colors">
                                <i class="fas fa-list"></i>
                                Accéder aux classes
                            </a>
                        </div>
                    </div>
                </div>
                @break

            @case(6)
                <!-- Admin Inscriptions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="flex items-start gap-6">
                        <div class="w-16 h-16 rounded-xl bg-orange-50 flex items-center justify-center">
                            <i class="fas fa-user-check text-2xl text-orange-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Gestion des inscriptions</h2>
                            <p class="text-gray-500 mb-6">Validation et suivi des nouvelles inscriptions</p>
                            <a href="{{ route('admin.students.pending') }}" class="inline-flex items-center gap-2 bg-orange-600 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-orange-700 transition-colors">
                                <i class="fas fa-tasks"></i>
                                Inscriptions en attente
                            </a>
                        </div>
                    </div>
                </div>
                @break

            @case(7)
                <!-- Admin Général -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="flex items-start gap-6">
                        <div class="w-16 h-16 rounded-xl bg-gray-900 flex items-center justify-center">
                            <i class="fas fa-cogs text-2xl text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Administration générale</h2>
                            <p class="text-gray-500 mb-6">Configuration complète du système</p>
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                                <i class="fas fa-rocket"></i>
                                Tableau de bord admin
                            </a>
                        </div>
                    </div>
                </div>
                @break

            @default
                <!-- Enseignant -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <div class="w-20 h-20 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-2xl text-teal-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Espace enseignant</h2>
                    <p class="text-gray-500 mb-6">Accédez à vos classes et gérez votre espace</p>
                    <a href="{{ route('teacher.classes') }}" class="inline-flex items-center gap-2 bg-teal-600 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-teal-700 transition-colors">
                        <i class="fas fa-eye"></i>
                        Voir mes classes
                    </a>
                </div>
                @break
        @endswitch
    </div>
</div>

@else
<!-- Page publique -->
<div class="min-h-[80vh] flex flex-col justify-center max-w-4xl mx-auto">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <div class="flex justify-center mb-6">
            <div class="w-40 h-40 rounded-2xl flex items-center justify-center shadow-lg">
                <img src="logo.png" alt="" class="fas fa-school text-3xl text-white">
            </div>
        </div>
        <h1 class="text-4xl md:text-5xl font-light text-gray-900 mb-4">
            CS <span class="font-semibold">MARIE-ALAIN</span>
        </h1>
        <p class="text-xl text-gray-500 max-w-2xl mx-auto">
            Excellence éducative de la maternelle à la terminale
        </p>
    </div>

    <!-- Connexion -->
    <div class="max-w-md mx-auto w-full">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-2xl text-gray-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Accès sécurisé</h2>
                <p class="text-sm text-gray-500">Connectez-vous à votre espace personnel</p>
            </div>
            <a href="{{ route('login') }}" 
               class="block w-full bg-blue-600 text-white text-center font-medium py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Se connecter
            </a>
            <p class="text-xs text-center text-gray-400 mt-4">
                Contactez l'administration pour obtenir vos identifiants
            </p>
        </div>
    </div>
</div>
@endauth

@endsection

@push('styles')
<style>
    /* Transitions douces */
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    
    /* Effets de hover subtils */
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    }
    
    /* Bordures et séparations */
    .border-gray-200 {
        border-color: #e5e7eb;
    }
    
    /* Typographie professionnelle */
    .font-light { font-weight: 300; }
    .font-medium { font-weight: 500; }
    .font-semibold { font-weight: 600; }
    
    /* Espacements cohérents */
    .gap-6 { gap: 1.5rem; }
    .gap-4 { gap: 1rem; }
</style>
@endpush