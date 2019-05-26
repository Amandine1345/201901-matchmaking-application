<?php

namespace App\Command;

use App\Service\UserCreate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    // name of the command
    protected static $defaultName = 'app:create-user';

    /**
     * @var UserCreate
     */
    private $userCreate;

    /**
     * CreateUserCommand constructor.
     * @param UserCreate $userCreate
     * @param string|null $name
     */
    public function __construct(UserCreate $userCreate, string $name = null)
    {
        parent::__construct($name);
        $this->userCreate = $userCreate;
    }

    public function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Your email')
            ->addArgument('password', InputArgument::REQUIRED, 'Your password')
            ->addArgument('society', InputArgument::REQUIRED, 'Your society')
            // the short description shown while running "php bin/console list"
            ->setDescription('Create a new user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'User Creator',
            '======',
        ]);

        $output->writeln('Society: ' . $input->getArgument('society'));
        $output->writeln('Email: ' . $input->getArgument('email'));
        $output->writeln('Password: ' . $input->getArgument('password'));

        $this->userCreate->create(
            $input->getArgument('email'),
            $input->getArgument('password'),
            $input->getArgument('society')
        );

        $output->writeln([
            'User created with success',
            ''
        ]);
    }
}
