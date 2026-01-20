@extends('layouts.app')

@section('content')

@auth
<!-- Header Principal -->
<div class="mb-10" data-aos="fade-down">
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-8 md:p-10">
        <div class="relative z-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Bienvenue, {{ auth()->user()->name ?? 'Utilisateur' }} !</h1>
            <p class="text-blue-100 text-lg max-w-2xl">
                Votre tableau de bord centralise la gestion de vos classes, enseignants et élèves en toute simplicité.
            </p>
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
        <div class="absolute right-20 top-5 text-white/20">
            <i class="fas fa-school text-8xl"></i>
        </div>
    </div>
</div>

<!-- Section principale avec sidebar -->
<div class="flex flex-col lg:flex-row gap-8">

    <!-- Contenu principal -->
    <div class="lg:w-3/4" data-aos="fade-up">
        <!-- Contenu spécifique selon rôle -->
        @switch(auth()->user()->id)
            @case(3)
                <!-- 🔹 Directeur Primaire -->
                <div class="space-y-8">
                    <!-- Statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg transform hover:-translate-y-1 transition-transform duration-300">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-blue-100 text-sm font-medium">Élèves inscrits</p>
                                    <p class="text-3xl font-bold mt-2">{{ $primaryStudentsCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/20">
                                <p class="text-sm text-blue-100">Section Primaire</p>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-6 shadow-lg transform hover:-translate-y-1 transition-transform duration-300">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-emerald-100 text-sm font-medium">Enseignants</p>
                                    <p class="text-3xl font-bold mt-2">{{ $primaryTeacherCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-chalkboard-teacher text-xl"></i>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/20">
                                <p class="text-sm text-emerald-100">Équipe pédagogique</p>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-violet-500 to-violet-600 text-white rounded-xl p-6 shadow-lg transform hover:-translate-y-1 transition-transform duration-300">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-violet-100 text-sm font-medium">Classes</p>
                                    <p class="text-3xl font-bold mt-2">{{ $primaryClassCount }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-school text-xl"></i>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/20">
                                <p class="text-sm text-violet-100">Répartition des classes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modules de gestion -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Gestion Primaire</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 hover:border-blue-200">
                                <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
                                    <i class="fas fa-user-tie text-2xl text-blue-600"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Gestion des enseignants</h3>
                                <p class="text-gray-600 text-sm mb-4">Attribuez les classes aux enseignants et suivez leurs affectations.</p>
                                <a href="{{ route('primaire.enseignants.enseignants') }}" class="inline-flex items-center text-blue-600 font-medium hover:text-blue-700">
                                    Gérer les enseignants
                                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                                </a>
                            </div>

                            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 hover:border-emerald-200">
                                <div class="w-14 h-14 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                                    <i class="fas fa-door-open text-2xl text-emerald-600"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Gestion des classes</h3>
                                <p class="text-gray-600 text-sm mb-4">Consultez les classes du primaire et leur répartition.</p>
                                <a href="{{ route('primaire.classe.classes') }}" class="inline-flex items-center text-emerald-600 font-medium hover:text-emerald-700">
                                    Voir les classes
                                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                                </a>
                            </div>

                            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 hover:border-violet-200">
                                <div class="w-14 h-14 rounded-xl bg-violet-50 flex items-center justify-center mb-4">
                                    <i class="fas fa-user-graduate text-2xl text-violet-600"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Suivi des élèves</h3>
                                <p class="text-gray-600 text-sm mb-4">Consultez les inscriptions, résultats et suivis académiques.</p>
                                <a href="{{ url('/primaire/ecoliers/liste') }}" class="inline-flex items-center text-violet-600 font-medium hover:text-violet-700">
                                    Gérer les élèves
                                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case(4)
                <!-- 🔹 Censeur -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-2xl text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Gestion académique</h2>
                            <p class="text-gray-600">Supervision des classes et suivi pédagogique</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-6">
                        <a href="{{ route('censeur.classes.index') }}" class="inline-flex items-center gap-3 bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-list"></i>
                            Accéder à la liste des classes
                        </a>
                    </div>
                </div>
                @break

            @case(6)
                <!-- 🔹 Admin Inscriptions -->
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-100 rounded-2xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Gestion des inscriptions</h2>
                            <p class="text-gray-600 mt-1">Validation et suivi des nouvelles inscriptions</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-user-check text-xl text-orange-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <a href="{{ route('admin.students.pending') }}" class="inline-flex items-center gap-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-tasks"></i>
                            Valider les inscriptions en attente
                        </a>
                    </div>
                </div>
                @break

            @case(7)
                <!-- 🔹 Admin Général -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 text-white rounded-2xl p-8 shadow-2xl">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center">
                            <i class="fas fa-cogs text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold">Administration Générale</h2>
                            <p class="text-gray-300 mt-2">Panneau de configuration complet du système</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-3 bg-white text-gray-900 px-8 py-4 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-rocket"></i>
                        Accéder au tableau de bord administrateur
                    </a>
                </div>
                @break

            @default
                <!-- 🔹 Enseignant -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-r from-teal-100 to-blue-100 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chalkboard-teacher text-3xl text-teal-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Vos classes</h2>
                        <p class="text-gray-600 mt-2">Accédez à vos classes et gérer votre espace enseignant</p>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('teacher.classes') }}" class="inline-flex items-center gap-3 bg-gradient-to-r from-teal-600 to-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-eye"></i>
                            Voir mes classes
                        </a>
                    </div>
                </div>
                @break
        @endswitch
    </div>
</div>

@else
<!-- Page de présentation pour non-connectés -->
<div class="min-h-[80vh] flex flex-col justify-center" data-aos="fade-up">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 p-8 md:p-12 mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    CS <span class="text-blue-600">MARIE-ALAIN</span>
                </h1>
                <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                    Excellence éducative de la maternelle à la terminale. Une plateforme complète pour la gestion pédagogique, des inscriptions au suivi académique.
                </p>
                
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user-tie text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Recrutement & Gestion RH</h4>
                            <p class="text-sm text-gray-600">Gestion simplifiée du personnel enseignant</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-user-graduate text-emerald-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Inscriptions & Suivi</h4>
                            <p class="text-sm text-gray-600">Suivi complet du parcours de chaque élève</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-purple-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Gestion des Classes</h4>
                            <p class="text-sm text-gray-600">Organisation optimale des entités pédagogiques</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <div class="relative mx-auto max-w-md">
                    <img src="{{ asset('logo.png') }}" alt="Logo École MARIE-ALAIN" 
                         class="w-full h-auto rounded-2xl shadow-2xl transform hover:scale-105 transition-transform duration-500">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-200 to-purple-200 rounded-3xl blur-xl opacity-30 -z-10"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section connexion -->
    <div class="max-w-md mx-auto text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-lock text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Accès sécurisé</h3>
            <p class="text-gray-600 mb-8">
                Connectez-vous pour accéder à votre espace personnel et aux outils de gestion.
            </p>
            <a href="{{ route('login') }}" 
               class="inline-flex items-center justify-center gap-3 w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter à l'espace personnel
            </a>
            <p class="text-sm text-gray-500 mt-4">
                Contactez l'administration pour obtenir vos identifiants
            </p>
        </div>
    </div>
</div>
@endauth

@endsection

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
</style>
@endpush