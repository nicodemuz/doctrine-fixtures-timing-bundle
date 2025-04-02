# 📦 DoctrineFixturesTimingBundle

A Symfony bundle that extends the DoctrineFixturesBundle to load fixtures with timing information ⏳ and a report of the top slowest fixtures 🐢.

## 🚀 Installation

Install the bundle via Composer:

```bash
composer require nicodemuz/doctrine-fixtures-timing-bundle
```

Enable the bundle in your Symfony application by adding it to `config/bundles.php`:

```php
return [
    // ...
    Nicodemuz\DoctrineFixturesTimingBundle\DoctrineFixturesTimingBundle::class => ['dev' => true, 'test' => true],
];
```

## 🛠️ Usage

Run the command to load your fixtures with timing analysis:

```bash
php bin/console nicodemuz:doctrine:fixtures:load-with-timing
```

## ⏱️ Output Example

```text
Top 15 Slowest Fixtures
-----------------------

 ------------------------------------------------------ ----------------------
  Fixture Class                                          Time Taken (seconds)  
 ------------------------------------------------------ ----------------------
  App\DataFixtures\ORM\UserFixtures                      7.251                 
  App\DataFixtures\ORM\MediaFixtures                     0.537                 
  App\DataFixtures\ORM\TagFixtures                       0.336                 
  App\DataFixtures\ORM\ForumFixtures                     0.204                 
  App\DataFixtures\ORM\SubscriptionFixtures              0.150                 
  ...                                                   ...                   
 ------------------------------------------------------ ----------------------

[✅] All 108 fixtures loaded in 24.591 seconds
```

⚡ Now you can easily identify and optimize the slowest fixtures in your project! 🚀

