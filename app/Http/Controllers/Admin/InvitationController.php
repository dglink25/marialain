<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\AcademicYear;
use App\Mail\InvitationMail;

class InvitationController extends Controller{
    public function index(Request $request){
        $query = Invitation::with('academicYear', 'inviter');

        // Filtrage par entité si demandé
        if ($request->filled('entity')) {
            $query->where('entity', $request->entity);
        }

        // Pagination, 10 par page
        $invitations = $query->orderBy('created_at', 'desc')->paginate(10);

        $years = AcademicYear::where('active', 1)->get();


        return view('admin.invitations.index', compact('invitations', 'years'));
    }

    public function store(Request $request){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $request->validate([
            'email' => 'required|email',
            'academic_year_id' => 'required|exists:academic_years,id',
            'entity' => 'required|in:maternelle,primaire,secondaire',
        ]);

        $token = Str::random(32);
        $invitation = Invitation::create([
            'email'              => $request->email,
            'academic_year_id'   => $activeYear->id,
            'entity'             => $request->entity,
            'token'              => $token,
            'invited_by'         => auth()->user()->id, 
        ]);


        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return redirect()->route('admin.invitations.index')->with('success','Invitation envoyée.');
    }

}
