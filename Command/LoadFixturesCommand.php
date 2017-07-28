<?php

namespace Piszczek\PimcoreFixturesBundle\Command;

use Nelmio\Alice\Loader\NativeLoader;
use Pimcore\Model\Document\Page;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('pimcore:fixtures:load')
            ->setDescription('Load fixtures.')
            ->setHelp('This command loads fixutres')
            ->addArgument('files', InputArgument::REQUIRED, 'Comma separated files located at app/Resources/fixtures');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = PIMCORE_APP_ROOT.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;

        $fileNames = explode(',', $input->getArgument('files'));

        //todo: add file_exist check
        $loader = new NativeLoader();


        foreach ($fileNames as $fileName) {
            $loader->loadFile($baseDir . $fileName);
        }
    }
}