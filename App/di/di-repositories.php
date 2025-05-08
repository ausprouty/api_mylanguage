<?php
return [
    'App\Repositories\AskQuestionRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\BaseRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\BibleBookRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\BibleGatewayRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\BibleRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\CountryLanguageRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\DbsLanguageRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\LanguageRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Factories\LanguageFactory')
    ),
    'App\Repositories\PassageReferenceRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\PassageRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Repositories\VideoRepository' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
];
