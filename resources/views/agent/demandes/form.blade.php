@extends('layouts.agent')

@section('title', $demande->exists ? 'Modifier la demande' : 'Nouvelle demande')

@section('content')
<form
    method="POST"
    action="{{ $demande->exists ? route('agent.demandes.update', $demande) : route('agent.demandes.store') }}"
    class="demande-form"
>
    @csrf
    @if($demande->exists)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <p><strong>Le formulaire contient des erreurs :</strong></p>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            @if(in_array(old('action'), ['generer_cdc', 'soumettre'], true))
                <p class="text-sm" style="margin-top:0.5rem;">Pour générer ou soumettre le cahier des charges, les sections 1 à 4 doivent être complètes. Sinon, utilisez « Enregistrer brouillon ».</p>
            @endif
        </div>
    @endif

    @if($demande->exists && ($aCahier ?? false))
        <div class="alert alert-success">
            <p><strong>Cahier des charges disponible</strong> — Numéro : {{ $demande->numero }}</p>
            <p class="text-sm">Téléchargez le PDF pour le vérifier, puis soumettez-le au secrétariat lorsque vous êtes prêt.</p>
            <a href="{{ route('agent.demandes.cahier', $demande) }}" class="btn btn-outline btn-sm" style="margin-top:0.5rem;">Télécharger le cahier des charges (PDF)</a>
        </div>
    @endif

    <fieldset class="form-section">
        <legend>1. Identification</legend>
        <div class="form-grid">
            <div class="form-group form-group--full">
                <label for="titre">Titre du projet *</label>
                <input id="titre" name="titre" value="{{ old('titre', $demande->titre) }}" required maxlength="200">
            </div>
            <div class="form-group">
                <label for="service_demandeur">Service demandeur</label>
                <input id="service_demandeur" name="service_demandeur" value="{{ old('service_demandeur', $demande->service_demandeur) }}" maxlength="150">
            </div>
            <div class="form-group">
                <label for="priorite">Priorité</label>
                <select id="priorite" name="priorite">
                    @foreach($priorites as $p)
                        <option value="{{ $p->value }}" @selected(old('priorite', $demande->priorite?->value ?? 'MOYENNE') === $p->value)>{{ $p->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="nom_demandeur">Nom du demandeur</label>
                <input id="nom_demandeur" name="nom_demandeur" value="{{ old('nom_demandeur', $demande->nom_demandeur) }}">
            </div>
            <div class="form-group">
                <label for="email_demandeur">Email</label>
                <input id="email_demandeur" type="email" name="email_demandeur" value="{{ old('email_demandeur', $demande->email_demandeur) }}">
            </div>
            <div class="form-group">
                <label for="telephone_demandeur">Téléphone</label>
                <input id="telephone_demandeur" name="telephone_demandeur" value="{{ old('telephone_demandeur', $demande->telephone_demandeur) }}">
            </div>
            <div class="form-group">
                <label for="date_souhaitee_livraison">Date souhaitée de livraison</label>
                <input id="date_souhaitee_livraison" type="date" name="date_souhaitee_livraison" value="{{ old('date_souhaitee_livraison', $demande->date_souhaitee_livraison?->format('Y-m-d')) }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>2. Contexte et problématique</legend>
        <div class="form-group">
            <label for="contexte">Contexte</label>
            <textarea id="contexte" name="contexte" rows="4">{{ old('contexte', $demande->contexte) }}</textarea>
        </div>
        <div class="form-group">
            <label for="problematique">Problématique</label>
            <textarea id="problematique" name="problematique" rows="4">{{ old('problematique', $demande->problematique) }}</textarea>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>3. Objectifs</legend>
        <div class="form-group">
            <label for="objectif_general">Objectif général</label>
            <textarea id="objectif_general" name="objectif_general" rows="3">{{ old('objectif_general', $demande->objectif_general) }}</textarea>
        </div>
        <div class="form-group">
            <label>Objectifs spécifiques</label>
            <div id="objectifs-list">
                @foreach(old('objectifs_specifiques', $demande->objectifs_specifiques ?? ['']) as $i => $obj)
                    <div class="objectif-row">
                        <input type="text" name="objectifs_specifiques[]" value="{{ $obj }}" placeholder="Objectif {{ $i + 1 }}">
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline btn-sm" id="add-objectif">+ Ajouter un objectif</button>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>4. Périmètre fonctionnel</legend>
        <div class="form-group">
            <label for="description_fonctionnelle">Description fonctionnelle</label>
            <textarea id="description_fonctionnelle" name="description_fonctionnelle" rows="4">{{ old('description_fonctionnelle', $demande->description_fonctionnelle) }}</textarea>
        </div>
        <div class="form-group">
            <label for="utilisateurs_cibles">Utilisateurs cibles</label>
            <textarea id="utilisateurs_cibles" name="utilisateurs_cibles" rows="3">{{ old('utilisateurs_cibles', $demande->utilisateurs_cibles) }}</textarea>
        </div>
        <div class="form-group">
            <label for="hors_perimetre">Hors périmètre</label>
            <textarea id="hors_perimetre" name="hors_perimetre" rows="3">{{ old('hors_perimetre', $demande->hors_perimetre) }}</textarea>
        </div>
    </fieldset>

    <div class="form-actions">
        <a href="{{ route('agent.demandes.index') }}" class="btn btn-outline">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn btn-outline">Enregistrer brouillon</button>
        <button type="submit" name="action" value="generer_cdc" class="btn btn-outline">Générer le cahier des charges</button>
        @if($demande->exists && ($aCahier ?? false))
            <a href="{{ route('agent.demandes.cahier', $demande) }}" class="btn btn-outline">Télécharger le PDF</a>
            <button type="submit" name="action" value="soumettre" class="btn btn-primary">Soumettre le cahier des charges</button>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('add-objectif')?.addEventListener('click', () => {
    const list = document.getElementById('objectifs-list');
    if (list.children.length >= 10) return;
    const row = document.createElement('div');
    row.className = 'objectif-row';
    row.innerHTML = '<input type="text" name="objectifs_specifiques[]" placeholder="Objectif">';
    list.appendChild(row);
});
</script>
@endpush
