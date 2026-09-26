<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class Gs1GpcCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gpcMappings = [
            'cloths'             => ['code' => '10001363', 'title' => 'Clothing - Tops/Shirts/Apparel'],
            'hygge-cotton'       => ['code' => '10001363', 'title' => 'Clothing - Tops/Shirts/Apparel'],
            'cap'                => ['code' => '10001358', 'title' => 'Clothing - Headwear/Hats/Caps'],
            'socks'              => ['code' => '10001370', 'title' => 'Clothing - Hosiery/Socks'],
            'grill-gloves'       => ['code' => '10001340', 'title' => 'Clothing - Handwear/Gloves'],
            'mug'                => ['code' => '10001452', 'title' => 'Tableware - Mugs/Cups/Drinkware'],
            'bottle'             => ['code' => '10001455', 'title' => 'Tableware - Bottles/Flasks'],
            'small-glass'        => ['code' => '10001450', 'title' => 'Tableware - Glassware/Shot Glasses'],
            'plate'              => ['code' => '10001450', 'title' => 'Tableware - Plates/Dishes'],
            'spoon'              => ['code' => '10001450', 'title' => 'Tableware - Cutlery/Spoons'],
            'ceramic-coaster'    => ['code' => '10001450', 'title' => 'Tableware - Coasters'],
            'opener-tools'       => ['code' => '10001450', 'title' => 'Tableware - Bottle Openers/Bar Tools'],
            'knife'              => ['code' => '10001450', 'title' => 'Cutlery - Pocket Knives/Cutlery'],
            'slicer'             => ['code' => '10001450', 'title' => 'Tableware - Cheese Slicers/Kitchen Tools'],
            'tote-bag'           => ['code' => '10002130', 'title' => 'Bags/Luggage - Tote Bags/Shopping Bags'],
            'bag'                => ['code' => '10002130', 'title' => 'Bags/Luggage - Handbags/Backpacks'],
            'purse'              => ['code' => '10002130', 'title' => 'Bags/Luggage - Purses/Wallets'],
            'luggage-tag'        => ['code' => '10002130', 'title' => 'Bags/Luggage - Luggage Tags'],
            'plastic-bag'        => ['code' => '10002130', 'title' => 'Packaging - Carrier/Plastic Bags'],
            'magnet'             => ['code' => '10002145', 'title' => 'Souvenirs - Decorative Magnets'],
            'pin'                => ['code' => '10002145', 'title' => 'Souvenirs - Lapel Pins/Badges'],
            'patches'            => ['code' => '10002145', 'title' => 'Souvenirs - Badges/Embroidered Patches'],
            'photo-frame'        => ['code' => '10002145', 'title' => 'Souvenirs - Picture Frames'],
            'snowball'           => ['code' => '10002145', 'title' => 'Souvenirs - Snow Globes'],
            'ribbon'             => ['code' => '10002145', 'title' => 'Souvenirs - Decorative Ribbons'],
            'figurine'           => ['code' => '10002145', 'title' => 'Souvenirs - Collectible Figurines'],
            'statue'             => ['code' => '10002145', 'title' => 'Souvenirs - Sculptures/Statues'],
            'coin'               => ['code' => '10002145', 'title' => 'Souvenirs - Commemorative Coins/Medallions'],
            'mirror'             => ['code' => '10002145', 'title' => 'Souvenirs - Compact Mirrors'],
            'bell'               => ['code' => '10002145', 'title' => 'Souvenirs - Commemorative Bells'],
            'globe'              => ['code' => '10002145', 'title' => 'Souvenirs - Desk Globes/Ornaments'],
            'crismas-ornament'   => ['code' => '10002145', 'title' => 'Souvenirs - Christmas Ornaments'],
            'viking-merchandise' => ['code' => '10002145', 'title' => 'Souvenirs - Viking Heritage Merchandise'],
            'banana-product'     => ['code' => '10002145', 'title' => 'Souvenirs - Novelty Gifts'],
            'display'            => ['code' => '10002145', 'title' => 'Retail - Merchandise Displays'],
            'keychain'           => ['code' => '10002148', 'title' => 'Accessories - Keychains/Key Rings'],
            'lighter'            => ['code' => '10002148', 'title' => 'Accessories - Pocket Lighters'],
            'pill-box'           => ['code' => '10002148', 'title' => 'Accessories - Pocket Pill Boxes'],
            'umbrella'           => ['code' => '10002154', 'title' => 'Accessories - Rain Umbrellas'],
            'pen'                => ['code' => '10001874', 'title' => 'Stationery - Writing Pens'],
            'pencil'             => ['code' => '10001874', 'title' => 'Stationery - Wooden Pencils'],
            'clip'               => ['code' => '10001874', 'title' => 'Stationery - Paper Clips/Binders'],
            'notebook'           => ['code' => '10001874', 'title' => 'Stationery - Paper Notebooks/Journals'],
            'sticker'            => ['code' => '10001874', 'title' => 'Stationery - Adhesive Stickers'],
            'bookmark'           => ['code' => '10001874', 'title' => 'Stationery - Reading Bookmarks'],
            'toy'                => ['code' => '10000780', 'title' => 'Toys/Games - General Toys'],
            'duck'               => ['code' => '10000780', 'title' => 'Toys/Games - Bath Ducks/Novelties'],
            'professonal-duck'   => ['code' => '10000780', 'title' => 'Toys/Games - Collectible Ducks'],
            'playing-card'       => ['code' => '10000780', 'title' => 'Toys/Games - Playing Cards'],
            'music-box'          => ['code' => '10000780', 'title' => 'Toys/Games - Musical Boxes'],
        ];

        foreach ($gpcMappings as $slug => $data) {
            Category::where('slug', $slug)->update([
                'gpc_code' => $data['code'],
                'gpc_title' => $data['title'],
            ]);
        }

        // Auto-classify all remaining categories using Gs1TaxonomyResolver
        $resolver = app(\App\Services\Barcode\Gs1TaxonomyResolver::class);
        $remaining = Category::whereNull('gpc_code')
            ->orWhere('gpc_code', '')
            ->orWhere('gpc_code', '10000000')
            ->get();

        foreach ($remaining as $cat) {
            $resolved = $resolver->resolve($cat->name);
            $cat->update([
                'gpc_code' => $resolved['code'],
                'gpc_title' => $resolved['title'],
            ]);
        }
    }
}
