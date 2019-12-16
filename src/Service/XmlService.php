<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class XmlService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $router)
    {
        $this->serializer = $serializer;
        $this->router = $router;
    }

    /**
     * @param array $podcasts
     * @return mixed
     */
    public function generate(array $podcasts)
    {
        $items = [];
        foreach ($podcasts as $podcast) {
            $title = htmlspecialchars($podcast->getTitle());
            $url = $this->router->generate(
                'single_podcast',
                [
                    'slug' => $podcast->getSlug(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $url = htmlspecialchars($url);
            $description = htmlspecialchars($podcast->getDescription());
            $pubDate = $podcast->getPublishedAt()->format('D, d M Y H:i:s T');
            $item = ['item' =>
            ['title' => $title, 'link' => $url, 'description' => $description, 'pubDate' => $pubDate]];
            $items[] = $item;
        }

        $homeUrl = $this->router->generate('podcasts', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $array = ['channel' => [
            'title' => ['Krepšinio podcastai'],
            'link' => $homeUrl,
            'description' => 'Krepšinio podkastai, pokalbiai, diskusijos',
            'language' => 'en-us',
            $items
        ]];

        $context['xml_root_node_name'] = 'rss';
        $context['remove_empty_tags'] = true;
        $xml = $this->serializer->encode($array, 'xml', $context);

        return $xml;
    }
}
