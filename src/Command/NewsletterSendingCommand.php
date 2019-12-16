<?php

namespace App\Command;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class NewsletterSendingCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'send-newsletters';

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(MailService $mailService, RouterInterface $router)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->router = $router;
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

        if (getenv('DOMAIN')) {
            $context = $this->router->getContext();
            $context->setHost(getenv('DOMAIN'));
            $context->setScheme('https');
        }

        $output->writeln([
            'Starts sending...',
        ]);

        $mailsForSubscribers = $this->mailService->sendDailyNewsletterToSubscribers();
        $mailsForUsersByTags = $this->mailService->sendDailyNewsletterBySelectedTagsToRegisteredUsers();

        if ($mailsForSubscribers) {
            $output->writeln('<fg=green>Emails for subscribers sent successfully</>');
        } else {
            $output->writeln(
                '<fg=red>No subscribers in database or no new podcasts today</>'
            );
        }

        if ($mailsForUsersByTags) {
            $output->writeln('<fg=green>Emails for users sent successfully</>');
        } else {
            $output->writeln(
                '<fg=red>No new podcasts by tags today, or no registered users with subscription</>'
            );
        }


        $this->release();

        return 0;
    }
}
