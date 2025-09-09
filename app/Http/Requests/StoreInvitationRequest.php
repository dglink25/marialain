<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class StoreInvitationRequest extends FormRequest{
    public function authorize(): bool { 
        return auth()->check() && auth()->user()->hasAnyRole(['founder','admin']); 
    }


    public function rules(): array{
        return [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|string|required_without:email',
            'role' => 'required|string|in:founder,admin,censeur,secretaire,directeur_primaire,surveillant,teacher,parent,student'
        ];
    }
}