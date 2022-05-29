<?php
namespace App\Console;

use App\Client\ItemsccService;
use App\Create\ElasticSearchItemCreate;
use App\Repository\ItemRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class UpdatePriceElasticSearchCommand extends Command
{

    const OFFSET = 3000;

    /**
     * @var ItemsccService
     */
    public $itemscc;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var ElasticSearchItemCreate
     */
    private $elasticSearchItemCreate;
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var string
     */
    private $temporaryFilesPath;


    protected function configure()
    {
        $this
            ->setName('user:UpdatePrice')
            ->setDescription('Create and Update Price');
    }

    /**
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);
        $this->builderService();

        $this->createElasticIndexDirectoryFile();

        $this->updateElasticIndexDirectoryFile();


        $output->writeln(
            'Success ' . 'Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.'
        );
        return 1;
    }



    public function preparingWriteToFile($items)
    {
        $itemPrice = array_column($items, 'steam_price_en');
        $itemName = array_column($items, 'steam_market_hash_name');
        return  array_combine($itemName, $itemPrice);
    }

    public function builderService()
    {
        $this->itemscc = $this->getItemsccClient();
        $this->filesystem = $this->getFilesystem();
        $this->elasticSearchItemCreate = $this->getElasticSearchItemCreate();
        $this->itemRepository = $this->getItemRepository();
    }

    public function pathFile($path, $offset): string
    {
        return './' . $this->getTemporaryFiles() . '/' . $path . '/items_' . $offset . '_offset.txt';
    }

    public function saveFileOffset($items, $offset, $path): bool
    {
        if (empty($items)) {
            $respond = false;
        } else {
            $dataItems = $this->preparingWriteToFile($items);
            $this->filesystem->dumpFile($this->pathFile($path, $offset), serialize($dataItems));
            $respond = true;
        }
        return $respond;
    }

    public function valueMdHash($differencePrice): array
    {
        $hashName = array_keys($differencePrice);
        $hashNameMd = [];
        foreach ($hashName as $hash) {
            $hashNameMd[] = md5($hash);
        }
        return $hashNameMd;
    }

    /**
     * @param $offset
     * @return false|mixed
     */
    public function differencePrice($offset)
    {
        $oldFile = hash_file('md5', $this->pathFile('old', $offset));
        $newFile = hash_file('md5', $this->pathFile('new', $offset));
        if ($oldFile != $newFile) {
            $fileOld = unserialize(file($this->pathFile('old', $offset))[0]);
            $fileNew = unserialize(file($this->pathFile('new', $offset))[0]);
            $differencePrice = array_diff_assoc($fileNew, $fileOld);
            $responded = $differencePrice;
        } else {
            $responded = false;
        }
        return $responded;
    }

    public function createElasticIndexDirectoryFile()
    {
        if (!$this->filesystem->exists('tmp')) {
            $i = 0;
            while (true) {
                $offset =  $i++ * $this::OFFSET;
                $items = $this->itemscc->getItemsOffset($offset);
                if (!$this->saveFileOffset($items, $offset, 'old')) {
                    break;
                }
                $this->elasticSearchItemCreate->createIndex('item');
                $this->elasticSearchItemCreate->createDocumentBulk($items, 'item');
            }
        }
    }

    public function updateElasticIndexDirectoryFile()
    {
        $i = 0;
        while (true) {
            $offset =  $i++ * $this::OFFSET;
            $items = $this->itemscc->getItemsOffset($offset);
            if (!$this->saveFileOffset($items, $offset, 'new')) {
                break;
            }

            if ($differencePrice = $this->differencePrice($offset)) {
                $hashNameMd = $this->valueMdHash($differencePrice);
                $response = $this->itemRepository->terms($hashNameMd);

                if (empty($response['hits']['hits']) && !empty($differencePrice)) {
                    $this->elasticSearchItemCreate->createDocumentBulk($differencePrice, 'item');
                }

                if (!empty($response['hits']['hits'])) {
                    $createBuilder = [];
                    $arrayNameHash = [];
                    foreach ($response['hits']['hits'] as $item) {
                        $createBuilder[] = [
                            'id' => $item['_id'],
                            'price' => $differencePrice[$item['_source']['data']['item']['name']]
                        ];
                        $arrayNameHash[$item['_source']['data']['item']['name']] = 0;
                    }
                    $this->elasticSearchItemCreate->updatePriceDocumentBulk($createBuilder,'item');

                    if (count($differencePrice) != $response['hits']['total']['value']) {
                        $items = array_diff_key($differencePrice,$arrayNameHash);
                        $this->elasticSearchItemCreate->createDocumentBulk($items, 'item');
                    }
                }
                $this->saveFileOffset($items, $offset, 'old');

            }
        }
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

    public function setElasticSearchItemCreate(ElasticSearchItemCreate $elasticSearchItemCreate)
    {
        $this->elasticSearchItemCreate = $elasticSearchItemCreate;
    }

    public function getElasticSearchItemCreate(): ElasticSearchItemCreate
    {
        return $this->elasticSearchItemCreate;
    }

    public function setItemRepository(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getItemRepository(): ItemRepository
    {
        return $this->itemRepository;
    }

    public function setTemporaryFiles($path)
    {
        $this->temporaryFilesPath = $path;
    }

    public function getTemporaryFiles(): string
    {
        return $this->temporaryFilesPath;
    }
}