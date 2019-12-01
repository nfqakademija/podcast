<?php

namespace App\Command;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterSendingCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'send-newsletters';

    private $mailService;

    public function __construct(MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sends newsletters to subscribers')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $output->writeln([
            'Starts sending...',
        ]);

        $result = $this->mailService->sendDailyNewsletterToSubscribers();

        if ($result) {
            $output->writeln('<fg=green>Emails sent successfully</>');
        } else {
            $output->writeln('<fg=red>No new podcasts today, we dont spam people with empty newsletters</>');
        }

        $this->release();

        return 0;
    }
}
