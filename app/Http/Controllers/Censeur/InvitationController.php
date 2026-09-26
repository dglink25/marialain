<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\TeacherInvitation;
use App\Mail\TeacherInvitationMail;
use App\Models\AcademicYear;


class InvitationController extends Controller{

    public function checkActiveYear(){
        $activeYear = AcademicYear::where('active', 1)->first();
        if (!$activeYear) {
            // Retourner une vue d’erreur si pas d’année active
            return view('errors.no_active_year');
        }
        return $activeYear;
    }
    
    public function index(){
        // Récupère l'année scolaire active
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return view('censeur.invitations.index', [
                'invitations' => collect(),
                'activeYear' => null,
                'error' => "Aucune année scolaire active n’a été trouvée."
            ]);
        }

        // Charge uniquement les invitations de cette année
        $invitations = TeacherInvitation::with('user')
            ->where('academic_year_id', $activeYear->id)
            ->where('censeur_id', 4)
            ->latest()
            ->get();

        return view('censeur.invitations.index', compact('invitations', 'activeYear'));
    }

    public function send(Request $request) {
        try {
            // Récupère l'année scolaire active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return back()->with('error', 'Aucune année scolaire active trouvée.');
            }

            // Validation des données
            $request->validate([
                'name'  => 'required|string',
                'email' => 'required|email|unique:users,email',
            ]);

            // Génération mot de passe aléatoire
            $plainPassword = "12345678";

            // Création de l'utilisateur
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($plainPassword),
                'role_id'  => 8, // Censeur / enseignant selon ton besoin
            ]);

            // Création de l'invitation
            $invitation = TeacherInvitation::create([
                'user_id'          => $user->id,
                'name'             => $request->name,   
                'email'            => $request->email, 
                'token'            => Str::random(32),
                'academic_year_id' => $activeYear->id, // ✅ insertion de l'année active
                'censeur_id'       => Auth::id(),
            ]);

            // Envoi de l'email
            Mail::to($user->email)->send(new TeacherInvitationMail($invitation, $plainPassword));

            return back()->with('success', 'Invitation envoyée à '.$user->email);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();

        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Erreur base de données : '.$e->getMessage());

        } catch (\Swift_TransportException $e) {
            return back()->with('error', 'Impossible d\'envoyer l\'email : '.$e->getMessage());

        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue : '.$e->getMessage());
        }
    }

    public function accept($token) {
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $invitation = TeacherInvitation::where('token', $token)->firstOrFail();
        $invitation->accepted = true;
        $invitation->save();

        return redirect('/login')->with('success', 'Votre compte est activé, veuillez vous connecter.');
    }


    public function exportPdf()
    {
        $activeYear = AcademicYear::where('active', true)->first();

        // Récupérer tous les enseignants (users) qui ont une invitation acceptée
        // pour l'année active, avec leurs matières distinctes via class_teacher_subject
        $invitations = TeacherInvitation::with('user')
            ->where('academic_year_id', $activeYear->id)
            ->where('censeur_id', 4)
            ->where('accepted', true)
            ->get()
            ->unique('user_id'); // un seul enregistrement par enseignant

        // Pour chaque enseignant, récupérer ses matières distinctes (toutes années confondues)
        $enseignants = $invitations->map(function ($inv) {
            $user = $inv->user;
            if (!$user) return null;

            // Matières via class_teacher_subject (toutes années)
            $matieres = \App\Models\Subject::whereHas('classTeacherSubjects', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })->distinct()->pluck('name')->toArray();

            return [
                'name'     => $user->name,
                'email'    => $user->email,
                'phone'    => $user->phone ?? '-',
                'matieres' => $matieres,
            ];
        })->filter()->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'censeur.invitations.pdf_enseignants',
            compact('enseignants', 'activeYear')
        )->setPaper('a4', 'portrait');

        return $pdf->download('liste-enseignants-' . ($activeYear->name ?? 'export') . '.pdf');
    }

    public function updateName(Request $request, TeacherInvitation $invitation) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Mettre à jour le nom sur l'invitation et sur le user
        $invitation->update(['name' => $request->name]);

        if ($invitation->user) {
            $invitation->user->update(['name' => $request->name]);
        }

        return back()->with('success', 'Nom mis à jour avec succès.');
    }

    public function destroy(TeacherInvitation $invitation){
        // Supprimer d'abord le user concerné
        if ($invitation->user) {
            $invitation->user->delete();
        }

        // Supprimer l’invitation elle-même
        $invitation->delete();

        return back()->with('success', 'Enseignant supprimé avec succès.');
    }


}
