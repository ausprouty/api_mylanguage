<?php

use FastRoute\RouteCollector;
use App\Configuration\Config;
use App\Responses\JsonResponse;
use App\Utilities\Logger;

return function (RouteCollector $r) {
    // Base path from your $basePath
    $basePath = Config::get('base_path');

    $container = require __DIR__ . '/../Configuration/container.php';

    // New Routes
    $r->addGroup($basePath . 'api/bible', function (RouteCollector $group) use ($container) {
        $group->addRoute('GET', '/best/{languageCodeHL}', function ($params) use ($container) {
            return $container->get(App\Controllers\BibleController::class)
                ->webGetBestBibleByLanguageCodeHL($params['languageCodeHL']);
        });
    });
    $r->addGroup($basePath . 'api/study', function (RouteCollector $group) use ($container) {
        $group->addRoute('GET', '/dbs/languages', function () use ($container) {
            return $container->get(App\Controllers\Language\DbsLanguageReadController::class)
                ->webGetLanguagesWithCompleteBible();
        });
        $group->addRoute('GET', '/dbs/languages/summary', function () use ($container) {
            return $container->get(App\Controllers\Language\DbsLanguageReadController::class)
                ->webGetSummaryOfLanguagesWithCompleteBible();
        });
        $group->addRoute('GET', '/dbsandjvideo/languages/summary', function () use ($container) {
            return $container->get(App\Controllers\Language\DbsLanguageReadController::class)
                ->webGetSummaryOfLanguagesWithCompleteBibleAndJVideo();
        });
        $group->addRoute('GET', '/titles/{study}/{languageCodeHL}', function () use ($container) {
            return $container->get(App\Controllers\BibleStudy\StudyTitleController::class)
                ->webGetTitleForStudy();
        });
        $group->addRoute('GET', '/{study}/{format}/{session}/{language1}[/{language2}]', function ($args) use ($container) {
            // Get the controller instance from the DI container
            $controller = $container->get(App\Controllers\BibleStudyController::class);
            // Call the method with the route arguments
            return $controller->webRequestToFetchStudy($args);
        });
    });
    // translate
    $r->addGroup($basePath . 'api/translate', function (RouteCollector $group) use ($container) {
        $group->addRoute('GET', '/cron/{token}', function ($args) use ($container) {
            $processor = $container->get(\App\Cron\TranslationQueueProcessor::class);
            $processor->runIfAuthorized($args['token']);
        });
        $group->addRoute('GET', '/interface/{languageCodeHL}/{app}', function ($args) use ($container) {
            $controller = $container->get(App\Controllers\TranslationFetchController::class);
            return $controller->webFetchInterface($args);
        });
        $group->addRoute('GET', '/commonContent/{languageCodeHL}/{study}', function ($args) use ($container) {
            $controller = $container->get(App\Controllers\TranslationFetchController::class);
            return $controller->webFetchCommonContent($args);
        });
        $group->addRoute('GET', '/lessonContent/{languageCodeHL}/{languageCodeJF}/{study}/{lesson}', function ($args) use ($container) {
            $controller = $container->get(App\Controllers\BibleStudyJsonController::class);
            return $controller->webFetchLessonContent($args);
        });

        $group->addRoute('GET', '/lessonContent/{languageCodeHL}/{study}/{lesson}', function ($args) use ($container) {
            $controller = $container->get(App\Controllers\BibleStudyJsonController::class);
            return $controller->webFetchLessonContent($args);
        });

        $group->addRoute('GET', '/lessonContent/ping', function () {
            error_log("🔔 lessonContent/ping route hit!");
            return new \App\Responses\JsonResponse(['ping' => 'pong']);
        });

        $group->addRoute('GET', '/videoUrls/jvideo/{languageCodeJF}', function ($args) use ($container) {
            $controller = $container->get(App\Controllers\Video\JesusVideoUrlController::class);
            return $controller->webFetchJesusVideoUrls($args);
        });
    });

    // DBS Group
    $r->addGroup($basePath . 'api/dbsx', function (RouteCollector $group) {
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/dbsMonolingualPdf.php');
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/dbsBilingualPdf.php');
        $group->addRoute('GET', '/studies', 'App/API/BibleStudies/dbsStudyOptions.php');
        $group->addRoute('GET', '/studies/{languageCodeHL1}', 'App/API/BibleStudies/dbsStudyOptions.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/dbsBilingualView.php');
    });

    $r->addRoute('GET', $basePath . 'api/languages/dbs/next/{languageCodeHL}', function ($params) {
        return App\Services\Language\LanguageLookupService::getNextLanguageForDbs($params['languageCodeHL']);
    });
    // Add a route for the root path
    $r->addRoute('GET', $basePath, function () {
        return new JsonResponse(['message' => 'Welcome to the API!']);
    });
    $r->addRoute('GET', '/{any:.*}', function () {
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        error_log("❌ Route not matched: $uri");
        return new JsonResponse(['error' => 'No matching route']);
    });
    /*   // Legacy Routes
    $r->addRoute('GET', $basePath . 'remote', 'App/views/indexRemote.php');
    $r->addRoute('GET', $basePath . 'tests', 'App/views/indexTests.php');

    // API Routes Grouping

    // Bible Passages
    $r->addGroup($basePath . 'api', function (RouteCollector $group) {
        $group->addRoute('GET', '/test/passage', 'App/API/BiblePassages/passageTest.php');
        $group->addRoute('GET', '/ask/{languageCodeHL}', 'App/API/askQuestions.php');
        $group->addRoute('GET', '/bibles/{languageCodeHL}', 'App/API/Bibles/biblesForLanguage.php');
        $group->addRoute('GET', '/bibles/dbs/next/{languageCodeHL}', 'App/API/Bibles/bibleForDbsNext.php');
        $group->addRoute('GET', '/content/available/{languageCodeHL1}/{languageCodeHL2}', 'App/API/contentAvailable.php');
        $group->addRoute('GET', '/createQrCode', 'App/API/createQrCode.php');
    });

    // DBS Group
    $r->addGroup($basePath . 'api/dbs', function (RouteCollector $group) {
        $group->addRoute('GET', '/languages', 'App/API/BibleStudies/dbsLanguageOptions.php');
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/dbsMonolingualPdf.php');
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/dbsBilingualPdf.php');
        $group->addRoute('GET', '/studies', 'App/API/BibleStudies/dbsStudyOptions.php');
        $group->addRoute('GET', '/studies/{languageCodeHL1}', 'App/API/BibleStudies/dbsStudyOptions.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/dbsMonolingualView.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/dbsBilingualView.php');
    });

    // Video Options
    $r->addGroup($basePath . 'api', function (RouteCollector $group) {
        $group->addRoute('GET', '/followingjesus/segments/{languageCodeHL}', 'App/API/Videos/followingJesusOptions.php');
        $group->addRoute('GET', '/jvideo/questions/{languageCodeHL}', 'App/API/Videos/jVideoQuestionsMonolingual.php');
        $group->addRoute('GET', '/jvideo/questions/{languageCodeHL1}/{languageCodeHL2}', 'App/API/Videos/jVideoQuestionsBilingual.php');
        $group->addRoute('GET', '/jvideo/segments/{languageCodeHL}/{languageCodeJF}', 'App/API/Videos/jVideoSegments.php');
        $group->addRoute('GET', '/jvideo/source/{segment}/{languageCodeJF}', 'App/API/Videos/jVideoSource.php');
    });
    // Language Options
    $r->addGroup($basePath . 'api/language', function (RouteCollector $group) {
        $group->addRoute('GET', '/{languageCodeHL}', 'App/API/Languages/languageDetails.php');
        $group->addRoute('GET', '/languageCodeJF/{languageCodeHL}', 'App/API/Languages/languageCodeJF.php');
        $group->addRoute('GET', '/languageCodeJFFollowingJesus/{languageCodeHL}', 'App/API/Languages/languageCodeJFFollowingJesus.php');
    });

    // Leadership Studies
    $r->addGroup($basePath . 'api/leadership', function (RouteCollector $group) {
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/leadershipMonolingualPdf.php');
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/leadershipBilingualPdf.php');
        $group->addRoute('GET', '/studies', 'App/API/BibleStudies/leadershipStudyOptions.php');
        $group->addRoute('GET', '/studies/{languageCodeHL1}', 'App/API/BibleStudies/leadershipStudyOptions.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/leadershipMonolingualView.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/leadershipBilingualView.php');
    });

    // Life Principles
    $r->addGroup($basePath . 'api/life_principles', function (RouteCollector $group) {
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/lifeMonolingualPdf.php');
        $group->addRoute('GET', '/pdf/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/lifeBilingualPdf.php');
        $group->addRoute('GET', '/studies', 'App/API/BibleStudies/lifeStudyOptions.php');
        $group->addRoute('GET', '/studies/{languageCodeHL1}', 'App/API/BibleStudies/lifeStudyOptions.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}', 'App/API/BibleStudies/lifeMonolingualView.php');
        $group->addRoute('GET', '/view/{lesson}/{languageCodeHL1}/{languageCodeHL2}', 'App/API/BibleStudies/lifeBilingualView.php');
    });
    // Conditionally include specific routes
    if ($_ENV['LOAD_IMPORT_ROUTES'] ?? false) {
        $importRoutes = include 'routes_import.php';
        $importRoutes($r); // Add import routes
    }

    if ($_ENV['LOAD_TEST_ROUTES'] ?? false) {
        $testRoutes = include 'routes_test.php';
        $testRoutes($r); // Add test routes
    }

    // Separate route for /tests
    $r->addGroup($basePath . 'tests', function (RouteCollector $group) {
        $group->addRoute('GET', '/createQrCode', 'App/Tests/createQrCode.php');
    });

    // Separate route for /webpage
    $r->addRoute('GET', $basePath . 'webpage', 'App/Tests/webpage.php');

    // Fallback Route for 404
    $r->addRoute('ANY', '/404', 'App/Views/404.php');
*/
};
