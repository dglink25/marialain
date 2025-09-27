@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <!-- En-tête avec fond solide -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Nouveau Paiement</h1>
                    <p class="text-green-100">{{ $student->last_name }} {{ $student->first_name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de l'étudiant -->
    <div class="px-8 py-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Classe:</span>
                <p class="font-semibold">{{ $student->classe->name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-gray-600">Frais annuels:</span>
                <p class="font-semibold">{{ number_format($student->classe->school_fees ?? 0, 2) }} FCFA</p>
            </div>
            <div>
                <span class="text-gray-600">Déjà payé:</span>
                <p class="font-semibold">{{ number_format($student->total_paid, 2) }} FCFA</p>
            </div>
            <div>
                <span class="text-gray-600">Reste à payer:</span>
                <p class="font-semibold text-red-600">{{ number_format($student->remaining_fees, 2) }} FCFA</p>
            </div>
        </div>
    </div>

    <!-- Formulaire -->
    <form method="POST" action="{{ route('students.payments.store', $student->id) }}" enctype="multipart/form-data" class="p-8">
        @csrf

        <div class="space-y-6">
            <!-- Tranche -->
            <div>
                <label for="tranche" class="block text-sm font-medium text-gray-700 mb-2">Tranche de paiement *</label>
                <select name="tranche" id="tranche" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">-- Sélectionnez la tranche --</option>
                    <option value="1" {{ old('tranche') == '1' ? 'selected' : '' }}>Tranche 1</option>
                    <option value="2" {{ old('tranche') == '2' ? 'selected' : '' }}>Tranche 2</option>
                    <option value="3" {{ old('tranche') == '3' ? 'selected' : '' }}>Tranche 3</option>
                </select>
                @error('tranche') 
                    <p class="text-red-600 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Montant -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Montant payé (FCFA) *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500"></span>
                    </div>
                    <input type="number" name="amount" id="amount" step="0.01" 
                           value="{{ old('amount') }}" 
                           class="w-full px-4 py-3 pl-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                           placeholder="0.00"
                           min="0"
                           max="{{ $student->remaining_fees }}"
                           required>
                </div>
                @error('amount') 
                    <p class="text-red-600 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Montant maximum autorisé: {{ number_format($student->remaining_fees, 2) }} FCFA</p>
            </div>

            <!-- Date de paiement -->
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Date de paiement *</label>
                <div class="relative">
                    <input type="date" name="payment_date" id="payment_date" 
                           value="{{ old('payment_date', date('Y-m-d')) }}" 
                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                           max="{{ date('Y-m-d') }}"
                           required>
                </div>
                @error('payment_date') 
                    <p class="text-red-600 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Justificatif (optionnel) 
            <div>
                <label for="receipt" class="block text-sm font-medium text-gray-700 mb-2">Justificatif de paiement (optionnel)</label>
                <input type="file" name="receipt" id="receipt" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                       accept=".pdf,.jpg,.jpeg,.png">
                <p class="text-sm text-gray-500 mt-1">Formats acceptés: PDF, JPG, PNG (max 5MB)</p>
            </div>
        </div>
        -->
        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-8 mt-8 border-t border-gray-200">
            <a href="{{ route('admin.students.index') }}" 
               class="w-full sm:w-auto px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Annuler
            </a>

            <div class="flex space-x-3">
                <a href="{{ route('students.payments.index', $student->id) }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Historique
                </a>
                
                <button type="submit" 
                        class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center justify-center font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer le paiement
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Validation en temps réel
    const amountInput = document.getElementById('amount');
    const remainingFees = {{ $student->remaining_fees }};
    
    amountInput.addEventListener('input', function() {
        if (this.value > remainingFees) {
            this.setCustomValidity(`Le montant ne peut pas dépasser ${remainingFees.toFixed(2)} FCFA`);
            this.classList.add('border-red-500');
        } else {
            this.setCustomValidity('');
            this.classList.remove('border-red-500');
        }
    });

    // Formatage automatique du montant
    amountInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });

    // Animation de focus sur les champs
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('focus', function() {
            this.classList.add('ring-2', 'ring-green-200');
        });
        
        element.addEventListener('blur', function() {
            this.classList.remove('ring-2', 'ring-green-200');
        });
    });

    // Prévisualisation du fichier
    const receiptInput = document.getElementById('receipt');
    receiptInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // MB
            if (fileSize > 5) {
                alert('Le fichier ne doit pas dépasser 5MB');
                this.value = '';
            }
        }
    });
</script>

<style>
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
    
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endsection