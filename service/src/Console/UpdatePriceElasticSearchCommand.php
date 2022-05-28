<?php
namespace App\Console;


use App\Client\ItemsccService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePriceElasticSearchCommand extends Command
{


    /**
     * @var ItemsccService
     */
    public $itemscc;

    protected function configure()
    {
        $this
            ->setName('user:UpdatePrice')
            ->setDescription('Create and Update Price');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {



        $i = 0;
        $client = $this->getItemsccClient();
//        do {
//
//            $array = $client->getItemsOffset(++$i);
//
//
//        } while (!empty($array));


        $output->writeln(
            'hello world'
        );
        return 1;
    }

    public function setItemsccClient(ItemsccService $itemscc)
    {
        $this->itemscc = $itemscc;
    }

    public function getItemsccClient(): ItemsccService
    {
        return $this->itemscc;
    }
}