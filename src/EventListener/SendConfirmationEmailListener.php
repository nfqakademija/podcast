<?php


namespace App\EventListener;


use App\Interfaces\Confirmable;
use App\Service\MailService;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class SendConfirmationEmailListener
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Confirmable) {
            return;
        }

        $this->mailService->sendVerification($entity);
    }
}