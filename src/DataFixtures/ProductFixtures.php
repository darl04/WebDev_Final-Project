<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Products;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $productsData = [
            ['name' => 'Pig Onesie', 'price' => '600', 'description' => 'A cute pig onesie for babies.', 'collectionType' => 'Onesie', 'image' => 'public\uploads\products\Gemini_Generated_Image_rjaabarjaabarjaa.png'],
            ['name' => 'Dinosaur', 'price' => '800', 'description' => 'A cute dinosaur Costume .', 'collectionType' => 'Inflatables', 'image' => 'public\uploads\products\Gemini_Generated_Image_qiknz2qiknz2qikn.png'],
            ['name' => 'Prince Costume', 'price' => '2999', 'description' => 'Elegant and Neat.', 'collectionType' => 'Seasonal', 'image' => 'public\uploads\products\Gemini-Generated-Image-rhpl5grhpl5grhpl-6a0e580ceed40-6a17294b7e726.png'],
        ];

        foreach ($productsData as $p) {
            $product = new Products();
            $product->setName($p['name']);
            $product->setPrice($p['price']);
            $product->setDescription($p['description']);
            $product->setCollectionType($p['collectionType']);
            $product->setImage($p['image']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
