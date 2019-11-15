<?php


namespace App\Command;


use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AddClientCommand extends Command
{
    protected static $defaultName = 'app:add-client';
    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Create a new client')
            ->setHelp('This command allows you to create a new user')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the client')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the client');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Create a new client');
        $email = $this->io->ask('What is the email of the client ?');
        $input->setArgument('email', $email);

        $password = $this->io->askHidden('Write the password for the client');
        $input->setArgument('password', $password);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Client email: '.$input->getArgument('email'));
        $output->writeln('Password: '.$input->getArgument('password'));

        $client = new Client();
        $client->setEmail($input->getArgument('email'));
        $client->setPassword($this->passwordEncoder->encodePassword($client, $input->getArgument('password')));

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->io->success('New client created');
    }
}