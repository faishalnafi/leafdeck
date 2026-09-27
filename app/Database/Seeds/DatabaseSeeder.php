<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $userTable = $this->db->table('users');

        $exists = $userTable->where('role', 'superadmin')->countAllResults();

        if ($exists === 0) {
            $userTable->insert([
                'sso_user_id' => '00000000-0000-0000-0000-000000000001',
                'nomor_induk' => 'ADMIN001',
                'nama'        => 'Super Administrator',
                'email'       => 'superadmin@sman3mjk.sch.id',
                'role'        => 'superadmin',
                'avatar'      => null,
                'is_active'   => 1,
                'created_at'  => Time::now()->toDateTimeString(),
                'updated_at'  => Time::now()->toDateTimeString(),
            ]);
        }
    }
}
