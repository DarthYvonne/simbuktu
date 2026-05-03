<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'slophub:make-admin {email} {--name=Admin} {--password=}';
    protected $description = 'Create or upgrade a user to admin.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Kodeord (lades tomt = random)') ?: str()->random(12);

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $this->option('name'), 'password' => Hash::make($password), 'is_admin' => true]
        );
        $user->is_admin = true;
        $user->save();

        $this->info("Admin: {$user->email}");
        $this->info("Password: {$password}");
        return self::SUCCESS;
    }
}
