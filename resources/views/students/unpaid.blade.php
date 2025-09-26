@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 p-4 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-300">
            {{ session('warning') }}
            @if (session('missingEmails'))
                <ul class="list-disc ml-6 mt-2 text-sm text-gray-700">
                    @foreach (session('missingEmails') as $student)
                        <li>{{ $student->last_name }} {{ $student->first_name }} (Classe : {{ $student->classe->name ?? '---' }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif


    <h1 class="text-xl md:text-2xl font-bold mb-6 text-gray-800">
        Élèves n'ayant pas soldé la scolarité ({{ $activeYear->name }})
    </h1>

    {{-- Message de progression --}}
    <div id="loadingMessage" class="hidden mb-4 p-3 bg-yellow-100 text-yellow-800 rounded border border-yellow-300">
        <strong>Veuillez patienter...</strong> L’envoi des mails est en cours.
    </div>

    {{-- Boutons --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <form id="mailForm" method="POST" action="{{ route('students.mail.sendAll') }}">
            @csrf
            <button type="submit" 
                class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                Envoyer mails de rappel
            </button>
        </form>
    </div>

    {{-- Table responsive --}}
    <div class="overflow-x-auto shadow rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">N°</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Nom & Prénoms</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Classe</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Payé</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Frais</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Reste</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Parent</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Email</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Téléphone</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($unpaidStudents as $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 font-medium">{{ $student->last_name }} {{ $student->first_name }}</td>
                        <td class="px-4 py-2">{{ $student->classe->name ?? '---' }}</td>
                        <td class="px-4 py-2 text-green-700 font-semibold">
                            {{ number_format($student->school_fees_paid, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-4 py-2">{{ number_format($student->classe->school_fees, 0, ',', ' ') }} FCFA</td>
                        <td class="px-4 py-2 text-red-600 font-bold">
                            {{ number_format($student->classe->school_fees - $student->school_fees_paid, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-4 py-2">{{ $student->parent_full_name }}</td>
                        <td class="px-4 py-2">{{ $student->parent_email }}</td>
                        <td class="px-4 py-2">{{ $student->parent_phone }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-6 text-gray-500">
                            Tous les élèves ont soldé leur scolarité
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <br>
    <br>
     <button onclick="window.history.back()" 
            class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
            Retour
        </button>
</div>

{{-- JS pour afficher "Veuillez patienter" --}}
<script>
    document.getElementById('mailForm').addEventListener('submit', function () {
        document.getElementById('loadingMessage').classList.remove('hidden');
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const alerts = document.querySelectorAll(".mb-4");
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }, 5000); // disparaît après 5s
        });
    });
</script>

@endsection
