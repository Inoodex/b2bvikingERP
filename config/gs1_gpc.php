<?php

declare(strict_types=1);

/**
 * Official GS1 Global Product Classification (GPC) Master Taxonomy Dictionary.
 * Compliant with GS1 Standard: https://gpc-browser.gs1.org/
 * 
 * Provides comprehensive 8-digit Brick Codes, Segments, Families, Classes, and Synonyms
 * for dynamic, zero-maintenance auto-classification across Viking ERP.
 */
return [
    'default_fallback' => [
        'code' => '10000000',
        'title' => 'General Merchandise / Unclassified Goods',
        'segment' => 'General Merchandise',
        'family' => 'General',
        'class' => 'General Goods',
    ],

    'bricks' => [
        // ==========================================
        // 1. SEGMENT 67000000 - CLOTHING & TEXTILES
        // ==========================================
        [
            'code' => '10001363',
            'title' => 'Clothing - Tops/Shirts/T-Shirts/Blouses',
            'segment' => 'Clothing',
            'family' => 'Outerwear',
            'class' => 'Tops',
            'keywords' => [
                'cloth', 'cloths', 'clothing', 'apparel', 'shirt', 'shirts', 't-shirt', 't-shirts',
                'tshirt', 'tshirts', 'top', 'tops', 'polo', 'polo shirt', 'jersey', 'tank top',
                'tee', 'tees', 'blouse', 'tunic', 'cotton', 'hygge cotton', 'viking wear', 'garment',
                'casual shirt', 'formal shirt', 'dress shirt', 'flannel', 'sleeveless'
            ],
        ],
        [
            'code' => '10001360',
            'title' => 'Clothing - Sweatshirts/Hoodies/Sweaters/Pullovers',
            'segment' => 'Clothing',
            'family' => 'Outerwear',
            'class' => 'Sweaters & Hoodies',
            'keywords' => [
                'hoodie', 'hoodies', 'sweatshirt', 'sweatshirts', 'sweater', 'sweaters', 'pullover',
                'pullovers', 'cardigan', 'cardigans', 'jumper', 'jumpers', 'fleece', 'wool sweater',
                'knitwear', 'nordic sweater', 'zip hoodie', 'hoody'
            ],
        ],
        [
            'code' => '10001365',
            'title' => 'Clothing - Jackets/Coats/Outerwear/Vests',
            'segment' => 'Clothing',
            'family' => 'Outerwear',
            'class' => 'Jackets & Coats',
            'keywords' => [
                'jacket', 'jackets', 'coat', 'coats', 'parka', 'parkas', 'anorak', 'windbreaker',
                'raincoat', 'vest', 'vests', 'waistcoat', 'poncho', 'winter jacket', 'outdoor jacket',
                'bomber', 'blazer', 'down jacket', 'trench coat'
            ],
        ],
        [
            'code' => '10001364',
            'title' => 'Clothing - Bottoms/Trousers/Pants/Jeans/Shorts',
            'segment' => 'Clothing',
            'family' => 'Bottoms',
            'class' => 'Pants & Shorts',
            'keywords' => [
                'pant', 'pants', 'trouser', 'trousers', 'jean', 'jeans', 'short', 'shorts',
                'chino', 'chinos', 'cargo', 'cargo pants', 'legging', 'leggings', 'sweatpants',
                'jogger', 'joggers', 'slacks', 'bermuda'
            ],
        ],
        [
            'code' => '10001357',
            'title' => 'Clothing - Skirts',
            'segment' => 'Clothing',
            'family' => 'Bottoms',
            'class' => 'Skirts',
            'keywords' => [
                'skirt', 'skirts', 'miniskirt', 'midi skirt', 'maxi skirt', 'pleated skirt', 'kilt'
            ],
        ],
        [
            'code' => '10001359',
            'title' => 'Clothing - Dresses & Gowns',
            'segment' => 'Clothing',
            'family' => 'One-Piece',
            'class' => 'Dresses',
            'keywords' => [
                'dress', 'dresses', 'gown', 'gowns', 'frock', 'sundress', 'evening dress', 'robe dress'
            ],
        ],
        [
            'code' => '10001361',
            'title' => 'Clothing - Suits & Formal Sets',
            'segment' => 'Clothing',
            'family' => 'Outerwear',
            'class' => 'Suits',
            'keywords' => [
                'suit', 'suits', 'tuxedo', 'tailored suit', 'two piece', 'blazer set'
            ],
        ],
        [
            'code' => '10001362',
            'title' => 'Clothing - Underwear & Undergarments',
            'segment' => 'Clothing',
            'family' => 'Underwear',
            'class' => 'Undergarments',
            'keywords' => [
                'underwear', 'undergarment', 'boxer', 'boxers', 'brief', 'briefs', 'panties',
                'bra', 'bras', 'undershirt', 'thermal underwear', 'long johns', 'lingerie'
            ],
        ],
        [
            'code' => '10001366',
            'title' => 'Clothing - Sleepwear & Loungewear',
            'segment' => 'Clothing',
            'family' => 'Sleepwear',
            'class' => 'Pajamas & Robes',
            'keywords' => [
                'sleepwear', 'pajama', 'pajamas', 'pyjama', 'pyjamas', 'nightgown', 'bathrobe',
                'dressing gown', 'lounge wear', 'loungewear'
            ],
        ],
        [
            'code' => '10001367',
            'title' => 'Clothing - Swimwear',
            'segment' => 'Clothing',
            'family' => 'Swimwear',
            'class' => 'Swimwear',
            'keywords' => [
                'swimwear', 'swimsuit', 'swimming trunks', 'bikini', 'boardshorts', 'rash guard'
            ],
        ],
        [
            'code' => '10001368',
            'title' => 'Clothing - Sportswear & Athletic Wear',
            'segment' => 'Clothing',
            'family' => 'Activewear',
            'class' => 'Sportswear',
            'keywords' => [
                'sportswear', 'activewear', 'gym wear', 'tracksuit', 'athletic shorts', 'workout shirt',
                'compression pants', 'sports jersey'
            ],
        ],
        [
            'code' => '10001358',
            'title' => 'Clothing - Headwear/Hats/Caps/Beanies',
            'segment' => 'Clothing',
            'family' => 'Accessories',
            'class' => 'Headwear',
            'keywords' => [
                'cap', 'caps', 'hat', 'hats', 'beanie', 'beanies', 'beret', 'headband', 'visor',
                'baseball cap', 'snapback', 'viking helmet', 'headwear', 'bonnet', 'fedora', 'toque'
            ],
        ],
        [
            'code' => '10001370',
            'title' => 'Clothing - Hosiery/Socks/Stockings',
            'segment' => 'Clothing',
            'family' => 'Accessories',
            'class' => 'Hosiery',
            'keywords' => [
                'sock', 'socks', 'stocking', 'stockings', 'tights', 'ankle socks', 'wool socks',
                'hosiery', 'booties', 'crew socks', 'compression socks'
            ],
        ],
        [
            'code' => '10001340',
            'title' => 'Clothing - Handwear/Gloves/Mittens',
            'segment' => 'Clothing',
            'family' => 'Accessories',
            'class' => 'Handwear',
            'keywords' => [
                'glove', 'gloves', 'mitten', 'mittens', 'grill gloves', 'winter gloves', 'handwear',
                'leather gloves', 'knit gloves', 'fingerless gloves'
            ],
        ],
        [
            'code' => '10001369',
            'title' => 'Clothing - Neckwear/Scarves/Bandanas/Ties',
            'segment' => 'Clothing',
            'family' => 'Accessories',
            'class' => 'Neckwear',
            'keywords' => [
                'scarf', 'scarves', 'bandana', 'bandanas', 'neck warmer', 'shawl', 'tie', 'necktie',
                'bow tie', 'muffler', 'cravat'
            ],
        ],
        [
            'code' => '10001341',
            'title' => 'Clothing - Belts & Suspenders',
            'segment' => 'Clothing',
            'family' => 'Accessories',
            'class' => 'Belts',
            'keywords' => [
                'belt', 'belts', 'leather belt', 'waist belt', 'suspenders', 'braces', 'buckle'
            ],
        ],
        [
            'code' => '10001342',
            'title' => 'Clothing - Costumes & Traditional Viking Wear',
            'segment' => 'Clothing',
            'family' => 'Costumes',
            'class' => 'Traditional & Costumes',
            'keywords' => [
                'costume', 'costumes', 'viking tunic', 'cloak', 'medieval', 'larp', 'traditional costume',
                'warrior costume'
            ],
        ],
        [
            'code' => '10001343',
            'title' => 'Clothing - Baby & Infant Wear',
            'segment' => 'Clothing',
            'family' => 'Baby Clothing',
            'class' => 'Infant',
            'keywords' => [
                'baby', 'infant', 'onesie', 'romper', 'babygrow', 'bib', 'bibs', 'toddler'
            ],
        ],

        // ==========================================
        // 2. SEGMENT 63000000 - FOOTWEAR
        // ==========================================
        [
            'code' => '10001344',
            'title' => 'Footwear - Shoes/Boots/Slippers/Loafers',
            'segment' => 'Footwear',
            'family' => 'Footwear',
            'class' => 'Shoes & Boots',
            'keywords' => [
                'shoe', 'shoes', 'boot', 'boots', 'slipper', 'slippers', 'sandal', 'sandals',
                'footwear', 'loafer', 'loafers', 'moccasin', 'leather boot', 'nordic boots'
            ],
        ],
        [
            'code' => '10001070',
            'title' => 'Footwear - Athletic/Sneakers/Sports Shoes',
            'segment' => 'Footwear',
            'family' => 'Athletic Footwear',
            'class' => 'Sneakers',
            'keywords' => [
                'sneaker', 'sneakers', 'trainer', 'trainers', 'running shoe', 'running shoes',
                'tennis shoe', 'athletic shoe', 'gym shoe'
            ],
        ],
        [
            'code' => '10001345',
            'title' => 'Footwear - Sandals/Slides/Flip-Flops',
            'segment' => 'Footwear',
            'family' => 'Casual Footwear',
            'class' => 'Open Footwear',
            'keywords' => [
                'sandal', 'sandals', 'flip-flop', 'flip flop', 'slide', 'slides', 'clog', 'clogs'
            ],
        ],
        [
            'code' => '10001346',
            'title' => 'Footwear - Safety Boots & Work Shoes',
            'segment' => 'Footwear',
            'family' => 'Safety Footwear',
            'class' => 'Protective Shoes',
            'keywords' => [
                'safety shoe', 'safety boot', 'steel toe', 'work boot', 'work boots', 'waterproof boot'
            ],
        ],

        // ==========================================
        // 3. SEGMENT 64000000 - PERSONAL ACCESSORIES, BAGS & JEWELRY
        // ==========================================
        [
            'code' => '10002130',
            'title' => 'Bags & Luggage - Handbags/Backpacks/Tote Bags',
            'segment' => 'Personal Accessories',
            'family' => 'Luggage',
            'class' => 'Bags',
            'keywords' => [
                'bag', 'bags', 'tote bag', 'tote-bag', 'totebag', 'canvas bag', 'shopping bag',
                'backpack', 'backpacks', 'rucksack', 'shoulder bag', 'crossbody', 'handbag',
                'carrier bag', 'duffle bag', 'gym bag', 'pouch', 'messenger bag'
            ],
        ],
        [
            'code' => '10002131',
            'title' => 'Bags & Luggage - Suitcases/Trolleys/Travel Bags',
            'segment' => 'Personal Accessories',
            'family' => 'Luggage',
            'class' => 'Suitcases',
            'keywords' => [
                'suitcase', 'suitcases', 'trolley', 'luggage', 'travel bag', 'carry-on', 'spinner'
            ],
        ],
        [
            'code' => '10002132',
            'title' => 'Personal Accessories - Purses/Wallets/Card Holders',
            'segment' => 'Personal Accessories',
            'family' => 'Small Leather Goods',
            'class' => 'Wallets',
            'keywords' => [
                'purse', 'purses', 'wallet', 'wallets', 'coin purse', 'money clip', 'card holder',
                'billfold', 'clutch', 'passport holder'
            ],
        ],
        [
            'code' => '10002134',
            'title' => 'Personal Accessories - Luggage Tags',
            'segment' => 'Personal Accessories',
            'family' => 'Travel Accessories',
            'class' => 'Luggage Tags',
            'keywords' => [
                'luggage tag', 'luggage-tag', 'bag tag', 'suitcase tag', 'travel tag', 'id tag'
            ],
        ],
        [
            'code' => '10002148',
            'title' => 'Personal Accessories - Keychains/Key Rings/Lanyards/Lighters',
            'segment' => 'Personal Accessories',
            'family' => 'Small Accessories',
            'class' => 'Keyrings',
            'keywords' => [
                'keychain', 'keychains', 'keyring', 'key ring', 'key-chain', 'key-ring', 'lanyard',
                'lighter', 'lighters', 'pill box', 'pill-box', 'bottle opener keychain', 'fob'
            ],
        ],
        [
            'code' => '10002154',
            'title' => 'Personal Accessories - Rain Umbrellas/Parasols',
            'segment' => 'Personal Accessories',
            'family' => 'Weather Protection',
            'class' => 'Umbrellas',
            'keywords' => [
                'umbrella', 'umbrellas', 'rain umbrella', 'parasol', 'brolly', 'storm umbrella'
            ],
        ],
        [
            'code' => '10002160',
            'title' => 'Personal Accessories - Jewelry/Ornaments/Pendants',
            'segment' => 'Personal Accessories',
            'family' => 'Jewelry',
            'class' => 'Fashion Jewelry',
            'keywords' => [
                'jewelry', 'jewellery', 'necklace', 'pendant', 'bracelet', 'ring', 'earring',
                'earrings', 'amber jewelry', 'viking ring', 'amulet', 'thor hammer', 'mjolnir',
                'choker', 'brooch', 'bangle'
            ],
        ],
        [
            'code' => '10002165',
            'title' => 'Personal Accessories - Watches & Wristwatches',
            'segment' => 'Personal Accessories',
            'family' => 'Timepieces',
            'class' => 'Watches',
            'keywords' => [
                'watch', 'watches', 'wristwatch', 'wrist watch', 'chronograph', 'pocket watch'
            ],
        ],
        [
            'code' => '10002170',
            'title' => 'Personal Accessories - Sunglasses & Eyewear',
            'segment' => 'Personal Accessories',
            'family' => 'Eyewear',
            'class' => 'Sunglasses',
            'keywords' => [
                'sunglass', 'sunglasses', 'shades', 'eyewear', 'reading glasses', 'spectacles'
            ],
        ],

        // ==========================================
        // 4. SEGMENT 65000000 - KITCHENWARE & TABLEWARE
        // ==========================================
        [
            'code' => '10001452',
            'title' => 'Tableware - Mugs/Cups/Drinkware',
            'segment' => 'Kitchenware',
            'family' => 'Tableware',
            'class' => 'Drinkware',
            'keywords' => [
                'mug', 'mugs', 'cup', 'cups', 'coffee mug', 'tea cup', 'espresso cup', 'ceramic mug',
                'souvenir mug', 'enamel mug', 'drinking cup', 'travel mug'
            ],
        ],
        [
            'code' => '10001455',
            'title' => 'Tableware - Bottles/Flasks/Thermoses/Horns',
            'segment' => 'Kitchenware',
            'family' => 'Drinkware',
            'class' => 'Bottles',
            'keywords' => [
                'bottle', 'bottles', 'water bottle', 'flask', 'thermos', 'vacuum bottle',
                'drinking horn', 'viking horn', 'tumbler', 'canteen', 'horn with stand'
            ],
        ],
        [
            'code' => '10001450',
            'title' => 'Tableware - Glassware/Shot Glasses/Cutlery/Table Tools',
            'segment' => 'Kitchenware',
            'family' => 'Tableware',
            'class' => 'Glassware & Cutlery',
            'keywords' => [
                'small glass', 'small-glass', 'shot glass', 'shot glasses', 'glass', 'glasses',
                'wine glass', 'beer glass', 'plate', 'plates', 'dish', 'dishes', 'spoon', 'spoons',
                'fork', 'forks', 'knife', 'knives', 'cutlery', 'coaster', 'coasters',
                'ceramic coaster', 'opener', 'bottle opener', 'corkscrew', 'cheese slicer'
            ],
        ],
        [
            'code' => '10001456',
            'title' => 'Kitchenware - Pots/Pans/Cookware/Baking',
            'segment' => 'Kitchenware',
            'family' => 'Cookware',
            'class' => 'Pots & Pans',
            'keywords' => [
                'pot', 'pots', 'pan', 'pans', 'frying pan', 'saucepan', 'wok', 'skillet',
                'cookware', 'baking dish', 'casserole'
            ],
        ],
        [
            'code' => '10001458',
            'title' => 'Kitchenware - Utensils/Cutting Boards/Tools',
            'segment' => 'Kitchenware',
            'family' => 'Kitchen Tools',
            'class' => 'Utensils',
            'keywords' => [
                'utensil', 'utensils', 'spatula', 'cutting board', 'chopping board', 'whisk',
                'ladle', 'tongs', 'peeler', 'grater', 'kitchen knife'
            ],
        ],
        [
            'code' => '10001460',
            'title' => 'Kitchenware - Food Storage Containers/Lunchboxes',
            'segment' => 'Kitchenware',
            'family' => 'Storage',
            'class' => 'Food Containers',
            'keywords' => [
                'food container', 'lunchbox', 'lunch box', 'bento box', 'tupperware', 'storage jar',
                'canister', 'mason jar'
            ],
        ],

        // ==========================================
        // 5. SEGMENT 72000000 - HOME DECOR, SOUVENIRS & LIVING
        // ==========================================
        [
            'code' => '10002145',
            'title' => 'Decorative Souvenirs - Magnets/Pins/Badges/Collectibles',
            'segment' => 'Home & Living',
            'family' => 'Decorative Souvenirs',
            'class' => 'Souvenirs',
            'keywords' => [
                'magnet', 'magnets', 'fridge magnet', 'pin', 'pins', 'lapel pin', 'badge', 'badges',
                'patches', 'patch', 'souvenir', 'souvenirs', 'viking merchandise', 'figurine',
                'figurines', 'statue', 'statues', 'sculpture', 'snowball', 'snow globe',
                'shield', 'viking shield', 'sword', 'replica sword', 'helmet replica', 'coin',
                'coins', 'medal', 'medallion', 'bell', 'bells', 'mirror', 'compact mirror'
            ],
        ],
        [
            'code' => '10002146',
            'title' => 'Home Decor - Picture Frames & Photo Displays',
            'segment' => 'Home & Living',
            'family' => 'Home Decor',
            'class' => 'Frames',
            'keywords' => [
                'photo frame', 'photo-frame', 'picture frame', 'frame', 'frames', 'photo album'
            ],
        ],
        [
            'code' => '10002147',
            'title' => 'Home Fragrance & Living - Candles & Candleholders',
            'segment' => 'Home & Living',
            'family' => 'Home Fragrance',
            'class' => 'Candles',
            'keywords' => [
                'candle', 'candles', 'scented candle', 'tealight', 'candle holder', 'candlestick',
                'incense', 'diffuser'
            ],
        ],
        [
            'code' => '10001470',
            'title' => 'Home Textiles - Blankets/Throws/Cushions/Towels',
            'segment' => 'Home & Living',
            'family' => 'Home Textiles',
            'class' => 'Bedding & Towels',
            'keywords' => [
                'blanket', 'blankets', 'throw', 'cushion', 'cushions', 'pillow', 'pillows',
                'towel', 'towels', 'bath towel', 'tea towel', 'bedspread', 'fleece throw'
            ],
        ],
        [
            'code' => '10002149',
            'title' => 'Wall Decor - Posters/Paintings/Wall Art',
            'segment' => 'Home & Living',
            'family' => 'Wall Decor',
            'class' => 'Art & Posters',
            'keywords' => [
                'poster', 'posters', 'wall art', 'print', 'prints', 'canvas art', 'plaque', 'metal sign',
                'tapestry', 'banner'
            ],
        ],
        [
            'code' => '10002150',
            'title' => 'Home Timepieces - Clocks/Wall Clocks/Alarm Clocks',
            'segment' => 'Home & Living',
            'family' => 'Clocks',
            'class' => 'Wall Clocks',
            'keywords' => [
                'clock', 'clocks', 'wall clock', 'desk clock', 'alarm clock', 'cuckoo clock'
            ],
        ],
        [
            'code' => '10002152',
            'title' => 'Holiday & Festive Decor - Christmas Ornaments/Festive Gifts',
            'segment' => 'Home & Living',
            'family' => 'Festive Decor',
            'class' => 'Holiday Ornaments',
            'keywords' => [
                'christmas ornament', 'crismas ornament', 'ornament', 'ornaments', 'holiday decor',
                'tree bauble', 'baubles', 'wreath', 'festive decor', 'yule ornament'
            ],
        ],

        // ==========================================
        // 6. SEGMENT 61000000 - TOYS, GAMES & NOVELTIES
        // ==========================================
        [
            'code' => '10000780',
            'title' => 'Toys & Novelties - Rubber Ducks/Miniatures/Novelties',
            'segment' => 'Toys & Games',
            'family' => 'Novelties',
            'class' => 'Novelty Toys',
            'keywords' => [
                'toy', 'toys', 'duck', 'ducks', 'professonal duck', 'rubber duck', 'bath duck',
                'novelty', 'novelties', 'action figure', 'figurine toy', 'miniature'
            ],
        ],
        [
            'code' => '10000782',
            'title' => 'Games - Playing Cards/Board Games/Puzzles',
            'segment' => 'Toys & Games',
            'family' => 'Games',
            'class' => 'Board Games & Cards',
            'keywords' => [
                'playing card', 'playing cards', 'card game', 'deck', 'board game', 'puzzle',
                'jigsaw puzzle', 'hnefatafl', 'viking chess', 'dice', 'tabletop game'
            ],
        ],
        [
            'code' => '10000785',
            'title' => 'Toys - Plush & Stuffed Animals',
            'segment' => 'Toys & Games',
            'family' => 'Plush Toys',
            'class' => 'Stuffed Toys',
            'keywords' => [
                'plush', 'plush toy', 'stuffed animal', 'teddy bear', 'plush duck', 'soft toy', 'doll'
            ],
        ],
        [
            'code' => '10000790',
            'title' => 'Novelties - Music Boxes & Mechanical Novelties',
            'segment' => 'Toys & Games',
            'family' => 'Novelties',
            'class' => 'Music Boxes',
            'keywords' => [
                'music box', 'music-box', 'wind-up toy', 'wind up toy', 'mechanical toy'
            ],
        ],

        // ==========================================
        // 7. SEGMENT 68000000 - STATIONERY & OFFICE SUPPLIES
        // ==========================================
        [
            'code' => '10001874',
            'title' => 'Stationery - Pens/Pencils/Writing Instruments',
            'segment' => 'Stationery & Office',
            'family' => 'Writing Instruments',
            'class' => 'Pens & Pencils',
            'keywords' => [
                'pen', 'pens', 'pencil', 'pencils', 'ballpoint', 'fountain pen', 'gel pen',
                'marker', 'markers', 'highlighter', 'quill'
            ],
        ],
        [
            'code' => '10001875',
            'title' => 'Stationery - Notebooks/Journals/Notepads/Paper',
            'segment' => 'Stationery & Office',
            'family' => 'Paper Products',
            'class' => 'Notebooks',
            'keywords' => [
                'notebook', 'notebooks', 'journal', 'journals', 'leather journal', 'pad', 'notepad',
                'diary', 'sketchbook', 'memo pad'
            ],
        ],
        [
            'code' => '10001876',
            'title' => 'Stationery - Stickers/Postcards/Greeting Cards/Bookmarks',
            'segment' => 'Stationery & Office',
            'family' => 'Printed Stationery',
            'class' => 'Cards & Stickers',
            'keywords' => [
                'sticker', 'stickers', 'bookmark', 'bookmarks', 'postcard', 'postcards',
                'greeting card', 'envelope', 'labels'
            ],
        ],
        [
            'code' => '10001878',
            'title' => 'Stationery - Desk Accessories/Clips/Rulers',
            'segment' => 'Stationery & Office',
            'family' => 'Desk Supplies',
            'class' => 'Desk Accessories',
            'keywords' => [
                'clip', 'clips', 'paper clip', 'stapler', 'scissors', 'ruler', 'eraser',
                'pen holder', 'desk pad', 'tape dispenser'
            ],
        ],

        // ==========================================
        // 8. SEGMENT 50000000 - FOOD, BEVERAGE & TOBACCO
        // ==========================================
        [
            'code' => '10000160',
            'title' => 'Food & Confectionery - Chocolates/Candy/Sweets/Fudge',
            'segment' => 'Food & Beverage',
            'family' => 'Confectionery',
            'class' => 'Sweets & Chocolate',
            'keywords' => [
                'chocolate', 'chocolates', 'candy', 'candies', 'sweet', 'sweets', 'fudge',
                'licorice', 'salmiak', 'gummy', 'toffee', 'bonbon', 'caramel'
            ],
        ],
        [
            'code' => '10000162',
            'title' => 'Food & Bakery - Biscuits/Cookies/Wafers',
            'segment' => 'Food & Beverage',
            'family' => 'Bakery',
            'class' => 'Biscuits & Cookies',
            'keywords' => [
                'biscuit', 'biscuits', 'cookie', 'cookies', 'gingerbread', 'shortbread', 'wafer',
                'crackers', 'pastry'
            ],
        ],
        [
            'code' => '10000165',
            'title' => 'Food & Beverage - Coffee/Tea/Hot Drinks',
            'segment' => 'Food & Beverage',
            'family' => 'Hot Beverages',
            'class' => 'Tea & Coffee',
            'keywords' => [
                'coffee', 'tea', 'black tea', 'herbal tea', 'coffee beans', 'ground coffee',
                'hot chocolate', 'cocoa'
            ],
        ],
        [
            'code' => '10000168',
            'title' => 'Food - Jams/Honey/Spreads/Condiments',
            'segment' => 'Food & Beverage',
            'family' => 'Spreads & Condiments',
            'class' => 'Jams & Honey',
            'keywords' => [
                'honey', 'jam', 'marmalade', 'preserve', 'mustard', 'sauce', 'viking honey'
            ],
        ],
        [
            'code' => '10000170',
            'title' => 'Food - Snacks/Nuts/Jerky/Crisps',
            'segment' => 'Food & Beverage',
            'family' => 'Snacks',
            'class' => 'Savory Snacks',
            'keywords' => [
                'snack', 'snacks', 'chips', 'crisps', 'nuts', 'beef jerky', 'jerky', 'dried fruit'
            ],
        ],
        [
            'code' => '10000175',
            'title' => 'Beverages - Soft Drinks/Juices/Water',
            'segment' => 'Food & Beverage',
            'family' => 'Non-Alcoholic Beverages',
            'class' => 'Juices & Soda',
            'keywords' => [
                'juice', 'soda', 'mineral water', 'soft drink', 'lemonade', 'tonic'
            ],
        ],
        [
            'code' => '10000180',
            'title' => 'Beverages - Alcoholic/Beer/Mead/Cider/Spirits',
            'segment' => 'Food & Beverage',
            'family' => 'Alcoholic Beverages',
            'class' => 'Beer & Mead',
            'keywords' => [
                'beer', 'mead', 'viking mead', 'cider', 'wine', 'spirit', 'spirits', 'aquavit',
                'whiskey', 'vodka', 'gin'
            ],
        ],

        // ==========================================
        // 9. SEGMENT 51000000 - HEALTH, BEAUTY & PERSONAL CARE
        // ==========================================
        [
            'code' => '10000520',
            'title' => 'Health & Beauty - Soaps/Shower Gel/Body Wash',
            'segment' => 'Health & Beauty',
            'family' => 'Personal Care',
            'class' => 'Soaps & Wash',
            'keywords' => [
                'soap', 'soaps', 'bar soap', 'hand soap', 'liquid soap', 'shower gel', 'body wash',
                'bath bomb', 'handmade soap'
            ],
        ],
        [
            'code' => '10000522',
            'title' => 'Health & Beauty - Lotions/Creams/Balms/Moisturizers',
            'segment' => 'Health & Beauty',
            'family' => 'Personal Care',
            'class' => 'Skin Care',
            'keywords' => [
                'lotion', 'cream', 'balm', 'lip balm', 'hand cream', 'moisturizer', 'beard balm',
                'body butter', 'salve'
            ],
        ],
        [
            'code' => '10000524',
            'title' => 'Health & Beauty - Haircare & Beard Care',
            'segment' => 'Health & Beauty',
            'family' => 'Haircare',
            'class' => 'Hair & Beard',
            'keywords' => [
                'shampoo', 'conditioner', 'beard oil', 'beard wash', 'hair wax', 'pomade'
            ],
        ],
        [
            'code' => '10000526',
            'title' => 'Health & Beauty - Perfumes/Fragrances/Colognes',
            'segment' => 'Health & Beauty',
            'family' => 'Fragrances',
            'class' => 'Perfume & Cologne',
            'keywords' => [
                'perfume', 'fragrance', 'cologne', 'eau de toilette', 'body spray', 'aftershave'
            ],
        ],

        // ==========================================
        // 10. SEGMENT 84000000 / 86000000 - ELECTRONICS & TECH ACCESSORIES
        // ==========================================
        [
            'code' => '10001920',
            'title' => 'Electronics - Cables/Chargers/Power Banks',
            'segment' => 'Electronics',
            'family' => 'Electronic Accessories',
            'class' => 'Cables & Power',
            'keywords' => [
                'electronic', 'gadget', 'charger', 'cable', 'usb cable', 'power bank', 'adapter',
                'charging dock', 'usb hub'
            ],
        ],
        [
            'code' => '10001922',
            'title' => 'Electronics - Audio/Headphones/Speakers',
            'segment' => 'Electronics',
            'family' => 'Audio Equipment',
            'class' => 'Headphones & Speakers',
            'keywords' => [
                'headphone', 'headphones', 'earphone', 'earphones', 'earbuds', 'speaker',
                'bluetooth speaker', 'audio'
            ],
        ],
        [
            'code' => '10001924',
            'title' => 'Electronics - Phone Cases & Mobile Covers',
            'segment' => 'Electronics',
            'family' => 'Mobile Accessories',
            'class' => 'Cases & Protection',
            'keywords' => [
                'phone case', 'phone cover', 'iphone case', 'screen protector', 'phone stand', 'mobile ring'
            ],
        ],
        [
            'code' => '10001926',
            'title' => 'Electronics - Computing Peripherals & Storage',
            'segment' => 'Electronics',
            'family' => 'Computing',
            'class' => 'Peripherals',
            'keywords' => [
                'usb', 'flash drive', 'mouse', 'keyboard', 'mousepad', 'mouse pad', 'memory stick'
            ],
        ],

        // ==========================================
        // 11. SEGMENT 62000000 / 64000000 - SPORTS, OUTDOOR & CAMPING
        // ==========================================
        [
            'code' => '10002340',
            'title' => 'Sports & Outdoors - Camping/Hiking/Survival Gear',
            'segment' => 'Sports & Outdoors',
            'family' => 'Outdoor Equipment',
            'class' => 'Camping & Hiking',
            'keywords' => [
                'camping', 'tent', 'sleeping bag', 'flashlight', 'pocket knife', 'multitool',
                'compass', 'survival kit', 'hiking pole', 'outdoor'
            ],
        ],
        [
            'code' => '10002342',
            'title' => 'Sports & Outdoors - Fitness & Exercise Equipment',
            'segment' => 'Sports & Outdoors',
            'family' => 'Fitness',
            'class' => 'Exercise Gear',
            'keywords' => [
                'fitness', 'gym', 'yoga mat', 'jump rope', 'resistance band', 'weights', 'sports bottle'
            ],
        ],

        // ==========================================
        // 12. SEGMENT 70000000 - PET SUPPLIES
        // ==========================================
        [
            'code' => '10004010',
            'title' => 'Pet Supplies - Collars/Leashes/Pet Accessories',
            'segment' => 'Pet Care',
            'family' => 'Pet Accessories',
            'class' => 'Collars & Leashes',
            'keywords' => [
                'pet', 'dog collar', 'cat collar', 'leash', 'harness', 'pet bandana', 'pet toy', 'dog chew'
            ],
        ],

        // ==========================================
        // 13. SEGMENT 93000000 - PACKAGING & LOGISTICS MATERIALS (GS1 Standard)
        // ==========================================
        [
            'code' => '10002501',
            'title' => 'Packaging - Corrugated Boxes & Shipping Cartons',
            'segment' => 'Packaging Materials',
            'family' => 'Cartons & Boxes',
            'class' => 'Corrugated Boxes',
            'keywords' => [
                'box', 'boxes', 'carton', 'cartons', 'shipping box', 'outer box', 'master carton',
                'corrugated box', 'packing box'
            ],
        ],
        [
            'code' => '10002505',
            'title' => 'Packaging - Shipping Bags & Poly Mailers',
            'segment' => 'Packaging Materials',
            'family' => 'Flexible Packaging',
            'class' => 'Mailers & Bags',
            'keywords' => [
                'poly mailer', 'bubble mailer', 'shipping envelope', 'packing bag', 'mailer'
            ],
        ],
        [
            'code' => '10002510',
            'title' => 'Logistics Units - Transport Pallets & Crates',
            'segment' => 'Packaging Materials',
            'family' => 'Transport Units',
            'class' => 'Pallets & Crates',
            'keywords' => [
                'pallet', 'pallets', 'euro pallet', 'wooden pallet', 'plastic pallet', 'crate', 'crates'
            ],
        ],
        [
            'code' => '10002520',
            'title' => 'Packaging - Protective Void Fill & Bubble Wrap',
            'segment' => 'Packaging Materials',
            'family' => 'Protective Materials',
            'class' => 'Void Fill',
            'keywords' => [
                'bubble wrap', 'packing peanut', 'air cushion', 'dunnage', 'foam sheet', 'packing tape'
            ],
        ],
    ],
];
