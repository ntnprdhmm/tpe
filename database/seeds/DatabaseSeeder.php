<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(UserCatagoryTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(GamesTableSeeder::class);
        $this->call(SynonymsTableSeeder::class);

        Model::reguard();
    }
}
