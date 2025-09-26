@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Classes du secondaire</h1>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($classes as $class)
        <div class="bg-white rounded-lg shadow p-5 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <h2 class="text-xl font-semibold mb-2">{{ $class->name }}</h2>
                <p class="text-gray-600">Élèves : {{ $class->students_count ?? '0' }}</p>
            </div>
            <div class="flex gap-2 mt-4">
                <!-- Bouton attribuer conduite -->
                <button onclick="openConductModal({{ $class->id }})" 
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white px-1 py-1 rounded transition">
                    Attribuer Conduite
                </button>

                <!-- Lien voir élèves -->
                <a href="{{ route('surveillant.classes.students', $class->id) }}"
                   class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-center transition">
                   Voir élèves
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal Attribuer conduite -->
<div id="conductModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-center">Attribuer conduite</h2>
        <form method="POST" id="conductForm" class="flex flex-col gap-3">
            @csrf
            <input type="text" name="grade" placeholder="Note (A, B, C...)" class="border p-3 rounded w-full" required>
            <input type="text" name="comment" placeholder="Commentaire" class="border p-3 rounded w-full">
            <div class="flex justify-between mt-4">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">Valider</button>
                <button type="button" onclick="closeConductModal()" 
                        class="flex-1 ml-3 px-4 py-2 border rounded hover:bg-gray-100 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openConductModal(classId) {
    const form = document.getElementById('conductForm');
    form.action = "/surveillant/classes/" + classId + "/conducts";
    document.getElementById('conductModal').classList.remove('hidden');
}
function closeConductModal() {
    document.getElementById('conductModal').classList.add('hidden');
}
</script>
@endsection
