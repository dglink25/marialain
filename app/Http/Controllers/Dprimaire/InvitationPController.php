<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Classe;
use App\Models\TeacherInvitation;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Entity;
use App\Mail\TeacherPInvitationMail;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvitationPController extends Controller
{
    // Vérifie l'année active
    public function checkActiveYear() {
        $activeYear = AcademicYear::where('active', 1)->first();
        if (!$activeYear) {
            return view('errors.no_active_year');
        }
        return $activeYear;
    }

    // Liste des invitations + classes dispo
    public function index() {
        $activeYear = $this->checkActiveYear();
        if (!$activeYear instanceof AcademicYear) return $activeYear;

        // Récupère entités primaire + maternelle
        $entities = Entity::whereIn('name', ['primaire', 'maternelle'])->pluck('id');

        $classes = Classe::where('academic_year_id', $activeYear->id)
            ->whereIn('entity_id', $entities)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $invitations = TeacherInvitation::with(['user', 'classe'])
            ->where('academic_year_id', $activeYear->id)
            ->whereHas('user', function ($q) {
                $q->where('classe_id', '!=', 0);
            })
            ->latest()
            ->get();


        return view('primaire.enseignants.index', compact('classes', 'invitations', 'activeYear'));
    }

    // Envoi invitation

    public function send(Request $request){
        try {
            // 1) année active
            $activeYear = AcademicYear::where('active', true)->first();
            if (! $activeYear) {
                return back()->with('error', 'Aucune année scolaire active. Veuillez activer une année avant d\'envoyer des invitations.');
            }

            // 2) validation
            $data = $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'classe' => 'nullable|exists:classes,id', // si tu veux attacher à une classe
            ]);

            DB::beginTransaction();

            // 3) créer user
            $plainPassword = Str::random(8);
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($plainPassword),
                'role_id'  => 6, // adapte si différent
            ]);

            // 4) créer invitation (assure-toi que la colonne academic_year_id existe)
            $invitation = TeacherInvitation::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'token' => Str::random(32),
                'academic_year_id' => $activeYear->id,
                'censeur_id' => auth()->id(),
                'classe_id' => $data['classe'] ?? null,
            ]);

            if (!empty($data['classe'])) {
                $classe = Classe::find($data['classe']);
                if ($classe) {
                    $classe->teacher_id = $user->id;
                    $classe->save();
                }
            }

            // 5) envoyer mail (non-queued pour debug ; ensuite tu pourras queue)
            Mail::to($user->email)->send(new TeacherPInvitationMail($invitation, $plainPassword));

            DB::commit();

            return back()->with('success', "Invitation envoyée à {$user->email}.");

        } catch (\Swift_TransportException $e) {
            DB::rollBack();
            Log::error('Mail transport error sending teacher invitation', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', "Erreur d'envoi du mail : vérifiez la configuration mail. ({$e->getMessage()})");
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laravel gère déjà ->withErrors, on veut juste redirect with old
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            // log complet pour debug
            Log::error('Error creating teacher invitation', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // En dev tu peux afficher $e->getMessage(), en prod garde message générique
            if (config('app.debug')) {
                return back()->with('error', 'Erreur : '.$e->getMessage());
            }
            return back()->with('error', 'Une erreur est survenue lors de l’envoi de l’invitation. Veuillez réessayer.');
        }
    }


    // Acceptation invitation
    public function accept($token) {
        $invitation = TeacherInvitation::where('token', $token)->firstOrFail();
        $invitation->accepted = true;
        $invitation->save();

        return redirect()->route('login')->with('success', 'Votre compte est activé, connectez-vous.');
    }
}
