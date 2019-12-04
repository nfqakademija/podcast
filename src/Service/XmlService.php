<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class XmlService
{
    public static function generate($podcasts)
    {
        $xmlEncoder = new XmlEncoder();

        $items = [];
        foreach ($podcasts as $podcast) {
            $title = self::xmlEscape($podcast->getTitle());
            $url = self::xmlEscape('https://podcast.projektai.nfqakademija.lt/podkastas/' . $podcast->getId());
            $description = self::xmlEscape($podcast->getDescription());
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
        $xml = $xmlEncoder->encode($array, 'xml', $context);

        return $xml;
    }

    private static function xmlEscape($string)
    {
        return str_replace(
            array('&', '<', '>', '\'', '"'),
            array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'),
            $string
        );
    }
}
