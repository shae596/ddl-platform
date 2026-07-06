<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numerotation_ddl', function (Blueprint $table) {
            $table->integer('annee')->primary();
            $table->integer('dernier_numero')->default(0);
        });

        Schema::create('demandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero')->nullable()->unique();
            $table->string('statut')->default('BROUILLON');
            $table->string('priorite')->default('MOYENNE');
            $table->string('titre');
            $table->string('service_demandeur');
            $table->string('nom_demandeur');
            $table->string('email_demandeur');
            $table->string('telephone_demandeur')->nullable();
            $table->date('date_souhaitee_livraison')->nullable();
            $table->text('contexte')->nullable();
            $table->text('problematique')->nullable();
            $table->text('objectif_general')->nullable();
            $table->json('objectifs_specifiques')->default('[]');
            $table->text('description_fonctionnelle')->nullable();
            $table->text('utilisateurs_cibles')->nullable();
            $table->text('hors_perimetre')->nullable();
            $table->text('contraintes_techniques')->nullable();
            $table->text('contraintes_reglementaires')->nullable();
            $table->text('dependances')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamp('date_soumission')->nullable();
            $table->foreignUuid('auteur_id')->constrained('users');
            $table->timestamps();

            $table->index('statut');
            $table->index('auteur_id');
            $table->index('date_soumission');
        });

        Schema::create('pieces_jointes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('nom_original');
            $table->string('mime_type');
            $table->integer('taille_octets');
            $table->string('chemin_stockage');
            $table->string('type')->default('ANNEXE');
            $table->foreignUuid('uploade_par_id')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->index('demande_id');
        });

        Schema::create('commentaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignUuid('auteur_id')->constrained('users');
            $table->text('contenu');
            $table->boolean('interne')->default(false);
            $table->timestamps();
            $table->index('demande_id');
        });

        Schema::create('historique_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignUuid('utilisateur_id')->constrained('users');
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut');
            $table->string('action');
            $table->text('commentaire')->nullable();
            $table->json('metadonnees')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('demande_id');
            $table->index('utilisateur_id');
            $table->index('created_at');
        });

        Schema::create('affectations_dev', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignUuid('developpeur_id')->constrained('users');
            $table->uuid('affecte_par_id')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['demande_id', 'developpeur_id']);
            $table->index('developpeur_id');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('demande_id')->nullable()->constrained('demandes')->nullOnDelete();
            $table->string('type');
            $table->string('titre');
            $table->text('message');
            $table->boolean('lue')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'lue']);
            $table->index('created_at');
        });

        Schema::create('parametres', function (Blueprint $table) {
            $table->string('cle')->primary();
            $table->text('valeur');
            $table->string('description')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('affectations_dev');
        Schema::dropIfExists('historique_actions');
        Schema::dropIfExists('commentaires');
        Schema::dropIfExists('pieces_jointes');
        Schema::dropIfExists('demandes');
        Schema::dropIfExists('numerotation_ddl');
    }
};
