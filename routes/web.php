<?php

use App\Http\Controllers\Agent\AgentDashboardController;
use App\Http\Controllers\Agent\DemandeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Di\DemandeController as DiDemandeController;
use App\Http\Controllers\Di\DiDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HistoriqueController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Developpeur\DemandeController as DeveloppeurDemandeController;
use App\Http\Controllers\Developpeur\DeveloppeurDashboardController;
use App\Http\Controllers\Secretariat\DemandeController as SecretariatDemandeController;
use App\Http\Controllers\Secretariat\SecretariatDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->role->dashboardRoute());
    }

    return redirect()->route('login');
});

Route::get('/ping', fn () => response('pong', 200));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('role:AGENT')->prefix('agent')->name('agent.')->group(function () {
        Route::get('/', [AgentDashboardController::class, 'index'])->name('dashboard');
        Route::resource('demandes', DemandeController::class);
        Route::get('demandes/{demande}/cahier-des-charges', [DemandeController::class, 'downloadCahier'])
            ->name('demandes.cahier');
        Route::get('demandes/{demande}/historique', [DemandeController::class, 'historique'])
            ->name('demandes.historique');
    });

    Route::middleware('role:SECRETARIAT')->prefix('secretariat')->name('secretariat.')->group(function () {
        Route::get('/', [SecretariatDashboardController::class, 'index'])->name('dashboard');
        Route::get('demandes', [SecretariatDemandeController::class, 'index'])->name('demandes.index');
        Route::get('demandes/{demande}', [SecretariatDemandeController::class, 'show'])->name('demandes.show');
        Route::get('demandes/{demande}/cahier-des-charges', [SecretariatDemandeController::class, 'downloadCahier'])
            ->name('demandes.cahier');
        Route::get('demandes/{demande}/historique', [SecretariatDemandeController::class, 'historique'])
            ->name('demandes.historique');
        Route::post('demandes/{demande}/recevoir', [SecretariatDemandeController::class, 'recevoir'])
            ->name('demandes.recevoir');
        Route::post('demandes/{demande}/transferer-di', [SecretariatDemandeController::class, 'transfererDi'])
            ->name('demandes.transferer');
    });

    Route::middleware('role:DIRECTION_INFORMATIQUE')->prefix('di')->name('di.')->group(function () {
        Route::get('/', [DiDashboardController::class, 'index'])->name('dashboard');
        Route::get('demandes', [DiDemandeController::class, 'index'])->name('demandes.index');
        Route::get('demandes/{demande}', [DiDemandeController::class, 'show'])->name('demandes.show');
        Route::get('demandes/{demande}/cahier-des-charges', [DiDemandeController::class, 'downloadCahier'])
            ->name('demandes.cahier');
        Route::get('demandes/{demande}/historique', [DiDemandeController::class, 'historique'])
            ->name('demandes.historique');
        Route::post('demandes/{demande}/prendre-en-charge', [DiDemandeController::class, 'prendreEnCharge'])
            ->name('demandes.prendre-en-charge');
        Route::post('demandes/{demande}/mettre-en-attente', [DiDemandeController::class, 'mettreEnAttente'])
            ->name('demandes.mettre-en-attente');
        Route::post('demandes/{demande}/reprendre', [DiDemandeController::class, 'reprendreAnalyse'])
            ->name('demandes.reprendre');
        Route::post('demandes/{demande}/valider', [DiDemandeController::class, 'valider'])
            ->name('demandes.valider');
        Route::post('demandes/{demande}/rejeter', [DiDemandeController::class, 'rejeter'])
            ->name('demandes.rejeter');
        Route::post('demandes/{demande}/demander-correction', [DiDemandeController::class, 'demanderCorrection'])
            ->name('demandes.demander-correction');
        Route::post('demandes/{demande}/delai', [DiDemandeController::class, 'definirDelai'])
            ->name('demandes.delai');
        Route::post('demandes/{demande}/affecter', [DiDemandeController::class, 'affecter'])
            ->name('demandes.affecter');
        Route::post('demandes/{demande}/commentaires', [DiDemandeController::class, 'commenter'])
            ->name('demandes.commenter');
    });

    Route::middleware('role:DEVELOPPEUR')->prefix('developpeur')->name('developpeur.')->group(function () {
        Route::get('/', [DeveloppeurDashboardController::class, 'index'])->name('dashboard');
        Route::get('demandes', [DeveloppeurDemandeController::class, 'index'])->name('demandes.index');
        Route::get('demandes/{demande}', [DeveloppeurDemandeController::class, 'show'])->name('demandes.show');
        Route::get('demandes/{demande}/cahier-des-charges', [DeveloppeurDemandeController::class, 'downloadCahier'])
            ->name('demandes.cahier');
        Route::get('demandes/{demande}/historique', [DeveloppeurDemandeController::class, 'historique'])
            ->name('demandes.historique');
        Route::post('demandes/{demande}/demarrer', [DeveloppeurDemandeController::class, 'demarrer'])
            ->name('demandes.demarrer');
        Route::post('demandes/{demande}/passer-en-test', [DeveloppeurDemandeController::class, 'passerEnTest'])
            ->name('demandes.passer-en-test');
        Route::post('demandes/{demande}/commentaires', [DeveloppeurDemandeController::class, 'commenter'])
            ->name('demandes.commenter');
    });

    Route::middleware('role:ADMIN')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('historique', [HistoriqueController::class, 'index'])->name('historique.index');
        Route::get('parametres', [ParametreController::class, 'index'])->name('parametres.index');
        Route::put('parametres/notifications', [ParametreController::class, 'updateNotifications'])
            ->name('parametres.notifications');
    });
});
