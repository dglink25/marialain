@component('mail::message')
# Bienvenue sur MARIE ALAIN

Bonjour **{{ $invitation->user->name }}**,

Vous avez été invité en tant qu'enseignant au CPEG MARIE ALAIN pour encadrer les élèves du 
Un compte a été créé pour vous sur la plateforme MARI ALAIN.

## Vos identifiants de connexion :
- Email : **{{ $invitation->user->email }}**
- Mot de passe : **{{ $plainPassword }}**

@component('mail::button', ['url' => url('/invitation/accept/'.$invitation->token)])
Confirmer mon compte
@endcomponent

Merci,  
L'équipe MARI ALAIN
@endcomponent
