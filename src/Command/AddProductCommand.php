<?php


namespace App\Command;


use App\Entity\Product;
use App\Manager\ProductManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddProductCommand extends Command
{
    protected static $defaultName = 'app:add-product';

    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ProductManager
     */
    private $productManager;

    public function __construct(EntityManagerInterface $entityManager, ProductManager $productManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }

    protected function configure()
    {
        $this->setDescription('Add a new product')
            ->setHelp('This command allows you to create a new product')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the product')
            ->addArgument('reference', InputArgument::REQUIRED, 'Reference number')
            ->addArgument('price', InputArgument::OPTIONAL, 'Price')
            ->addArgument('description', InputArgument::OPTIONAL, 'Description of the product');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Add a new product to the portfolio');

        $name = $this->io->ask('What is the name of the product ?');
        $input->setArgument('name', $name);

        $reference = $this->io->ask('Reference of the product');
        $input->setArgument('reference', $reference);

        $price = $this->io->ask('Price of the product');
        $input->setArgument('price', $price);

        $description = $this->io->ask('Describe the product');
        $input->setArgument('description', $description);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Product name: '.$input->getArgument('name'));
        $output->writeln('Reference: '.$input->getArgument('reference'));
        $output->writeln('Price: '.$input->getArgument('price'));
        $output->writeln('Description: '.$input->getArgument('description'));

        $product = new Product();
        $product->setName($input->getArgument('name'))
                ->setReference($input->getArgument('reference'))
                ->setPrice($input->getArgument('price'))
                ->setDescription($input->getArgument('description'));

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->productManager->deleteCache();

        $this->io->success('New product added');
    }
}