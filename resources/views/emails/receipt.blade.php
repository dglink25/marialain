@component('mail::message')
# Bonjour {{ $payment->student->parent_full_name ?? 'Parent/Tuteur' }},

Un paiement scolaire a été enregistré pour votre enfant **{{ $payment->student->last_name }} {{ $payment->student->first_name }}**.

- **Tranche :** {{ $payment->tranche }}
- **Montant payé :** {{ number_format($payment->amount,2) }} FCFA
- **Date :** {{ $payment->payment_date->format('d/m/Y') }}

Vous trouverez en pièce jointe le reçu PDF.

Merci pour votre confiance.  
Cordialement,  
Le Secrétaire de CPEG MARIE-ALAIN
@endcomponent
