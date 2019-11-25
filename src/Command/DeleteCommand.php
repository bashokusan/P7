<?php


namespace App\Command;


use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteCommand extends Command
{
    protected static $defaultName = 'app:delete-client';
    /**
     * @var ClientRepository
     */
    private $clientRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $entityManager, ClientRepository $clientRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
    }

    protected function configure()
    {
        $this->setDescription('Deletes clients from the database')
            ->setHelp('This command allows you to delete a client from the database')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the client');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Delete a client');
        $email = $this->io->ask('What is the email of the client to delete ?');
        $input->setArgument('email', $email);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $client = $this->clientRepository->findOneBy(['email' => $email]);

        if(null === $client){
            throw new RuntimeException(sprintf('Client with email "%s" not found', $email));
        }

        $this->entityManager->remove($client);
        $this->entityManager->flush();

        $this->io->success(sprintf('Client with email "%s" was successfully deleted', $client->getEmail()));
    }
}