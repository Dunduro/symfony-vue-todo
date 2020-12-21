<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\TaskList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Faker\Generator;
use Faker\Factory;

class CreateListCommand extends Command
{
    protected static $defaultName = 'app:create-list';

    private $container;

    private $faker;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->faker = Factory::create();
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('Create a task list');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager();
        $list = new TaskList($this->faker->words(3, true), $this->faker->numberBetween(0, 100));
        $em->persist($list);
        for ($i = 0; $i < 5; $i++) {
            $task = new Task($list, $this->faker->words(5, true), $this->faker->numberBetween(0, 100));
            $em->persist($task);
        }
        $em->flush();
        return Command::SUCCESS;
    }
}
