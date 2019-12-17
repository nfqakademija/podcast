<?php

namespace App\EventListener;

use App\Interfaces\MailableEntity;
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

        if (!$entity instanceof MailableEntity) {
            return;
        }

        $this->mailService->sendVerification($entity);
    }
}
