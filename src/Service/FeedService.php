<?php

namespace App\Service;

class FeedService
{
    public static function generate($podcasts)
    {
        $xml = <<<xml
            <?xml version='1.0' encoding='UTF-8'?>
            <rss version='2.0'>
            <channel>
            <title>Krepšinio podcastai</title>
            <link>http://podcast.projektai.nfqakademija.lt</link>
            <description>Krepšinio podkastai, pokalbiai, diskusijos</description>
            <language>en-us</language>
            xml;
        foreach ($podcasts as $podcast) {
            $title = self::xmlEscape($podcast->getTitle());
            $url = 'podkastas/' . $podcast->getId();
            $description = self::xmlEscape($podcast->getDescription());
            $pubDate = $podcast->getPublishedAt()->format('D, d M Y H:i:s T');
            $xml .= <<<xml
            <item>
            <title>{$title}</title>
            <link>https://podcast.projektai.nfqakademija.lt/{$url}</link>
            <description>{$description}</description>
            <pubDate>$pubDate</pubDate>
            </item>
            xml;
        }
        $xml .= "</channel></rss>";

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
