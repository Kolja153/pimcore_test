<?php

namespace App\Command;

use App\Service\ProductService;
use App\Service\UploadImageService;
use Carbon\Carbon;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset\Image;
use Psy\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductCommand extends AbstractCommand
{
    public function __construct(private UploadImageService $uploadImageService)
    {
        parent::__construct($this->getName());
    }

    protected function configure()
    {
        $this->setName('app:product.import');
        $this->addArgument('url', InputArgument::REQUIRED, 'Import from url');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        $data = $this->getProductsData($url);

        foreach ($data['products'] as $item) {
            $date = new Carbon($item['date']);
            $product = ProductService::findOrCreateProduct($item['gtin']);
            $product->setName($item['name']);
            $product->setDate($date);

            $asset = $this->uploadImageService->uploadFileFromUrl($item['image']);
            if ($asset) {
                $image = Image::getByPath($asset->getFullPath());
                $product->setImage($image);
            }

            $product->save();

            $output->writeln('Saved product: ' . $product->getGtin());
        }

        return Command::SUCCESS;
    }

    private function getProductsData(string $url): array
    {
        try {
            $data = file_get_contents($url);

            return json_decode($data, true);
        } catch (\Exception $errorException) {

            throw new \ErrorException("Error parsing product data: " . $errorException->getMessage());
        }
    }
}
