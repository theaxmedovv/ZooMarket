<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Bosh admin} {--password=}';

    protected $description = "Bosh admin (e'lonlarni tekshiruvchi) hisobini yaratadi yoki mavjud foydalanuvchiga admin rolini beradi";

    public function handle(): int
    {
        $email = $this->argument('email');

        $readPosts = Permission::firstOrCreate(['name' => 'read posts', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->givePermissionTo($readPosts);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::where('email', $email)->first();

        if (! $user) {
            $password = $this->option('password') ?: $this->secret('Parol (kamida 8 belgi)');

            if (! $password || strlen($password) < 8) {
                $this->error("Parol kamida 8 belgidan iborat bo'lishi kerak.");

                return self::FAILURE;
            }

            $user = User::create([
                'name' => $this->option('name'),
                'email' => $email,
                'password' => $password,
            ]);
        }

        // The main admin only reviews listings; it is neither a seller nor a buyer.
        $user->syncRoles(['admin']);

        $this->info("{$user->email} endi bosh admin. Kirgandan so'ng /moderation sahifasiga yo'naltiriladi.");

        return self::SUCCESS;
    }
}
