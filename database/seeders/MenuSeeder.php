<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Biryani & Rice', 'slug' => 'biryani', 'sort' => 1],
            ['name' => 'Karahi & Curries', 'slug' => 'karahi', 'sort' => 2],
            ['name' => 'BBQ & Grill', 'slug' => 'bbq', 'sort' => 3],
            ['name' => 'Vegetarian', 'slug' => 'vegetarian', 'sort' => 4],
            ['name' => 'Sides', 'slug' => 'sides', 'sort' => 5],
            ['name' => 'Breads', 'slug' => 'breads', 'sort' => 6],
            ['name' => 'Desserts', 'slug' => 'desserts', 'sort' => 7],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }

        $ids = Category::query()->pluck('id', 'slug');

        $items = [
            ['Chicken Biryani', 'chicken-biryani', 'biryani', 'Aromatic basmati rice with tender chicken, traditional spices and fresh herbs.', 950, 'images/menu/biryani.jpg', 1],
            ['Mutton Biryani', 'mutton-biryani', 'biryani', 'Slow-cooked mutton layered with saffron basmati rice and fried onions.', 1450, 'images/menu/biryani.jpg', 2],
            ['Chicken Karahi', 'chicken-karahi', 'karahi', 'Classic Lahori-style chicken cooked in a rich tomato and green chilli gravy.', 1050, 'images/menu/karahi.jpg', 3],
            ['Beef Nihari', 'beef-nihari', 'karahi', 'A slow-cooked classic with tender beef, aromatic spices and a rich, velvety gravy.', 1250, 'images/menu/nihari.jpg', 4],
            ['Palak Chicken', 'palak-chicken', 'karahi', 'Tender chicken cooked in a rich, flavorful spinach gravy with a homestyle touch.', 800, 'images/menu/palak.jpg', 5],
            ['Chicken Seekh Kebab', 'chicken-seekh-kebab', 'bbq', 'Juicy, smoky kebabs made with seasoned minced chicken, grilled to perfection.', 850, 'images/menu/kebab.jpg', 6],
            ['Beef Seekh Kabab', 'beef-seekh-kabab', 'bbq', 'Grilled, flavorful beef kebabs served with mint chutney and onion salad.', 950, 'images/menu/kebab.jpg', 7],
            ['Chicken Tikka', 'chicken-tikka', 'bbq', 'Charred chicken tikka marinated in yoghurt, chilli, and warm spices.', 900, 'images/menu/tikka.jpg', 8],
            ['Daal Tadka', 'daal-tadka', 'vegetarian', 'Comforting lentils tempered with fragrant spices, a Pakistani home-style favourite.', 550, 'images/menu/daal.jpg', 9],
            ['Daal Chana with Tandoori Roti', 'daal-chana', 'vegetarian', 'Chickpea daal finished with tadka, served with tandoori roti.', 600, 'images/menu/daal.jpg', 10],
            ['Mixed Sabzi', 'mixed-sabzi', 'vegetarian', 'Seasonal vegetables cooked with cumin, garlic, and a light home-style masala.', 500, 'images/menu/salad.jpg', 11],
            ['Mint Raita', 'mint-raita', 'sides', 'Cool yoghurt with mint, cumin, and cucumber.', 150, 'images/menu/salad.jpg', 12],
            ['Kachumber Salad', 'kachumber-salad', 'sides', 'Fresh onion, tomato, and cucumber with lemon and chaat masala.', 120, 'images/menu/salad.jpg', 13],
            ['Tandoori Roti', 'tandoori-roti', 'breads', 'Soft whole-wheat roti blistered in the tandoor.', 40, 'images/menu/naan.jpg', 14],
            ['Garlic Naan', 'garlic-naan', 'breads', 'Tandoor naan brushed with garlic butter and coriander.', 80, 'images/menu/naan.jpg', 15],
            ['Shahi Kheer', 'shahi-kheer', 'desserts', 'A rich and creamy rice pudding with milk, cardamom and nuts.', 450, 'images/menu/dessert.jpg', 16],
            ['Gulab Jamun', 'gulab-jamun', 'desserts', 'Warm milk dumplings soaked in cardamom syrup.', 350, 'images/menu/dessert.jpg', 17],
        ];

        foreach ($items as [$name, $slug, $category, $description, $price, $image, $sort]) {
            MenuItem::query()->updateOrCreate(['slug' => $slug], [
                'category_id' => $ids[$category],
                'name' => $name,
                'description' => $description,
                'price_per_person' => $price,
                'image' => $image,
                'is_active' => true,
                'sort' => $sort,
            ]);
        }
    }
}
