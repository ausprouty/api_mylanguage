<?php
return [
    'App\Factories\BibleBrainConnectionFactory' => DI\autowire()->constructor(

    ),
    'App\Factories\BibleFactory' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleRepository')
    ),
    'App\Factories\BibleStudyReferenceFactory' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Factories\LanguageFactory' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Factories\PassageFactory' => DI\autowire()->constructor(

    ),
    'App\Factories\PassageReferenceFactory' => DI\autowire()->constructor(
        DI\get('App\Repositories\PassageReferenceRepository')
    ),
];
