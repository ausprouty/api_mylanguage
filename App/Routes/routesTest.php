<?php

// Tests Group
$r->addGroup(Config::get('web.web_root') . 'test', function (RouteCollector $group) {
    $group->addRoute('GET', '', 'App/Tests/test.php');
    $group->addRoute('GET', '/bible', 'App/Tests/biblePassageControllerTest.php');
    $group->addRoute('GET', '/bible/reference/info', 'App/Tests/CanCreateBibleReferenceInfo.php');
    $group->addRoute('GET', '/biblebrain/bible/best', 'App/Tests/canGetBestBibleFromBibleBrain.php');
    $group->addRoute('GET', '/biblebrain/bible/default', 'App/Tests/canGetDefaultBibleForLanguage.php');
    $group->addRoute('GET', '/biblebrain/bible/formats', 'App/Tests/canGetBibleBrainBibleFormatTypes.php');
    $group->addRoute('GET', '/biblebrain/language', 'App/Tests/canGetBibleBrainLanguageDetails.php');
    $group->addRoute('GET', '/biblebrain/languages/countrycode', 'App/Tests/canGetLanguagesForCountryCode.php');
    $group->addRoute('GET', '/biblebrain/languages/country', 'App/Tests/canGetLanguagesForCountry.php');
    $group->addRoute('GET', '/biblebrain/passage/formatted', 'App/Tests/canGetBibleBrainPassageTextFormatted.php');
    $group->addRoute('GET', '/bible/passage/formatted', 'App/Tests/canGetBibleTextFormatted.php');
    $group->addRoute('GET', '/biblebrain/passage/json', 'App/Tests/canGetBibleBrainPassageTextJson.php');
    $group->addRoute('GET', '/bible/passage/json', 'App/Tests/canGetBibleTextJson.php');
    $group->addRoute('GET', '/biblebrain/passage/plain', 'App/Tests/canGetBibleTextPlain.php');
    $group->addRoute('GET', '/biblebrain/passage/usx', 'App/Tests/canGetBibleBrainPassageTextUsx.php');
    $group->addRoute('GET', '/bible/passage/usx', 'App/Tests/canGetBibleTextUsx.php');
    $group->addRoute('GET', '/biblegateway', 'App/Tests/canGetBibleGatewayPassage.php');
    $group->addRoute('GET', '/bibles/best', 'App/Tests/canGetBestBibleForLanguage.php');
    $group->addRoute('GET', '/bibles/best/hl', 'App/Tests/canGetBestBibleByLanguageCodeHL.php');
    $group->addRoute('GET', '/dbs/bilingual', 'App/Tests/canMakeStandardBilingualDBS.php');
    $group->addRoute('GET', '/dbs/pdf', 'App/Tests/canPrintDbsPdf.php');
    $group->addRoute('GET', '/dbs/translation', 'App/Tests/canGetDBSTranslation.php');
    $group->addRoute('GET', '/language/hl', 'App/Tests/canGetLanguageFromHL.php');
    $group->addRoute('GET', '/passage/select/source', 'App/Tests/canSelectBiblePassageFromDatabaseOrExternal.php');
    $group->addRoute('GET', '/passage/select/controller', 'App/Tests/passageSelectControllerTest.php');
    $group->addRoute('GET', '/passage/stored', 'App/Tests/canSeePassageStored.php');
    $group->addRoute('GET', '/word/passage/ex', 'App/Tests/canGetBibleWordPassageFromExternalId.php');
    $group->addRoute('GET', '/word/passage/external', 'App/Tests/canGetWordPassageFromExternalId.php');
    $group->addRoute('GET', '/youversion/link', 'App/Tests/canGetBibleYouVersionLink.php');
});
