@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Historique de paiements de {{ $student->last_name }} {{ $student->first_name }}</h1>

    <a href="{{ route('students.payments.create', $student->id) }}" 
       class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter Paiement</a>

    <table class="min-w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2">Tranche</th>
                <th class="border px-4 py-2">Date</th>
                <th class="border px-4 py-2">Montant</th>
                <th class="border px-4 py-2">Reçu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
            <tr>
                <td class="border px-4 py-2">{{ $p->tranche }}</td>
                <td class="border px-4 py-2">{{ $p->payment_date->format('d/m/Y') }}</td>
                <td class="border px-4 py-2">{{ number_format($p->amount,2) }} FCFA</td>
                <td class="border px-4 py-2 text-center">
                    <a href="{{ asset('storage/receipts/recu_'.$p->id.'.pdf') }}" 
                    target="_blank" 
                    class="text-blue-600 hover:underline">
                    Voir reçu
                    </a>
<br><br>
                    <a href="{{ asset('storage/receipts/recu_'.$p->id.'.pdf') }}" 
                    target="_blank" 
                    class="text-blue-600 hover:underline" download>
                    Télécharger
                    </a>
                </td>
            </tr>
            @endforeach

            <tr class="font-bold bg-gray-50">
                <td colspan="3">Total payé</td>
                <td>{{ number_format($student->total_paid,2) }} FCFA</td>
            </tr>
            <tr class="font-bold bg-gray-50">
                <td colspan="3">Montant restant</td>
                <td>{{ number_format($student->remaining_fees,2) }} FCFA</td>
            </tr>
        </tbody>
    </table>


    <div class="flex justify-between mt-8">
        <a href="{{ route('admin.students.index') }}" 
           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Retour
        </a>
    </div>

</div>
@endsection
