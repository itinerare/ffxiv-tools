# FFXIV Tools

A collection of small, light-weight browser utilities related to FFXIV. Currently contains:

- Leveling Calculator
    - Takes user input (e.g. current level, highest level class/job, various EXP buffs) and calculates number of dungeon/deep dungeon runs or frontline matches to get to the next level/to the end of a level range (e.g. 1-50, 51-60). Also supports fetching level, EXP, and highest level class/job from The Lodestone provided character ID and class/job.
- Diadem Gil Optimization
    - Consults Universalis to see what the current pricest mat on each Diadem node is on a given world, as well as the priciest overall. Supports NA, Europe, JP, and Oceania data centers/worlds.
    
An instance can be found at https://ffxiv.itinerare.net and is periodically updated from this repo. 

## Setup

### Obtain a copy of the code

```
$ git clone https://code.itinerare.net/itinerare/ffxiv-tools.git
```

### Configure .env in the directory

```
$ cp .env.example .env
```

This only needs the bare minimum (e.g. this doesn't use a database for anything).

### Setting up

Install packages with composer:
```
$ composer install
```

Generate app key:
```
$ php artisan key:generate
```

## Contact
If you have any questions, please contact me via email at [queries@itinerare.net](emailto:queries@itinerare.net).
