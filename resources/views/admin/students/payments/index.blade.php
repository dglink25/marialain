@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4">

@if ($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('error'))
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ session('error') }}
    </div>
  @endif

  @if (session('success'))
    <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-6">
      {{ session('success') }}
    </div>
  @endif

    <!-- En-tête de page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Historique des Paiements</h1>
            <p class="text-lg text-gray-600 mt-2">
                Élève : <span class="font-semibold">{{ $student->last_name }} {{ $student->first_name }}</span>
            </p>
            <div class="flex items-center mt-2 space-x-2">
                <span class="text-sm text-gray-600">Type d'inscription:</span>
                @if($student->registration_type)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        {{ $student->registration_type == 'new' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800' }}">
                        {{ $student->registration_type == 'new' ? 'Nouvelle inscription' : 'Réinscription' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        Non défini
                    </span>
                @endif
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Bouton pour modifier le type d'inscription -->
            <button onclick="openRegistrationTypeModal()" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg shadow-md transition duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier type d'inscription
            </button>
            <a href="{{ route('students.payments.create', $student->id) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-md transition duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau Paiement
            </a>
        </div>
    </div>

    <!-- Cartes de résumé -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total à payer</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalFees ?? $student->total_fees, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Payé</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalPaid ?? $student->total_paid, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 mr-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Montant Restant</p>
                    <p class="text-2xl font-bold {{ $remainingFees > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($remainingFees ?? ($student->remaining_fees), 0, ',', ' ') }} FCFA
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des paiements -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Détail des Transactions</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tranche</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reçu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $p)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Tranche {{ $p->tranche }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($p->amount, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($p->receipt)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Reçu généré
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Non disponible</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('payments.receipt', $p->id) }}" 
                               class="text-green-600 hover:text-green-900 flex items-center transition duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Télécharger
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-lg">Aucun paiement enregistré pour cet élève</p>
                            <a href="{{ route('students.payments.create', $student->id) }}" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                Enregistrer un premier paiement
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pied de tableau avec totaux -->
        @if($payments->count() > 0)
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    {{ $payments->count() }} paiement(s) trouvé(s)
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-600">Solde total: 
                        <span class="text-lg font-bold {{ $remainingFees > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($remainingFees, 0, ',', ' ') }} FCFA
                        </span>
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Bouton de retour -->
    <div class="flex justify-start mt-8">
        <a href="{{ route('admin.students.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg shadow-md transition duration-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste
        </a>
    </div>
</div>

<!-- Modal pour modifier le type d'inscription -->
<div id="registrationTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Modifier le type d'inscription</h3>
            <form action="{{ route('students.update-registration-type', $student->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'inscription</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" name="registration_type" value="new" id="type_new" 
                                   {{ $student->registration_type == 'new' ? 'checked' : '' }} class="mr-2">
                            <label for="type_new" class="text-sm text-gray-700">Nouvelle inscription</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="registration_type" value="re_registration" id="type_re" 
                                   {{ $student->registration_type == 're_registration' ? 'checked' : '' }} class="mr-2">
                            <label for="type_re" class="text-sm text-gray-700">Réinscription</label>
                        </div>
                    </div>
                </div>

                @if($student->classe)
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Frais de scolarité: <span class="font-semibold">{{ number_format($student->classe->school_fees ?? 0, 0, ',', ' ') }} FCFA</span></p>
                    <p class="text-sm text-gray-600 mb-1">Frais inscription: <span class="font-semibold">{{ number_format($student->classe->registration_fee ?? 0, 0, ',', ' ') }} FCFA</span></p>
                    <p class="text-sm text-gray-600">Frais réinscription: <span class="font-semibold">{{ number_format($student->classe->re_registration_fee ?? 0, 0, ',', ' ') }} FCFA</span></p>
                </div>
                @endif

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRegistrationTypeModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRegistrationTypeModal() {
        document.getElementById('registrationTypeModal').classList.remove('hidden');
    }
    
    function closeRegistrationTypeModal() {
        document.getElementById('registrationTypeModal').classList.add('hidden');
    }
    
    // Fermer le modal si on clique en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('registrationTypeModal');
        if (event.target == modal) {
            modal.classList.add('hidden');
        }
    }
</script>

<style>
    .shadow-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .hover\:shadow-lg:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Animation pour le modal */
    #registrationTypeModal {
        transition: opacity 0.3s ease;
    }
    
    #registrationTypeModal .bg-white {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>
@endsection