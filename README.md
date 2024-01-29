# FFXIV Tools

A collection of small, light-weight browser utilities related to FFXIV. Currently contains:

- Leveling Calculator
    - Takes user input (e.g. current level, highest level class, various EXP buffs) and calculates number of dungeon/deep dungeon runs or frontline matches to get to the next level/to the end of a level range (e.g. 1-50, 51-60).
- Diadem Gil Optimization
    - Consults Universalis to see what the current pricest mat on each Diadem node is on a given world, as well as the priciest overall. Supports NA, Europe, JP, and Oceania data centers/worlds.
## Setup

### Obtain a copy of the code

```
$ git clone https://github.com/itinerare/alcyone.git
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
