<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

class SymfonySerializerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Serializer::class, function ($app) {
            // Inspired by https://stackoverflow.com/a/60341687
            $extractor = new ReflectionExtractor();
            $normalizers = [new ObjectNormalizer(null, null, null, $extractor), new ArrayDenormalizer()];
            return new Serializer($normalizers);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
