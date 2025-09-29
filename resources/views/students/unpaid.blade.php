@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Élèves n'ayant pas soldé la scolarité</h1>
                <p class="mt-1 text-sm text-gray-600">Année académique : {{ $activeYear->name }}</p>
            </div>
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Total: <span class="font-medium">{{ $unpaidStudents->count() }} élève(s)</span></p>
            </div>
        </div>
    </div>

    <!-- Messages d'alerte -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                    @if (session('missingEmails'))
                        <div class="mt-2">
                            <p class="text-sm text-yellow-700 font-medium">Élèves sans email :</p>
                            <ul class="list-disc ml-5 mt-1 text-sm text-yellow-600">
                                @foreach (session('missingEmails') as $student)
                                    <li>{{ $student->last_name }} {{ $student->first_name }} ({{ $student->classe->name ?? '---' }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Message de progression -->
    <div id="loadingMessage" class="hidden mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-blue-800">Veuillez patienter...</p>
                <p class="text-sm text-blue-600">L'envoi des emails de rappel est en cours.</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 mb-6">
        <form id="mailForm" method="POST" action="{{ route('students.mail.sendAll') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" 
                    class="w-full sm:w-auto inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Envoyer les rappels par email
            </button>
        </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-200">
        @if($unpaidStudents->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élève</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reste</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($unpaidStudents as $student)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $student->last_name }} {{ $student->first_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $student->classe->name ?? '---' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                                {{ number_format($student->school_fees_paid, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ number_format($student->classe->school_fees, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ number_format($student->classe->school_fees - $student->school_fees_paid, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $student->parent_full_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex flex-col space-y-1">
                                    @if($student->parent_email)
                                        <a href="mailto:{{ $student->parent_email }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                            {{ $student->parent_email }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">---</span>
                                    @endif
                                    @if($student->parent_phone)
                                        <a href="tel:{{ $student->parent_phone }}" class="text-gray-600 hover:text-gray-900">
                                            {{ $student->parent_phone }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">---</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Excellent !</h4>
                <p class="text-gray-600">Tous les élèves ont soldé leur scolarité pour cette année.</p>
            </div>
        @endif
    </div>
    <br>
    <br>
     <button onclick="window.history.back()" 
                class="w-full sm:w-auto inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </button>
</div>

<script>
    document.getElementById('mailForm').addEventListener('submit', function () {
        document.getElementById('loadingMessage').classList.remove('hidden');
    });

    // Auto-dismiss alerts
    document.addEventListener("DOMContentLoaded", () => {
        const alerts = document.querySelectorAll(".bg-green-50, .bg-yellow-50");
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateX(100%)";
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
</script>

<style>
    .min-w-full {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .min-w-full th {
        background-color: #f9fafb;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .min-w-full td {
        border-bottom: 1px solid #f3f4f6;
    }
    
    .min-w-full tr:last-child td {
        border-bottom: none;
    }
    
    @media (max-width: 768px) {
        .max-w-7xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .overflow-x-auto {
            margin-left: -1rem;
            margin-right: -1rem;
        }
    }
</style>
@endsection