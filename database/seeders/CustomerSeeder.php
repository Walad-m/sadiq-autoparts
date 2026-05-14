<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Kwame Asante', 'phone' => '0244 556677'],
            ['name' => 'Ama Serwah', 'phone' => '0557 112233'],
            ['name' => 'Kofi Mensah', 'phone' => '0208 334455'],
            ['name' => 'Yaa Adomako', 'phone' => '0555 998877'],
            ['name' => 'Nana Osei', 'phone' => '0244 223344'],
            ['name' => 'Akosua Boateng', 'phone' => '0557 667788'],
            ['name' => 'Yaw Frimpong', 'phone' => '0208 445566'],
            ['name' => 'Abena Kyei', 'phone' => '0555 334455'],
            ['name' => 'Kojo Darko', 'phone' => '0244 778899'],
            ['name' => 'Efua Owusu', 'phone' => '0557 223311'],
            ['name' => 'Papa Brew', 'phone' => '0208 556677'],
            ['name' => 'Afia Appiah', 'phone' => '0555 889900'],
            ['name' => 'Kweku Badu', 'phone' => '0244 112244'],
            ['name' => 'Adjoa Manu', 'phone' => '0557 445566'],
            ['name' => 'Fiifi Agyemang', 'phone' => '0208 998877'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['name' => $customer['name']], $customer);
        }
    }
}
