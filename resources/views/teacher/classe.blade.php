@foreach($classes as $class)
    <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-lg transition">
        <h2 class="text-lg font-semibold text-gray-800">{{ $class->name }}</h2>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('teacher.classes.students', $class->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                Elèves
            </a>
            <a href="{{ route('teacher.classes.timetable', $class->id) }}"
               class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                Emploi du temps
            </a>
            <a href="{{ route('teacher.classes.notes.trimestres', $class->id) }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Trimestres
            </a>

            {{-- Bouton Cahier de texte --}}
            @php
                $currentLesson = app(App\Http\Controllers\TeacherController::class)->checkCurrentLesson($class->id);
            @endphp

            @if($currentLesson)
                <!-- Bouton modal -->
                <button 
                    onclick="document.getElementById('modal-{{ $class->id }}').classList.remove('hidden')"
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">
                    Cahier de texte
                </button>

                <!-- Modal formulaire -->
                <div id="modal-{{ $class->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
                        <button onclick="document.getElementById('modal-{{ $class->id }}').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">✖</button>
                        <h3 class="text-lg font-bold mb-4">Cahier de texte - {{ $currentLesson->subject->name }}</h3>

                        <form action="{{ route('teacher.cahier.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <input type="hidden" name="subject_id" value="{{ $currentLesson->subject_id }}">
                            <input type="hidden" name="teacher_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="timetable_id" value="{{ $currentLesson->id }}">
                            <input type="hidden" name="day" value="{{ $currentLesson->day }}">

                            <textarea name="content" rows="4" class="w-full border rounded p-2 mb-2" placeholder="Écrivez le contenu du cours ici..."></textarea>
                            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                Enregistrer
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endforeach
