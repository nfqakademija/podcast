<?php

namespace App\DataFixtures;

use App\Entity\Podcast;
use App\Entity\Source;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const SOURCES = [
        [
            'name' => 'Delfi krepšinio zonoje',
            'url' => 'https://www.delfi.lt/klausyk/krepsinio-zonoje/',
            'mainElementSelector' => '.playlist-item',
            'imageSelector' => null,
            'titleSelector' => '.player-title',
            'descriptionSelector' => null,
            'audioSelector' => '.playlist-item',
            'audioSourceAttribute' => 'data-src',
            'publicationDateSelector' => '.player-date',
            'imageSourceAttribute' => null,
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'Delfi Iš viršaus',
            'url' => 'https://www.delfi.lt/klausyk/virsaus/',
            'mainElementSelector' => '.playlist-item',
            'imageSelector' => null,
            'titleSelector' => '.player-title',
            'descriptionSelector' => null,
            'audioSelector' => '.playlist-item',
            'audioSourceAttribute' => 'data-src',
            'publicationDateSelector' => '.player-date',
            'imageSourceAttribute' => null,
             'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'Basket News',
            'url' => 'https://basketnews.podbean.com',
            'mainElementSelector' => '.entry',
            'imageSelector' => 'img',
            'titleSelector' => 'h2',
            'descriptionSelector' => 'p',
            'audioSelector' => '.theme1',
            'audioSourceAttribute' => 'data-uri',
            'publicationDateSelector' => '.date',
            'imageSourceAttribute' => 'data-src',
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'Iš devinių metrų',
            'url' => 'https://is9metru.podbean.com/',
            'mainElementSelector' => '.entry',
            'imageSelector' => 'img',
            'titleSelector' => 'h2',
            'descriptionSelector' => 'p',
            'audioSelector' => '.theme1',
            'audioSourceAttribute' => 'data-uri',
            'publicationDateSelector' => '.day',
            'imageSourceAttribute' => 'data-src',
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'Iš eilutės',
            'url' => 'https://iseilutes.podbean.com/',
            'mainElementSelector' => '.entry',
            'imageSelector' => 'img',
            'titleSelector' => 'h2',
            'descriptionSelector' => 'p',
            'audioSelector' => '.theme1',
            'audioSourceAttribute' => 'data-uri',
            'publicationDateSelector' => '.day',
            'imageSourceAttribute' => 'data-src',
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'Užkalti halės langai',
            'url' => 'https://uzkaltihaleslangai.podbean.com/',
            'mainElementSelector' => '.entry',
            'imageSelector' => 'img',
            'titleSelector' => 'h2',
            'descriptionSelector' => 'p',
            'audioSelector' => '.theme1',
            'audioSourceAttribute' => 'data-uri',
            'publicationDateSelector' => '.date',
            'imageSourceAttribute' => 'data-src',
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ],
        [
            'name' => 'urBONUSas',
            'url' => 'https://urbonusas.podbean.com/',
            'mainElementSelector' => '.entry',
            'imageSelector' => 'img',
            'titleSelector' => 'h2',
            'descriptionSelector' => 'p',
            'audioSelector' => '.theme2',
            'audioSourceAttribute' => 'data-uri',
            'publicationDateSelector' => '.day',
            'imageSourceAttribute' => 'data-src',
            'sourceType' => Podcast::TYPES['TYPE_AUDIO']
        ]
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SOURCES as $source) {
            $fixtureSource = new Source();
            $fixtureSource->setName($source['name']);
            $fixtureSource->setUrl($source['url']);
            $fixtureSource->setMainElementSelector($source['mainElementSelector']);
            $fixtureSource->setImageSelector($source['imageSelector']);
            $fixtureSource->setTitleSelector($source['titleSelector']);
            $fixtureSource->setDescriptionSelector($source['descriptionSelector']);
            $fixtureSource->setAudioSelector($source['audioSelector']);
            $fixtureSource->setAudioSourceAttribute($source['audioSourceAttribute']);
            $fixtureSource->setPublicationDateSelector($source['publicationDateSelector']);
            $fixtureSource->setImageSourceAttribute($source['imageSourceAttribute']);
            $fixtureSource->setCreatedAt(new \DateTime());
            $fixtureSource->setSourceType($source['sourceType']);

            $manager->persist($fixtureSource);
        }

        $manager->flush();
    }
}
