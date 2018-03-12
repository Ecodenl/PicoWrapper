# Pico wrapper

## Publishing
```
php artisan vendor:publish --provider="Ecodenl\PicoWrapper\PicoServiceProvider" --tag=config
```

## Calling the API

```php
/** @var PicoClient $pico */
$pico = app()->make('pico');
$pico->bag_adres_pchnr(['query' => ['pc' => '1234 AB', 'hnr' => 1]]));
```