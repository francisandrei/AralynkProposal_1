<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

// Job categories (the admin can add more later)
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'IT / Technology',
            'Food / Restaurant',
            'Sales / Business',
            'Social Media / Content',
            'Tutoring / Education',
            'Agriculture / Farm',
            'Events',
            'Retail / Store',
            'Customer Service / Call Center',
            'Administrative / Clerical',
            'Accounting / Bookkeeping',
            'Marketing / Advertising',
            'Graphic Design / Multimedia',
            'Writing / Translation',
            'Photography / Videography',
            'Delivery / Logistics',
            'Driving / Transportation',
            'Construction / Carpentry',
            'Electrical / Plumbing / Repair',
            'Manufacturing / Production',
            'Warehouse / Inventory',
            'Cleaning / Housekeeping',
            'Hotel / Tourism',
            'Health / Wellness',
            'Veterinary / Animal Care',
            'Fisheries / Aquaculture',
            'Food Processing / Technology',
            'Engineering / Surveying',
            'Research / Data Encoding',
            'Beauty / Salon',
            'Laundry',
            'Security',
            'Community / Government Service',
            'Arts / Crafts',
            'Entertainment / Music',
            'Sports / Fitness',
            'Gardening / Landscaping',
            'Agribusiness / Farm Marketing',
            'Childcare',
            'Other',
        ];

        foreach ($names as $item) {
            Category::firstOrCreate(['name' => $item]);
        }
    }
}
