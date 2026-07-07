<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\NumerotationDdlService;
use App\Services\ParametreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['email' => 'agent@ceni.cd', 'nom' => 'Kabila', 'prenom' => 'Jean', 'telephone' => '0892905498', 'role' => UserRole::Agent, 'service' => 'Direction des opérations'],
            ['email' => 'secretariat@ceni.cd', 'nom' => 'Mukendi', 'prenom' => 'Marie', 'telephone' => '0891111111', 'role' => UserRole::Secretariat, 'service' => 'Secrétariat général'],
            ['email' => 'di@ceni.cd', 'nom' => 'Lubala', 'prenom' => 'Paul', 'telephone' => '0892222222', 'role' => UserRole::DirectionInformatique, 'service' => 'Direction Informatique'],
            ['email' => 'dev@ceni.cd', 'nom' => 'Tshimanga', 'prenom' => 'David', 'telephone' => '0893333333', 'role' => UserRole::Developpeur, 'service' => 'Direction Informatique'],
            ['email' => 'sharonemulembweng@gmail.com', 'nom' => 'Mulembwe', 'prenom' => 'Sharone', 'telephone' => '0894444444', 'role' => UserRole::Admin, 'service' => 'Direction Informatique'],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrNew(['email' => $data['email']]);
            $user->fill([
                ...$data,
                'actif' => true,
            ]);
            $user->password = 'Password123!';
            $user->save();
        }

        User::query()->where('email', 'admin@ceni.cd')->update(['actif' => false]);

        app(NumerotationDdlService::class)->synchroniserCompteur();

        $parametres = app(ParametreService::class);
        foreach (ParametreService::NOTIFICATIONS as $type => $cle) {
            $parametres->definir($cle, '1', 'Notification : '.$type);
        }
    }
}
