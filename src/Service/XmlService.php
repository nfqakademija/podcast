<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;

class XmlService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $podcasts
     * @return mixed
     */
    public function generate(array $podcasts)
    {
        $items = [];
        foreach ($podcasts as $podcast) {
            $title = $this->xmlEscape($podcast->getTitle());
            $url = $this->xmlEscape('https://podcast.projektai.nfqakademija.lt/podkastas/' . $podcast->getId());
            $description = $this->xmlEscape($podcast->getDescription());
            $pubDate = $podcast->getPublishedAt()->format('D, d M Y H:i:s T');
            $item = ['item' =>
            ['title' => $title, 'link' => $url, 'description' => $description, 'pubDate' => $pubDate]];
            $items[] = $item;
        }

        $array = ['channel' => [
            'title' => ['Krepšinio podcastai'],
            'link' => 'https://podcast.projektai.nfqakademija.lt',
            'description' => 'Krepšinio podkastai, pokalbiai, diskusijos',
            'language' => 'en-us',
            $items
        ]];

        $context['xml_root_node_name'] = 'rss';
        $context['remove_empty_tags'] = true;
        $xml = $this->serializer->encode($array, 'xml', $context);

        return $xml;
    }

    /**
     * @param string|null $string
     * @return string
     */
    private function xmlEscape(?string $string): string
    {
        return str_replace(
            array('&', '<', '>', '\'', '"'),
            array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'),
            $string
        );
    }
}
