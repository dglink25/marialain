{{-- resources/views/emails/students/validated.blade.php --}}
@component('mail::message')
# Validation de l'inscription

Bonjour {{ $student->parent_full_name }},

L'inscription de **{{ $student->first_name }} {{ $student->last_name }}** a été **validée** par le secrétariat.  

Montant payé à l'inscription : **{{ number_format($student->amount_paid, 0, ',', ' ') }} FCFA**

Merci de votre confiance,  
CPEG MARIE-ALAIN
@endcomponent
