<?php

namespace Database\Seeders;

use App\Models\AlumniUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Usage: php artisan db:seed --class=SuperAdminSeeder
     *
     * Edit the email/password below before running, or override via env
     * if you prefer. This only runs once — if the email already exists,
     * the seeder skips creation.
     */
    public function run(): void
    {
        $email    = env('SUPER_ADMIN_EMAIL', 'superadmin@iccr.gov.in');
        $password = env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');

        if (AlumniUser::where('email', $email)->exists()) {
            $this->command->warn("Super admin with email {$email} already exists. Skipping.");
            return;
        }

        AlumniUser::create([
            'full_name'    => 'Super Admin',
            'email'        => $email,
            'password'     => Hash::make($password),
            'role'         => 'super_admin',
            'is_approved'  => true,

            // Placeholder values for required (NOT NULL) alumni columns
            'batch_name'   => 'N/A',
            'phone'        => 'N/A',
            'department'   => 'Administration',
            'passing_year' => date('Y'),
            'roll_number'  => 'SUPERADMIN-' . strtoupper(uniqid()),
            'attachment'   => 'none',
            'birth_date'   => now()->subYears(30)->toDateString(),
            'gender'       => 'Other',
            'institute'    => 'ICCR',
        ]);

        $this->command->info("Super admin created: {$email} / {$password}");
        $this->command->warn('IMPORTANT: Log in and change this password immediately.');
    }
}