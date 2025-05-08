<?php
return [
    'App\Models\AskQuestionModel' => DI\autowire()->constructor(
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get('')
    ),
    'App\Models\Bible\BibleBookNameModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\BibleModel' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleRepository')
    ),
    'App\Models\Bible\PassageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\PassageReferenceModel' => DI\autowire()->constructor(

    ),
    'App\Models\BibleStudy\BaseStudyReferenceModel' => DI\autowire()->constructor(
        DI\get('array')
    ),
    'App\Models\BibleStudy\DbsReferenceModel' => DI\autowire()->constructor(
        DI\get('array')
    ),
    'App\Models\BibleStudy\LeadershipReferenceModel' => DI\autowire()->constructor(
        DI\get('array')
    ),
    'App\Models\BibleStudy\LifePrincipleReferenceModel' => DI\autowire()->constructor(
        DI\get('array')
    ),
    'App\Models\Language\CountryLanguageModel' => DI\autowire()->constructor(
        DI\get(''),
        DI\get(''),
        DI\get('')
    ),
    'App\Models\Language\DbsLanguageModel' => DI\autowire()->constructor(
        DI\get(''),
        DI\get(''),
        DI\get('')
    ),
    'App\Models\Language\LanguageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Tract\TractModel' => DI\autowire()->constructor(

    ),
    'App\Models\Video\JesusVideoSegmentModel' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Models\Video\VideoModel' => DI\autowire()->constructor(
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get('')
    ),
];
