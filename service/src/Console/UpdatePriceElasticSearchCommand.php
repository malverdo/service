<?php
namespace App\Console;


use App\Client\ItemsccService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class UpdatePriceElasticSearchCommand extends Command
{


    /**
     * @var ItemsccService
     */
    public $itemscc;
    /**
     * @var Filesystem
     */
    private $filesystem;



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
        $filesystem = $this->getFilesystem();
        if (!$filesystem->exists('tmp')) {

            while (true) {
                $prefixOffset =  $i * 3000;
                $items = $client->getItemsOffset($i++);
                if (empty($items)) {
                    break;
                } else {
                    $filesystem->dumpFile('./tmp/old/items_' . $prefixOffset . '_offset.txt', $items);
                }
            }

        }


        $i = 0;
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

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }
}