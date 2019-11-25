<?php


namespace App\Command;


use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClientsListCommand extends Command
{
    protected static $defaultName = 'app:clients-list';
    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        parent::__construct();
        $this->clientRepository = $clientRepository;
    }

    protected function configure()
    {
        $this->setDescription('Lists all the clients')
            ->setHelp('This command allows you to see the list of all the clients');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clients = $this->clientRepository->findAll();
        $clientsAsArray = array_map(function (Client $client){
            return [
                $client->getId(),
                $client->getEmail()
            ];
        }, $clients);
        $bufferedOutput = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $bufferedOutput);
        $this->io->table([
            ['ID', 'Email'],
        ], $clientsAsArray);

        $clientsAsTable = $bufferedOutput->fetch();
        $output->writeln($clientsAsTable);
    }
}