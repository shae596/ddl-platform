<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->input('action');
        $complet = in_array($action, ['generer_cdc', 'soumettre'], true);

        return [
            'action' => ['required', Rule::in(['brouillon', 'generer_cdc', 'soumettre'])],
            'titre' => ['required', 'string', 'max:200'],
            'service_demandeur' => [$complet ? 'required' : 'nullable', 'string', 'max:150'],
            'nom_demandeur' => [$complet ? 'required' : 'nullable', 'string', 'max:100'],
            'email_demandeur' => [$complet ? 'required' : 'nullable', 'email', 'max:255'],
            'telephone_demandeur' => ['nullable', 'string', 'max:30'],
            'date_souhaitee_livraison' => ['nullable', 'date'],
            'contexte' => [$complet ? 'required' : 'nullable', 'string', 'max:2000'],
            'problematique' => [$complet ? 'required' : 'nullable', 'string', 'max:2000'],
            'objectif_general' => [$complet ? 'required' : 'nullable', 'string', 'max:1500'],
            'objectifs_specifiques' => [$complet ? 'required' : 'nullable', 'array', 'min:1', 'max:10'],
            'objectifs_specifiques.*' => ['nullable', 'string', 'max:500'],
            'description_fonctionnelle' => [$complet ? 'required' : 'nullable', 'string', 'max:3000'],
            'utilisateurs_cibles' => [$complet ? 'required' : 'nullable', 'string', 'max:1000'],
            'hors_perimetre' => ['nullable', 'string', 'max:1500'],
            'priorite' => ['required', Rule::in(['BASSE', 'MOYENNE', 'HAUTE', 'CRITIQUE'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'titre' => 'titre du projet',
            'service_demandeur' => 'service demandeur',
            'nom_demandeur' => 'nom du demandeur',
            'email_demandeur' => 'e-mail du demandeur',
            'telephone_demandeur' => 'téléphone',
            'date_souhaitee_livraison' => 'date souhaitée de livraison',
            'contexte' => 'contexte',
            'problematique' => 'problématique',
            'objectif_general' => 'objectif général',
            'objectifs_specifiques' => 'objectifs spécifiques',
            'description_fonctionnelle' => 'description fonctionnelle',
            'utilisateurs_cibles' => 'utilisateurs cibles',
            'hors_perimetre' => 'hors périmètre',
            'priorite' => 'priorité',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Veuillez choisir une action : brouillon, générer le CDC ou soumettre.',
            'action.in' => 'Action invalide.',
            'titre.required' => 'Le titre du projet est obligatoire.',
            'service_demandeur.required' => 'Le service demandeur est obligatoire (section 1).',
            'nom_demandeur.required' => 'Le nom du demandeur est obligatoire (section 1).',
            'email_demandeur.required' => 'L\'e-mail du demandeur est obligatoire (section 1).',
            'email_demandeur.email' => 'L\'e-mail du demandeur n\'est pas valide.',
            'contexte.required' => 'Le contexte est obligatoire (section 2).',
            'problematique.required' => 'La problématique est obligatoire (section 2).',
            'objectif_general.required' => 'L\'objectif général est obligatoire (section 3).',
            'objectifs_specifiques.required' => 'Ajoutez au moins un objectif spécifique (section 3).',
            'objectifs_specifiques.min' => 'Ajoutez au moins un objectif spécifique (section 3).',
            'description_fonctionnelle.required' => 'La description fonctionnelle est obligatoire (section 4).',
            'utilisateurs_cibles.required' => 'Les utilisateurs cibles sont obligatoires (section 4).',
            'priorite.required' => 'La priorité est obligatoire.',
            'priorite.in' => 'La priorité sélectionnée est invalide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $objectifs = collect($this->input('objectifs_specifiques', []))
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();

        $this->merge(['objectifs_specifiques' => $objectifs]);
    }

    public function donneesDemande(): array
    {
        return $this->only([
            'titre',
            'service_demandeur',
            'nom_demandeur',
            'email_demandeur',
            'telephone_demandeur',
            'date_souhaitee_livraison',
            'contexte',
            'problematique',
            'objectif_general',
            'objectifs_specifiques',
            'description_fonctionnelle',
            'utilisateurs_cibles',
            'hors_perimetre',
            'priorite',
        ]);
    }
}
