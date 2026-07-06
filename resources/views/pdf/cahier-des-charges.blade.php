<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Cahier des charges — {{ $demande->numero }}</title>
    <style>
        @page { margin: 80px 50px 60px 50px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; line-height: 1.5; }
        .header { border-bottom: 2px solid #003da5; padding-bottom: 12px; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { height: 55px; }
        .org-title { font-size: 10px; font-weight: bold; color: #003da5; text-transform: uppercase; }
        .doc-title { font-size: 12px; font-weight: bold; color: #003da5; margin-top: 4px; }
        .meta { margin: 16px 0; background: #e8f0fe; padding: 10px 12px; border-radius: 4px; }
        .meta-table { width: 100%; }
        .meta-table td { padding: 2px 8px 2px 0; }
        h2 { font-size: 13px; color: #003da5; border-bottom: 1px solid #e8f0fe; padding-bottom: 4px; margin-top: 18px; }
        p { margin: 6px 0; text-align: justify; }
        .label { font-weight: bold; color: #374151; }
        ol { margin: 6px 0 6px 18px; }
        table.annexes { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        table.annexes th, table.annexes td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        table.annexes th { background: #e8f0fe; color: #003da5; }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e8f0fe;
            padding-top: 8px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 90px;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo" alt="CENI RDC">
                    @endif
                </td>
                <td>
                    <div class="org-title">Commission Électorale Nationale Indépendante</div>
                    <div class="doc-title">Cahier des charges — Demande de Développement Logiciel</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta">
        <table class="meta-table">
            <tr>
                <td><span class="label">Numéro :</span> {{ $demande->numero }}</td>
                <td><span class="label">Date de soumission :</span> {{ $demande->date_soumission?->format('d/m/Y') ?? 'Non soumis' }}</td>
            </tr>
            <tr>
                <td><span class="label">Statut :</span> {{ $demande->statut->label() }}</td>
                <td><span class="label">Priorité :</span> {{ $demande->priorite->label() }}</td>
            </tr>
        </table>
    </div>

    <h2>1. Identification du demandeur</h2>
    <p><span class="label">Titre du projet :</span> {{ $demande->titre }}</p>
    <p><span class="label">Service demandeur :</span> {{ $demande->service_demandeur }}</p>
    <p><span class="label">Demandeur :</span> {{ $demande->nom_demandeur }} — {{ $demande->email_demandeur }}</p>
    <p><span class="label">Téléphone :</span> {{ $demande->telephone_demandeur ?? 'Non renseigné' }}</p>
    <p><span class="label">Date souhaitée de livraison :</span> {{ $demande->date_souhaitee_livraison?->format('d/m/Y') ?? 'Non renseignée' }}</p>

    <h2>2. Contexte et problématique</h2>
    <p><span class="label">Contexte :</span><br>{{ $demande->contexte }}</p>
    <p><span class="label">Problématique :</span><br>{{ $demande->problematique }}</p>

    <h2>3. Objectifs</h2>
    <p><span class="label">Objectif général :</span><br>{{ $demande->objectif_general }}</p>
    <p class="label">Objectifs spécifiques :</p>
    <ol>
        @foreach($demande->objectifs_specifiques ?? [] as $objectif)
            <li>{{ $objectif }}</li>
        @endforeach
    </ol>

    <h2>4. Périmètre fonctionnel</h2>
    <p><span class="label">Description fonctionnelle :</span><br>{{ $demande->description_fonctionnelle }}</p>
    <p><span class="label">Utilisateurs cibles :</span><br>{{ $demande->utilisateurs_cibles }}</p>
    @if($demande->hors_perimetre)
        <p><span class="label">Hors périmètre :</span><br>{{ $demande->hors_perimetre }}</p>
    @endif

    <div class="footer">
        Document généré automatiquement par la plateforme DDL — CENI RDC
    </div>
</body>
</html>
