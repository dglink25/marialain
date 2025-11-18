@extends('layouts.app')

@section('title', "Cahier de texte - $teacher->name - $subject->name")

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<div class="container mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold text-indigo-700 mb-6 text-center sm:text-left">Historique du Cahier de texte de {{ $teacher->name }} </h1>

    {{-- Header --}}
    <div class="bg-white/90 backdrop-blur-lg shadow-lg rounded-xl p-6 border border-gray-200 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="text-center lg:text-left">
                <h2 class="text-xl font-bold text-gray-800">Classe : <span class="text-indigo-600">{{ $class->name }}</span></h2>
                <p class="text-sm text-gray-600 mt-2">
                    Matière courante :
                    <span class="text-indigo-600 font-semibold">
                        {{ $subject->name }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- Entries --}}
    @if ($entries->isEmpty())
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 text-yellow-800 p-8 rounded-2xl shadow text-center mt-8">
            <svg class="w-16 h-16 mx-auto mb-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Aucun enregistrement trouvé</h3>
        </div>
    @else
        {{-- Desktop Table --}}
        <div class="hidden lg:block bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700">
                    <thead>
                        <tr class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-left">
                            <th class="px-6 py-4 font-semibold">Jour</th>
                            <th class="px-6 py-4 font-semibold">Heure</th>
                            <th class="px-6 py-4 font-semibold">Durée</th>
                            <th class="px-6 py-4 font-semibold">Contenu</th>
                            <th class="px-6 py-4 font-semibold">Retard</th>
                            <th class="px-6 py-4 font-semibold">Enrégistré</th>
                            <th class="px-6 py-4 font-semibold">Dernière modification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($entries as $entry)
                        <tr class="hover:bg-indigo-50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ ucfirst($entry->day) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600">
                                    {{ substr($entry->timetable->start_time ?? '00:00', 0, 5) }}
                                    —
                                    {{ substr($entry->timetable->end_time ?? '00:00', 0, 5) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                    {{ intval($entry->duration_minutes / 60) }}h{{ $entry->duration_minutes % 60 }}min
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-md">
                                    <p class="text-gray-800 line-clamp-2">{{ Str::limit($entry->content, 100) }}</p>
                                    @if(strlen($entry->content) > 100)
                                        <button onclick="openFullContentModal(@json($entry))" 
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mt-1 transition-colors">
                                            Voir plus
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($entry->is_late)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Oui
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1 max-w-xs">Motif : {{ Str::limit($entry->motif_retard, 50) }}</div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Non
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $canEdit = now()->diffInMinutes($entry->created_at) <= 10; @endphp
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $entry->created_at}}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php $canEdit = now()->diffInMinutes($entry->created_at) <= 10; @endphp
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $entry->updated_at}}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden space-y-4">
            @foreach ($entries as $entry)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-xl transition-shadow duration-300">
                {{-- Header --}}
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ ucfirst($entry->day) }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ substr($entry->timetable->start_time ?? '00:00', 0, 5) }} - 
                            {{ substr($entry->timetable->end_time ?? '00:00', 0, 5) }}
                        </p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                        {{ intval($entry->duration_minutes / 60) }}h{{ $entry->duration_minutes % 60 }}min
                    </span>
                </div>

                {{-- Content --}}
                <div class="mb-3">
                    <p class="text-gray-800 text-sm line-clamp-3">{{ $entry->content }}</p>
                    @if(strlen($entry->content) > 150)
                        <button onclick="openFullContentModal(@json($entry))" 
                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mt-1 transition-colors">
                            Voir plus
                        </button>
                    @endif
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between mb-3">
                    @if($entry->is_late)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            En retard
                        </span>
                        <div class="text-xs text-red-600 text-right">
                            {{ Str::limit($entry->motif_retard, 40) }}
                        </div>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            À l'heure
                        </span>
                    @endif
                    
                    @php $canEdit = now()->diffInMinutes($entry->created_at) <= 10; @endphp
                    @if($canEdit)
                        <button onclick='openModalForEdit(@json($entry))' 
                            class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors text-xs font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </button>
                    @else
                        <span class="text-gray-400 text-xs">Modification expirée</span>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                    Dernière modification : {{ $entry->updated_at->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Full content modal --}}
<div id="full-content-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden animate-fadeInUp">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Contenu complet</h3>
            <button onclick="closeFullContentModal()" class="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-1 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div id="full-content-body" class="text-gray-800 whitespace-pre-wrap text-sm leading-relaxed"></div>
        </div>
        <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
            <button onclick="closeFullContentModal()" 
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors font-medium">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
// Toggle late reason box
function toggleLateReason() {
    const checkbox = document.getElementById('is_late_checkbox');
    const lateReasonBox = document.getElementById('late_reason_box');
    const motifRetard = document.getElementById('motif_retard');
    
    if (checkbox.checked) {
        lateReasonBox.classList.remove('hidden');
        motifRetard.required = true;
        // Add animation
        setTimeout(() => {
            lateReasonBox.style.transform = 'scaleY(1)';
        }, 10);
    } else {
        lateReasonBox.classList.add('hidden');
        motifRetard.required = false;
        motifRetard.value = ''; // Clear the field when unchecked
    }
}

// Form validation
function validateForm() {
    const isLate = document.getElementById('is_late_checkbox').checked;
    const motifRetard = document.getElementById('motif_retard').value;
    
    if (isLate && !motifRetard.trim()) {
        alert('Veuillez saisir le motif du retard.');
        return false;
    }
    return true;
}

// Modal functions
function openModalForCreate() {
    document.getElementById('modal-title').innerText = 'Ajouter un Cahier de Texte';
    document.getElementById('modal-form').action = "{{ route('teacher.cahier.store') }}";
    document.getElementById('entry_id').value = '';
    document.getElementById('content').value = '';
    document.getElementById('motif_retard').value = '';
    document.getElementById('is_late_checkbox').checked = false;
    document.getElementById('late_reason_box').classList.add('hidden');
    document.getElementById('subject_id').value = '{{ $class->currentLesson->subject_id ?? 0 }}';
    document.getElementById('timetable_id').value = '{{ $class->currentLesson->id ?? 0 }}';
    document.getElementById('day').value = '{{ $class->currentLesson->day ?? now()->format('l') }}';
    document.getElementById('modal-meta').innerText = '';
    
    // Reset form validation
    document.getElementById('modal-form').onsubmit = function() {
        return validateForm();
    };
    
    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openModalForEdit(entry) {
    document.getElementById('modal-title').innerText = 'Modifier le Cahier de Texte';
    document.getElementById('modal-form').action = "{{ url('/teacher/cahier/update') }}/" + entry.id;
    document.getElementById('entry_id').value = entry.id;
    document.getElementById('content').value = entry.content ?? '';
    document.getElementById('subject_id').value = entry.subject_id ?? '';
    document.getElementById('timetable_id').value = entry.timetable_id ?? '';
    document.getElementById('day').value = entry.day ?? '';
    
    // Set late status
    document.getElementById('is_late_checkbox').checked = entry.is_late ?? false;
    document.getElementById('motif_retard').value = entry.motif_retard ?? '';
    
    // Show/hide late reason box based on current state
    if (entry.is_late) {
        document.getElementById('late_reason_box').classList.remove('hidden');
        document.getElementById('motif_retard').required = true;
    } else {
        document.getElementById('late_reason_box').classList.add('hidden');
        document.getElementById('motif_retard').required = false;
    }
    
    document.getElementById('modal-meta').innerText = "Créé : " + new Date(entry.created_at).toLocaleString() + " • Dernière modif : " + new Date(entry.updated_at).toLocaleString();
    
    // Set form validation
    document.getElementById('modal-form').onsubmit = function() {
        return validateForm();
    };
    
    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openFullContentModal(entry) {
    document.getElementById('full-content-body').innerText = entry.content || 'Aucun contenu disponible';
    document.getElementById('full-content-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullContentModal() {
    document.getElementById('full-content-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function closeModal() {
    document.getElementById('cahier-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals on outside click
document.addEventListener('click', function(event) {
    const cahierModal = document.getElementById('cahier-modal');
    const fullContentModal = document.getElementById('full-content-modal');
    
    if (event.target === cahierModal) {
        closeModal();
    }
    if (event.target === fullContentModal) {
        closeFullContentModal();
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
        closeFullContentModal();
    }
});

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('modal-form');
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
});
</script>

<style>
.animate-fadeInUp {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions for late reason box */
#late_reason_box {
    transform: scaleY(0);
    transform-origin: top;
    transition: transform 0.3s ease;
}

#late_reason_box:not(.hidden) {
    transform: scaleY(1);
}

/* Smooth scroll for modal content */
.modal-content {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.modal-content::-webkit-scrollbar {
    width: 6px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
@endsection