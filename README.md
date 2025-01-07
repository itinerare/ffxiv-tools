# FFXIV Tools

A collection of small, light-weight browser utilities related to FFXIV. Currently contains:

- Leveling Calculator
    - Takes user input (e.g. current level, highest level class/job, various EXP buffs) and calculates number of dungeon/deep dungeon runs or frontline matches to get to the next level/to the end of a level range (e.g. 1-50, 51-60). Also supports fetching level, EXP, and highest level class/job from The Lodestone provided character ID and class/job.
- Crafting Profit Calculator
    - Uses data from Teamcraft and Universalis (as well as provided user preferences) to calculate profit for a given crafting job's recipes and tries to recommend the most profitable (and likely to sell). Includes "endgame" crafting recipes from 6.x and 7.x.
- Gathering Profit Calculator
    - Uses data from Teamcraft and Universalis (as well as provided user preferences) to list gatherable items used in "endgame" crafting recipes (6.x and 7.x) and rank them based on price; also tries to recommend the most profitable (and likely to sell).
- Diadem Gil Optimization
    - Consults Universalis to see what the current pricest mat on each Diadem node is on a given world, as well as the priciest overall.

For all utilities that use game item, Universalis, etc. data, data is stored locally to reduce strain on external services and maintain availability. Relevant utilities consequently also account for Universalis data recency to the best of their ability (particularly when making recommendations).
Additionally, all utilities that support world selection support NA, Europe, JP, and Oceania data centers/worlds.
    
An instance can be found at https://ffxiv.itinerare.net and is automatically updated from this repo. 

## Setup

### Obtain a copy of the code

```
$ git clone https://code.itinerare.net/itinerare/ffxiv-tools.git
```

### Configure .env in the directory

```
$ cp .env.example .env
```

This only needs the bare minimum (e.g. no database connection info needs to be provided).

### Setting up

Install packages with composer:
```
$ composer install
```

Generate app key:
```
$ php artisan key:generate
```

Create the database and tables:
```
$ touch database/database.sqlite
$ php artisan migrate
$ php artisan app:update-universalis-cache
```

Queue game item record creation and Universalis data updates:
```
$ php artisan app:update-universalis-cache
```

You will also need to set the Laravel scheduler to run, e.g. by adding the following to a crontab:
```
* * * * * cd ~/your_domain/www && php artisan schedule:run >> /dev/null 2>&1
```

You will also need to set up a queue worker. See the [Laravel docs](https://laravel.com/docs/11.x/queues#running-the-queue-worker) or [Aldebaran docs](https://code.itinerare.net/itinerare/Aldebaran/wiki/Queue-Setup) for instructions on how to do this.

## Contact
If you have any questions, please contact me via email at [queries@itinerare.net](emailto:queries@itinerare.net).
