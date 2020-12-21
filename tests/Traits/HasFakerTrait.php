<?php


namespace App\Tests\Traits;


use Faker\Factory;
use Faker\Generator;

trait HasFakerTrait
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @return Generator
     */
    protected function getFaker()
    {
        if (!$this->faker) {
            $this->faker = Factory::create();
        }
        return $this->faker;
    }
}
