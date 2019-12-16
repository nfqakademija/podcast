![Krepšinio podcastai](https://podcast.projektai.nfqakademija.lt/images/headphones.png)

# Description

This app for Lithuanian basketball podcasts. There are many sites with podcasts, instead of following all of them, there is only one site. It collects data from entered sources. Currently, we have 13 sources, but we see a trend that the number of sources will grow. Therefore this app indeed very necessary for any basketball fan.

# Requirements

- PHP 7.3
- MySQL
- Nginx
- Docker 

# Technologies under the hood

- [Crawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Youtube API](https://developers.google.com/youtube/v3)

# Features

- Filters by source, type, tags
- Search
- Newsletters about new podcasts for subscribers (no registration needed)
- Newsletters about new podcasts by tag (for registered users)
- RSS feed
- Like/dislike (for registered users)
- Listen later (for registered users)
- Comments (for registered users)

# How to use

- Clone the project
- Go to the project folder and run Docker containers `scripts/start.sh`
- First time run `scripts/install-first.sh`
- Migrate data, run `scripts/backend.sh`, then in container run `bin/console doctrine:migrations:migrate`
- In env.local enter Youtube API key and Mailer URL
- Add sources in the database
- Enter PHP container `scripts/backend.sh` and run command to collect podcasts `collect-podcasts`

More information you can find [here](https://github.com/nfqakademija/kickstart#paleidimo-instrukcija).

# Live version

[https://podcast.projektai.nfqakademija.lt](https://podcast.projektai.nfqakademija.lt)

# Feedback

Any feedback is more than welcome, reach us by email krepsinio.podcast@gmail.com