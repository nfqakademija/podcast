<?php

namespace App\Command;

use App\Repository\SourceRepository;
use App\Service\YoutubeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateYoutubeCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'migrate-youtube';
    /**
     * @var SourceRepository
     */
    private $sourceRepository;
    /**
     * @var YoutubeService
     */
    private $youtubeApiService;

    public function __construct(
        YoutubeService $youtubeApiService,
        SourceRepository $sourceRepository
    ) {
        $this->youtubeApiService = $youtubeApiService;
        $this->sourceRepository = $sourceRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Make data migration from Sources with type \'Youtube\'')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $output->writeln([
            'Starts Migration...',
        ]);

        $sources = $this->sourceRepository->findBy([
            'sourceType' => 'Youtube'
        ]);

        $res = $this->youtubeApiService->importDataFromYoutube($sources);

        if ($res) {
            $output->writeln('<fg=green>Migration ended successfully</>');
        } else {
            $output->writeln('<fg=red>Migration ended with errors</>');
        }

        $this->release();

        return 1;
    }
}
