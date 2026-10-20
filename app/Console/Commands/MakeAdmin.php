<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

// Désigne un superadmin. Aucun moyen de se promouvoir depuis l'interface.
class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email} {--revoke : Retire le rôle administrateur}';

    protected $description = 'Accorde (ou retire avec --revoke) le rôle administrateur à un compte';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('Aucun compte avec cet e-mail.');
            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => !$this->option('revoke')])->save();
        $this->info(($user->is_admin ? 'Administrateur : ' : 'Rôle retiré : ') . $user->email);

        return self::SUCCESS;
    }
}
