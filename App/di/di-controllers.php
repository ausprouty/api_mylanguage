<?php
return [
    'App\Controllers\BibleController' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleRepository')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainBibleController' => DI\autowire()->constructor(
        DI\get('App\Services\Bible\BibleUpdateService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Factories\BibleBrainConnectionFactory')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Services\Bible\BibleBrainLanguageService')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainPassageController' => DI\autowire()->constructor(
        DI\get('App\Services\BiblePassage\BibleBrainPassageService')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainTextFormatController' => DI\autowire()->constructor(
        DI\get('App\Services\Bible\BibleBrainPassageService')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainTextJsonController' => DI\autowire()->constructor(
        DI\get('App\Services\Bible\BibleBrainPassageService')
    ),
    'App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController' => DI\autowire()->constructor(
        DI\get('App\Services\Bible\PassageFormatterService'),
        DI\get('App\Repositories\BibleReferenceRepository')
    ),
    'App\Controllers\BiblePassage\BibleGateway\BibleGatewayBibleController' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleGatewayRepository'),
        DI\get('App\Services\Bible\BibleGatewayDataParserService'),
        DI\get('App\Services\Language\LanguageLookupService')
    ),
    'App\Controllers\BiblePassage\BibleGateway\BibleGatewayPassageController' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Repositories\BiblePassageRepository')
    ),
    'App\Controllers\BiblePassage\BibleWordPassageController' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Repositories\BiblePassageRepository')
    ),
    'App\Controllers\BiblePassage\BibleYouVersionPassageController' => DI\autowire()->constructor(
        DI\get('App\Services\Bible\YouVersionPassageService')
    ),
    'App\Controllers\BiblePassage\PassageSelectController' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Controllers\BibleStudy\BibleBlockController' => DI\autowire()->constructor(

    ),
    'App\Controllers\BibleStudy\Bilingual\BilingualTemplateTranslationController' => DI\autowire()->constructor(
        DI\get(''),
        DI\get(''),
        DI\get(''),
        DI\get('')
    ),
    'App\Controllers\BibleStudy\Monolingual\MonolingualTemplateTranslationController' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('string'),
        DI\get('string'),
        DI\get('string')
    ),
    'App\Controllers\BibleStudyController' => DI\autowire()->constructor(
        DI\get('App\Services\BibleStudy\BibleStudyService')
    ),
    'App\Controllers\Gospel\GospelPageController' => DI\autowire()->constructor(

    ),
    'App\Controllers\Language\DbsLanguageController' => DI\autowire()->constructor(
        DI\get('App\Services\Language\DbsLanguageService')
    ),
    'App\Controllers\Language\HindiLanguageController' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Controllers\Language\TranslationController' => DI\autowire()->constructor(

    ),
    'App\Controllers\Video\JesusVideoQuestionController' => DI\autowire()->constructor(

    ),
    'App\Controllers\Video\JesusVideoSegmentController' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('')
    ),
    'App\Controllers\Video\LifeVideoController' => DI\autowire()->constructor(

    ),
    'App\Controllers\Video\VideoController' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
];
