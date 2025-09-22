
@component('mail::message')

Bonjour {{ $student->parent_full_name }},

Votre enfant **{{ $student->first_name }} {{ $student->last_name }}** a été inscrit avec succès en classe de **{{ $student->classe->name }}** au CPEG MARIE-ALAIN.

Veuillez vous rapprocher auprès de notre Sécretariat pour valider votre inscription! 

Merci,<br>
CPEG MARIE-ALAIN
@endcomponent
