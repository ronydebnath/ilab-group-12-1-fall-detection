<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin-user';
    protected $description = 'Create an admin user';

    public function handle()
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@fall-detection.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->info('Admin user created successfully!');
        $this->info('Email: admin@fall-detection.com');
        $this->info('Password: password');

        return Command::SUCCESS;
    }
} 