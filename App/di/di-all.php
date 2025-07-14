<?php
return [
    'App\Configuration\Config' => DI\autowire()->constructor(

    ),
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

    ),
    'App\Controllers\BibleStudy\Monolingual\MonolingualTemplateTranslationController' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
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
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Controllers\Video\VideoController' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Cron\BibleBrainLanguageSyncService' => DI\autowire(),

    'App\Factories\BibleBrainConnectionFactory' => DI\autowire()->constructor(

    ),
    'App\Factories\BibleFactory' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleRepository')
    ),
    'App\Factories\BibleStudyReferenceFactory' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\PassageReferenceRepository')
    ),
    'App\Factories\LanguageFactory' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Factories\PassageFactory' => DI\autowire()->constructor(

    ),
    'App\Factories\PassageReferenceFactory' => DI\autowire()->constructor(
        DI\get('App\Repositories\PassageReferenceRepository')
    ),
    'App\Helpers\FilenameHelper' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Services\LoggerService' => DI\autowire()->constructor(

    ),
    'App\Middleware\CORSMiddleware' => DI\autowire()->constructor(

    ),
    'App\Middleware\PostAuthorizationMiddleware' => DI\autowire()->constructor(

    ),
    'App\Middleware\PreflightMiddleware' => DI\autowire()->constructor(

    ),
    'App\Models\AskQuestionModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\BibleBookNameModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\BibleModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\PassageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Bible\PassageReferenceModel' => DI\autowire()->constructor(

    ),
    'App\Models\BibleStudy\DbsReferenceModel' => DI\autowire()->constructor(

    ),
    'App\Models\BibleStudy\LeadershipReferenceModel' => DI\autowire()->constructor(

    ),
    'App\Models\BibleStudy\LifePrincipleReferenceModel' => DI\autowire()->constructor(

    ),
    'App\Models\Language\CountryLanguageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Language\DbsLanguageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Language\LanguageModel' => DI\autowire()->constructor(

    ),
    'App\Models\Tract\TractModel' => DI\autowire()->constructor(

    ),
    'App\Models\Video\JesusVideoSegmentModel' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService')
    ),
    'App\Models\Video\VideoModel' => DI\autowire()->constructor(

    ),
    'App\Renderers\HtmlRenderer' => DI\autowire()->constructor(

    ),
    'App\Renderers\PdfRenderer' => DI\autowire()->constructor(

    ),
    'App\Renderers\RendererFactory' => DI\create()
        ->constructor([
            'html' => DI\get('App\Renderers\HtmlRenderer'),
            'pdf' => DI\get('App\Renderers\PdfRenderer'),
        ]),
    'App\Repositories\AskQuestionRepository' => DI\autowire()->constructor(
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
    'App\Services\Bible\BibleBrainLanguageService' => DI\autowire()->constructor(
        DI\get('App\Repositories\BibleBrainLanguageRepository'),
        DI\get('App\Factories\LanguageFactory')
    ),
    'App\Services\Bible\BibleGatewayDataParserService' => DI\autowire()->constructor(

    ),
    'App\Services\Bible\BibleUpdateService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Models\Bible\BibleModel')
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
    'App\Services\BibleStudy\BibleStudyService' => DI\autowire()->constructor(
        DI\get('App\Renderers\RendererFactory'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('DI\Container')
    ),
    'App\Services\BibleStudy\BilingualDbsStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\BilingualLeadStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\BilingualLifeStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\BilingualStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\MonolingualDbsStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\MonolingualLeadStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\MonolingualLifeStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\MonolingualStudyService' => DI\autowire()->constructor(
        DI\get('App\Services\Database\DatabaseService'),
        DI\get('App\Repositories\LanguageRepository'),
        DI\get('App\Repositories\BibleRepository'),
        DI\get('App\Factories\BibleStudyReferenceFactory'),
        DI\get('App\Services\BiblePassage\BiblePassageService'),
        DI\get('App\Factories\PassageReferenceFactory'),
        DI\get('App\Services\BibleStudy\TemplateService'),
        DI\get('App\Services\Language\TranslationService'),
        DI\get('App\Services\TwigService'),
        DI\get('App\Services\LoggerService'),
        DI\get('App\Services\QRCodeGeneratorService'),
        DI\get('App\Services\VideoService')
    ),
    'App\Services\BibleStudy\TemplateService' => DI\autowire()->constructor(

    ),
    'App\Services\Database\DatabaseService' => DI\autowire()->constructor(

    ),
    'App\Services\Language\DbsLanguageService' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Services\Language\LanguageLookupService' => DI\autowire()->constructor(
        DI\get('App\Repositories\LanguageRepository')
    ),
    'App\Services\Language\TranslationService' => DI\autowire()->constructor(

    ),
    'App\Services\QrCodeGeneratorService' => DI\autowire()->constructor(

    ),
    'App\Services\ResourceStorageService' => DI\autowire()->constructor(

    ),
    'App\Services\TwigService' => DI\autowire()->constructor(

    ),
    'App\Services\VideoService' => DI\autowire()->constructor(
        DI\get('App\Services\TwigService')
    ),
    'App\Services\Web\BibleBrainConnectionService' => DI\autowire()->constructor(

    ),
    'App\Services\Web\BibleGatewayConnectionService' => DI\autowire()->constructor(

    ),
    'App\Services\Web\BibleWordConnectionService' => DI\autowire()->constructor(

    ),
    'App\Services\Web\CloudFrontConnectionService' => DI\autowire()->constructor(

    ),
    'App\Services\Web\WebsiteConnectionService' => DI\autowire()->constructor(

    ),
    'App\Services\Web\YouVersionConnectionService' => DI\autowire()->constructor(

    ),
];
