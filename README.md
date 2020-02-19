# Recalc Gedmo Tree Command (Doctrine Behavioral Extensions)

This command was created for [Gedmo Doctrine Behavioral Tree Extension](https://github.com/Atlantic18/DoctrineExtensions).

If your tree has been damaged, the leaves and roots have incorrect values - this command is all you need.

You can easily recalc all the properties of a tree by one command.

## How it use?

1. Install package ``composer require devpack/gedmo-tree-recalc`` or copy only command 😉
2. Add bundle to your ``bundles.php`` file

**Example:**

```php
<?php

return [
    // ...
    DevPack\GedmoTreeRecalc\GedmoTreeRecalcBundle::class => ['all' => true],
];
```

3. Run your symfony console

**Example:**

```
php bin/console gedmo:tree:recalc [<YourEntity>]
```

## How test package?

Run this command: ``./vendor/bin/phpunit tests``

#### If I helped you, leave me a star ⭐
