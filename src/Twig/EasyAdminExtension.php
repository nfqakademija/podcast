<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EasyAdminExtension extends AbstractExtension
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('filter_admin_actions', [$this, 'filterActions'])
        ];
    }

    public function filterActions(array $itemActions, $item)
    {
        if ($item instanceof User && $item->getId() === $this->security->getUser()->getId()) {
            unset($itemActions['delete']);
        }

        return $itemActions;
    }
}
