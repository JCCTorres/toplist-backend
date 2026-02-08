<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe um usuário admin
        if (!User::where('email', 'admin@admin.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]);

            $this->command->info('Usuário admin criado com sucesso!');
            $this->command->info('Email: admin@admin.com');
            $this->command->info('Senha: 123456');
        } else {
            $this->command->info('Usuário admin já existe!');
        }
    }
}