@extends('layouts.app')

@section('title', 'Année académique inactive')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <!-- En-tête -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-50 rounded-full mb-4">
                <i class="fas fa-calendar-times text-amber-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Année académique</h1>
            <p class="text-gray-600 text-sm">Aucune année active pour le moment</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 text-center text-sm">
                Aucune année académique n'est actuellement active sur la plateforme.
                <br>Cette fonctionnalité nécessite une année académique en cours.
            </p>
        </div>

        <!-- Informations -->
        <div class="bg-gray-50 rounded p-4 mb-6 border border-gray-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="font-semibold text-gray-800 text-sm mb-1">Information</h3>
                    <p class="text-gray-600 text-xs leading-relaxed">
                        Pour accéder à cette fonctionnalité, une année académique doit être configurée et activée par l'administrateur.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2 mb-6">
            <a href="mailto:cpegmariealain@gmail.com?subject=Demande%20d%27activation%20ann%C3%A9e%20acad%C3%A9mique" 
               class="w-full block text-center px-4 py-2.5 bg-amber-500 text-white text-sm font-medium rounded hover:bg-amber-600 transition-colors">
                <i class="fas fa-envelope mr-2"></i>
                Contacter l'administrateur
            </a>
            
            <a href="https://wa.me/22994119476?text=Bonjour%2C%20je%20souhaite%20signaler%20qu%27aucune%20ann%C3%A9e%20acad%C3%A9mique%20n%27est%20active%20sur%20la%20plateforme." 
               target="_blank"
               class="w-full block text-center px-4 py-2.5 bg-green-500 text-white text-sm font-medium rounded hover:bg-green-600 transition-colors">
                <i class="fab fa-whatsapp mr-2"></i>
                Contacter via WhatsApp
            </a>
            
            <a href="{{ url()->previous() }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Page précédente
            </a>
        </div>

        <!-- Support -->
        <div class="pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center mb-3">Support technique</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="mailto:cpegmariealain@gmail.com" 
                   class="inline-flex items-center justify-center gap-2 text-xs text-gray-600 hover:text-gray-800">
                    <i class="fas fa-envelope"></i>
                    <span>cpegmariealain@gmail.com</span>
                </a>
                <span class="hidden sm:block text-gray-300">|</span>
                <a href="https://wa.me/22994119476" target="_blank" 
                   class="inline-flex items-center justify-center gap-2 text-xs text-green-600 hover:text-green-700">
                    <i class="fab fa-whatsapp"></i>
                    <span>+229 94 11 94 76</span>
                </a>
            </div>
        </div>

        <!-- Horodatage -->
        <div class="mt-4 text-center">
            <span class="text-xs text-gray-400">
                <i class="far fa-clock mr-1"></i>
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
</div>
@endsection