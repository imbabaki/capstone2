<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@instaprint.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'InstaPrint',
                'email' => 'admin@instaprint.com',
                'password' => Hash::make('InstaPrint2025Nov'),
                'is_admin' => true,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Name: InstaPrint');
            $this->command->info('Email: admin@instaprint.com');
            $this->command->info('Password: InstaPrint2025Nov');
        } else {
            $this->command->warn('Admin user already exists! Updating credentials...');

            // Update existing admin user
            $admin = User::where('email', 'admin@instaprint.com')->first();
            $admin->update([
                'name' => 'InstaPrint',
                'password' => Hash::make('InstaPrint2025Nov'),
                'is_admin' => true,
            ]);

            $this->command->info('Admin user updated successfully!');
            $this->command->info('Name: InstaPrint');
            $this->command->info('Email: admin@instaprint.com');
            $this->command->info('Password: InstaPrint2025Nov');
        }
    }
}
