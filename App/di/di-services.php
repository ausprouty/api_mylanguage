<?php
return [
    'App\Services\Bible\BibleBrainLanguageService' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Factories\LanguageFactory')
    ),
    'App\Services\Bible\BibleGatewayDataParserService' => DI\autowire()->constructor(

    ),
    'App\Services\Bible\BibleUpdateService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Models\Bible\BibleModel')
    ),
    'App\Services\BiblePassage\AbstractBiblePassageService' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Services\BiblePassage\BibleBrainPassageService' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Services\BiblePassage\BibleGatewayPassageService' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Services\BiblePassage\BibleWordPassageService' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Services\BiblePassage\YouVersionPassageService' => DI\autowire()->constructor(
        DI\get('App\Models\Bible\BibleModel'),
        DI\get('App\Models\Bible\PassageReferenceModel'),
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Services\BibleStudy\AbstractBibleStudy' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\AbstractBiLingualStudy' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\AbstractMonoLingualStudy' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\BibleStudyService' => DI\autowire()->constructor(
        DI\get('App\Renderers\RendererFactory'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('DI\Container')
    ),
    'App\Services\BibleStudy\MonoLingualDBS' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\MonoLingualLeadership' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\MonoLingualPrinciples' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService')
    ),
    'App\Services\BibleStudy\TemplateService' => DI\autowire()->constructor(

    ),
    'App\Services\Database\DatabaseService' => DI\autowire()->constructor(
        DI\get('')
    ),
    'App\Services\Language\DbsLanguageService' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Services\Language\LanguageLookupService' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Services\Language\TranslationService' => DI\autowire()->constructor(
        DI\get('string'),
        DI\get('string')
    ),
    'App\Services\LoggerService' => DI\autowire()->constructor(

    ),
    'App\Services\QrCodeGeneratorService' => DI\autowire()->constructor(

    ),
    'App\Services\ResourceStorageService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\BibleBrainConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\BibleGatewayConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\BibleWordConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\CloudFrontConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\WebsiteConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
    'App\Services\Web\YouVersionConnectionService' => DI\autowire()->constructor(
        DI\get('string')
    ),
];
