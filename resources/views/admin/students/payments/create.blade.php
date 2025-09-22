@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Ajouter Paiement - {{ $student->last_name }} {{ $student->first_name }}</h1>

    <form method="POST" action="{{ route('students.payments.store', $student->id) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label>Tranche (1-3)</label>
            <input type="number" name="tranche" min="1" max="3" value="{{ old('tranche') }}" class="border p-2 rounded w-full">
            @error('tranche') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label>Montant payé</label>
            <input type="number" name="amount" step="0.01" value="{{ old('amount') }}" class="border p-2 rounded w-full">
            @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label>Date de paiement</label>
            <input type="date" name="payment_date" value="{{ old('payment_date') }}" class="border p-2 rounded w-full">
            @error('payment_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <div class="flex justify-between mt-8">
        <a href="{{ route('admin.students.index') }}" 
           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Retour
        </a>
    </div>


</div>
@endsection
