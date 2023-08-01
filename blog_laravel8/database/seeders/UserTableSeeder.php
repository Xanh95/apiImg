<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 1; $i < 31; $i++) {
            $user = new User;
            $user->email = "usernumber$i@gmail.com";
            $role = random_int(1, 4);
            $user->name = "user number $i";
            $user->password = Hash::make('123qweasd');
            $user->email_verified_at = Carbon::now();
            $user->status = 'active';
            $user->save();
            $user->roles()->sync($role);
        }
    }
}
