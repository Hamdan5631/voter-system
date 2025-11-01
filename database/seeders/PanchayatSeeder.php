<?php

namespace Database\Seeders;

use App\Models\Panchayat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PanchayatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $panchayats = [
            // Thiruvananthapuram District
            ['name' => 'Varkala', 'code' => 'TVM001', 'district' => 'Thiruvananthapuram', 'description' => 'Varkala Panchayat'],
            ['name' => 'Attingal', 'code' => 'TVM002', 'district' => 'Thiruvananthapuram', 'description' => 'Attingal Panchayat'],
            ['name' => 'Nedumangad', 'code' => 'TVM003', 'district' => 'Thiruvananthapuram', 'description' => 'Nedumangad Panchayat'],
            ['name' => 'Kattakkada', 'code' => 'TVM004', 'district' => 'Thiruvananthapuram', 'description' => 'Kattakkada Panchayat'],
            
            // Kollam District
            ['name' => 'Karunagappally', 'code' => 'KLM001', 'district' => 'Kollam', 'description' => 'Karunagappally Panchayat'],
            ['name' => 'Kottarakkara', 'code' => 'KLM002', 'district' => 'Kollam', 'description' => 'Kottarakkara Panchayat'],
            ['name' => 'Pathanapuram', 'code' => 'KLM003', 'district' => 'Kollam', 'description' => 'Pathanapuram Panchayat'],
            ['name' => 'Kunnathur', 'code' => 'KLM004', 'district' => 'Kollam', 'description' => 'Kunnathur Panchayat'],
            
            // Pathanamthitta District
            ['name' => 'Ranni', 'code' => 'PTA001', 'district' => 'Pathanamthitta', 'description' => 'Ranni Panchayat'],
            ['name' => 'Kozhenchery', 'code' => 'PTA002', 'district' => 'Pathanamthitta', 'description' => 'Kozhenchery Panchayat'],
            ['name' => 'Adoor', 'code' => 'PTA003', 'district' => 'Pathanamthitta', 'description' => 'Adoor Panchayat'],
            ['name' => 'Mallappally', 'code' => 'PTA004', 'district' => 'Pathanamthitta', 'description' => 'Mallappally Panchayat'],
            
            // Alappuzha District
            ['name' => 'Ambalappuzha', 'code' => 'ALP001', 'district' => 'Alappuzha', 'description' => 'Ambalappuzha Panchayat'],
            ['name' => 'Cherthala', 'code' => 'ALP002', 'district' => 'Alappuzha', 'description' => 'Cherthala Panchayat'],
            ['name' => 'Kayamkulam', 'code' => 'ALP003', 'district' => 'Alappuzha', 'description' => 'Kayamkulam Panchayat'],
            ['name' => 'Kuttanad', 'code' => 'ALP004', 'district' => 'Alappuzha', 'description' => 'Kuttanad Panchayat'],
            
            // Kottayam District
            ['name' => 'Changanassery', 'code' => 'KTM001', 'district' => 'Kottayam', 'description' => 'Changanassery Panchayat'],
            ['name' => 'Vaikom', 'code' => 'KTM002', 'district' => 'Kottayam', 'description' => 'Vaikom Panchayat'],
            ['name' => 'Palai', 'code' => 'KTM003', 'district' => 'Kottayam', 'description' => 'Palai Panchayat'],
            ['name' => 'Kanjirappally', 'code' => 'KTM004', 'district' => 'Kottayam', 'description' => 'Kanjirappally Panchayat'],
            
            // Idukki District
            ['name' => 'Thodupuzha', 'code' => 'IDK001', 'district' => 'Idukki', 'description' => 'Thodupuzha Panchayat'],
            ['name' => 'Devikulam', 'code' => 'IDK002', 'district' => 'Idukki', 'description' => 'Devikulam Panchayat'],
            ['name' => 'Udumbanchola', 'code' => 'IDK003', 'district' => 'Idukki', 'description' => 'Udumbanchola Panchayat'],
            ['name' => 'Peerumedu', 'code' => 'IDK004', 'district' => 'Idukki', 'description' => 'Peerumedu Panchayat'],
            
            // Ernakulam District
            ['name' => 'Kothamangalam', 'code' => 'EKM001', 'district' => 'Ernakulam', 'description' => 'Kothamangalam Panchayat'],
            ['name' => 'Muvattupuzha', 'code' => 'EKM002', 'district' => 'Ernakulam', 'description' => 'Muvattupuzha Panchayat'],
            ['name' => 'Perumbavoor', 'code' => 'EKM003', 'district' => 'Ernakulam', 'description' => 'Perumbavoor Panchayat'],
            ['name' => 'Angamaly', 'code' => 'EKM004', 'district' => 'Ernakulam', 'description' => 'Angamaly Panchayat'],
            
            // Thrissur District
            ['name' => 'Kodungallur', 'code' => 'TSR001', 'district' => 'Thrissur', 'description' => 'Kodungallur Panchayat'],
            ['name' => 'Guruvayur', 'code' => 'TSR002', 'district' => 'Thrissur', 'description' => 'Guruvayur Panchayat'],
            ['name' => 'Chalakudy', 'code' => 'TSR003', 'district' => 'Thrissur', 'description' => 'Chalakudy Panchayat'],
            ['name' => 'Kunnamkulam', 'code' => 'TSR004', 'district' => 'Thrissur', 'description' => 'Kunnamkulam Panchayat'],
            
            // Palakkad District
            ['name' => 'Ottapalam', 'code' => 'PLK001', 'district' => 'Palakkad', 'description' => 'Ottapalam Panchayat'],
            ['name' => 'Mannarkkad', 'code' => 'PLK002', 'district' => 'Palakkad', 'description' => 'Mannarkkad Panchayat'],
            ['name' => 'Alathur', 'code' => 'PLK003', 'district' => 'Palakkad', 'description' => 'Alathur Panchayat'],
            ['name' => 'Chittur', 'code' => 'PLK004', 'district' => 'Palakkad', 'description' => 'Chittur Panchayat'],
            
            // Malappuram District
            ['name' => 'Perinthalmanna', 'code' => 'MLP001', 'district' => 'Malappuram', 'description' => 'Perinthalmanna Panchayat'],
            ['name' => 'Tirur', 'code' => 'MLP002', 'district' => 'Malappuram', 'description' => 'Tirur Panchayat'],
            ['name' => 'Ponnani', 'code' => 'MLP003', 'district' => 'Malappuram', 'description' => 'Ponnani Panchayat'],
            ['name' => 'Nilambur', 'code' => 'MLP004', 'district' => 'Malappuram', 'description' => 'Nilambur Panchayat'],
            
            // Kozhikode District
            ['name' => 'Koyilandy', 'code' => 'KKD001', 'district' => 'Kozhikode', 'description' => 'Koyilandy Panchayat'],
            ['name' => 'Vadakara', 'code' => 'KKD002', 'district' => 'Kozhikode', 'description' => 'Vadakara Panchayat'],
            ['name' => 'Quilandy', 'code' => 'KKD003', 'district' => 'Kozhikode', 'description' => 'Quilandy Panchayat'],
            ['name' => 'Balussery', 'code' => 'KKD004', 'district' => 'Kozhikode', 'description' => 'Balussery Panchayat'],
            
            // Wayanad District
            ['name' => 'Kalpetta', 'code' => 'WYD001', 'district' => 'Wayanad', 'description' => 'Kalpetta Panchayat'],
            ['name' => 'Sulthan Bathery', 'code' => 'WYD002', 'district' => 'Wayanad', 'description' => 'Sulthan Bathery Panchayat'],
            ['name' => 'Mananthavady', 'code' => 'WYD003', 'district' => 'Wayanad', 'description' => 'Mananthavady Panchayat'],
            ['name' => 'Vythiri', 'code' => 'WYD004', 'district' => 'Wayanad', 'description' => 'Vythiri Panchayat'],
            
            // Kannur District
            ['name' => 'Thalassery', 'code' => 'KNR001', 'district' => 'Kannur', 'description' => 'Thalassery Panchayat'],
            ['name' => 'Payyannur', 'code' => 'KNR002', 'district' => 'Kannur', 'description' => 'Payyannur Panchayat'],
            ['name' => 'Taliparamba', 'code' => 'KNR003', 'district' => 'Kannur', 'description' => 'Taliparamba Panchayat'],
            ['name' => 'Iritty', 'code' => 'KNR004', 'district' => 'Kannur', 'description' => 'Iritty Panchayat'],
            
            // Kasaragod District
            ['name' => 'Kanhangad', 'code' => 'KSD001', 'district' => 'Kasaragod', 'description' => 'Kanhangad Panchayat'],
            ['name' => 'Nileshwar', 'code' => 'KSD002', 'district' => 'Kasaragod', 'description' => 'Nileshwar Panchayat'],
            ['name' => 'Manjeshwar', 'code' => 'KSD003', 'district' => 'Kasaragod', 'description' => 'Manjeshwar Panchayat'],
            ['name' => 'Hosdurg', 'code' => 'KSD004', 'district' => 'Kasaragod', 'description' => 'Hosdurg Panchayat'],
        ];

        foreach ($panchayats as $panchayat) {
            Panchayat::firstOrCreate(
                ['code' => $panchayat['code']],
                $panchayat
            );
        }

        $this->command->info('Panchayats seeded successfully!');
    }
}
