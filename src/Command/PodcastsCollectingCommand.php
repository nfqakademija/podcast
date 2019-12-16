<?php

namespace App\Command;

use App\Repository\SourceRepository;
use App\Service\CrawlerService;
use App\Service\YoutubeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PodcastsCollectingCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'collect-podcasts';

    /**
     * @var SourceRepository
     */
    private $sourceRepository;

    /**
     * @var YoutubeService
     */
    private $youtubeApiService;

    /**
     * @var CrawlerService
     */
    private $crawlerService;

    public function __construct(
        YoutubeService $youtubeApiService,
        SourceRepository $sourceRepository,
        CrawlerService $crawlerService
    ) {
        $this->youtubeApiService = $youtubeApiService;
        $this->sourceRepository = $sourceRepository;

        parent::__construct();
        $this->crawlerService = $crawlerService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Collect new podcasts from Youtube and various podcast sites')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $output->writeln([
            'Starts Collecting...',
        ]);

        $sources = $this->sourceRepository->findBy([
            'sourceType' => 'Youtube'
        ]);

        $youtubeResults = $this->youtubeApiService->importDataFromYoutube($sources);
        $crawlerResults = $this->crawlerService->scrapSites();

        if ($youtubeResults && $crawlerResults) {
            $output->writeln('<fg=green>Collecting ended successfully</>');
        } else {
            $output->writeln('<fg=red>Collecting ended with errors</>');
        }

        $this->release();

        return 1;
    }
}
