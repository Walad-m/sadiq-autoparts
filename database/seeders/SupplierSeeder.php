<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Abossey Okai Wholesale', 'contact_person' => 'Kwame Mensah', 'phone' => '0244 123456', 'email' => 'kwame@abosseyokai.com', 'address' => 'Abossey Okai, Accra'],
            ['name' => 'Japan Motors Parts', 'contact_person' => 'Yaw Boateng', 'phone' => '0557 987654', 'email' => null, 'address' => 'Industrial Area, Kumasi'],
            ['name' => 'Silver Star Auto', 'contact_person' => 'Ama Serwah', 'phone' => '0302 776655', 'email' => 'parts@silverstar.gh', 'address' => 'Airport City, Accra'],
            ['name' => 'Azar Trading', 'contact_person' => 'Ali Hassan', 'phone' => '0208 334455', 'email' => null, 'address' => 'Suame Magazine, Kumasi'],
            ['name' => 'China Auto Parts', 'contact_person' => 'Chen Wei', 'phone' => '0555 667788', 'email' => 'chen@chinaauto.com', 'address' => 'Tema Port Area, Tema'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
