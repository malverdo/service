<?php
namespace App\Console;


use App\Builder\ElasticSearchBuilder;
use App\Client\ItemsccService;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
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
    /**
     * @var ElasticSearchBuilder
     */
    private $elasticSearchBuilder;


    protected function configure()
    {
        $this
            ->setName('user:UpdatePrice')
            ->setDescription('Create and Update Price');
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = 0;
        $client = $this->getItemsccClient();
        $filesystem = $this->getFilesystem();
        $elasticSearchBuilder = $this->getElasticSearchBuilder();

        $elasticSearchBuilder->createIndex('item');



        if (!$filesystem->exists('tmp')) {
            while (true) {
                $offset =  $i++ * 3000;
                $items = $client->getItemsOffset($offset);
                if (empty($items)) {
                    break;
                } else {
                    $filesystem->dumpFile('./tmp/old/items_' . $offset . '_offset.txt', $items);
                    $elasticSearchBuilder->createDocumentBulk(unserialize($items), 'item');
                }
            }

        }

        $i = 0;

        while (true) {
            $offset =  $i++ * 3000;
            $items = $client->getItemsOffset($offset);
            if (empty($items)) {
                break;
            } else {
                $filesystem->dumpFile('./tmp/new/items_' . $offset . '_offset.txt', $items);
            }
            $oldFile = hash_file('md5', './tmp/old/items_' . $offset . '_offset.txt');
            $newFile = hash_file('md5', './tmp/new/items_' . $offset . '_offset.txt');

            if ($oldFile != $newFile) {
                $fileOld = unserialize(file('./tmp/old/items_' . $offset . '_offset.txt')[0]);
                $fileNew = unserialize(file('./tmp/new/items_' . $offset . '_offset.txt')[0]);
                $itemPriceOld = array_column($fileOld, 'steam_price_en');
                $itemNameOld = array_column($fileOld, 'steam_market_hash_name');
                $itemPriceNew = array_column($fileNew, 'steam_price_en');
                $itemNameNew = array_column($fileNew, 'steam_market_hash_name');

                $combineOld = array_combine($itemNameOld, $itemPriceOld );
                $combineNew =  array_combine($itemNameNew, $itemPriceNew);

                $differencePrice = array_diff_assoc($combineOld, $combineNew);
                $priceKey = array_keys($differencePrice);


                $a = 0;

//                $filesystem->dumpFile('./tmp/old/items_' . $prefixOffset . '_offset.txt', $items);

            }




        }


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

    public function setElasticSearchBuilder(ElasticSearchBuilder $elasticSearchBuilder)
    {
        $this->elasticSearchBuilder = $elasticSearchBuilder;
    }

    public function getElasticSearchBuilder(): ElasticSearchBuilder
    {
        return $this->elasticSearchBuilder;
    }
}