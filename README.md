# Description

This app for Lithuanian basketball podcasts. There are many sites with podcasts, instead of following all of them, there is one app. It collects data from entered sources.

# Requirements

- PHP 7.4
- Docker 

# Technologies under the hood

- [Crawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Youtube API](https://developers.google.com/youtube/v3)

# Features

-  Filters by source, type, tags
- Search
- Newsletters about new podcasts for subscribers (no registration needed)
- Newsletters about new podcasts by tag (for registered users)
- Like/dislike (for registered users)
- Listen later (for registered users)
- Comments (for registered users)

# How to use

- Clone the project
- Go to the project folder and run Docker containers `scripts/start.sh`
- First time run `scripts/install-first.sh`
- Migrate data, run `scripts/backend.sh`, then in container run `bin/console doctrine:migrations:migrate`
- In env.local enter Youtube API key and Mailer URL

More information you can find [here](https://github.com/nfqakademija/kickstart#paleidimo-instrukcija).
