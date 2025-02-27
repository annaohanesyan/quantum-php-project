<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.6.0
 */

namespace Shared\Commands;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Bluemmb\Faker\PicsumPhotosProvider;
use Quantum\Console\QtCommand;
use Faker\Factory;


/**
 * Class PostCreateCommand
 * @package Shared\Commands
 */
class DemoCommand extends QtCommand
{

    /**
     * Command name
     * @var string
     */
    protected $name = 'install:demo';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Generates demo users and posts';

    /**
     * Command help text
     * @var string
     */
    protected $help = 'The command will create 2 new files (users.php and posts.php) and will generate records';

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * How many posts to create
     */
    const POST_COUNT = 12;

    /**
     * Default password for generated users
     */
    const DEFAULT_PASSWORD = 'password';

    /**
     * Command name of create user
     */
    const COMMAND_USER_CREATE = 'user:create';

    /**
     * Command name of create post
     */
    const COMMAND_POST_CREATE = 'post:create';

    /**
     * Command constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
        $this->faker->addProvider(new PicsumPhotosProvider($this->faker));
    }

    /**
     * Executes the command
     * @throws \Quantum\Exceptions\DiException
     */
    public function exec()
    {
        $adminArguments = $this->newUser('admin');
        $guestArguments = $this->newUser();

        $this->runCommand(self::COMMAND_USER_CREATE, $adminArguments);
        $this->runCommand(self::COMMAND_USER_CREATE, $guestArguments);

        for ($i = 1; $i <= self::POST_COUNT; $i++) {
            $postArguments = [
                'title' => str_replace(['"', '\'', '-'], '', $this->faker->realText(50)),
                'description' => str_replace(['"', '\'', '-'], '', $this->faker->realText(1000)),
                'image' => $this->faker->imageUrl(640, 480, true, 0),
                'author' => $adminArguments['email'],
            ];

            $this->runCommand(self::COMMAND_POST_CREATE, $postArguments);
        }

        $this->info('Demo installed successfully');

    }

    /**
     * Runs the external command
     * @throws \Exception
     */
    protected function runCommand($commandName, $arguments)
    {
        $command = $this->getApplication()->find($commandName);
        $command->run(new ArrayInput($arguments), new NullOutput);
    }

    /**
     * Creates new user
     * @param string $role
     * @return array
     */
    private function newUser(string $role = ''): array
    {
        return [
            'firstname' => $this->faker->name(),
            'lastname' => $this->faker->lastName(),
            'role' => $role,
            'email' => $this->faker->email(),
            'password' => self::DEFAULT_PASSWORD,
        ];
    }


}
