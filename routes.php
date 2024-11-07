<?php
$dir = __DIR__ ;
require_once __DIR__ .'/router.php';

##################################################
$path = WEB_ROOT;

get($path  . 'remote', 'App/views/indexRemote.php');

//API
get($path  . 'api/test/passage', 'App/API/BiblePassages/passageTest.php');

get($path  . 'api/ask/$languageCodeHL', 'App/API/askQuestions.php');
get($path  . 'api/bibles/$languageCodeHL', 'App/API/Bibles/biblesForLanguage.php');
get($path  . 'api/bibles/dbs/next/$languageCodeHL','App/API/Bibles/bibleForDbsNext.php');

get($path  . 'api/content/available/$languageCodeHL1/$languageCodeHL2', 'App/API/contentAvailable.php');

get($path  . 'api/createQrCode', 'App/API/createQrCode.php');

get($path  . 'api/dbs/languages', 'App/API/BibleStudies/dbsLanguageOptions.php');
get($path  . 'api/dbs/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/dbsMonolingualPdf.php');
get($path  . 'api/dbs/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/dbsBilingualPdf.php');
get($path  . 'api/dbs/studies', 'App/API/BibleStudies/dbsStudyOptions.php');
get($path  . 'api/dbs/studies/$languageCodeHL1', 'App/API/BibleStudies/dbsStudyOptions.php');
get($path  . 'api/dbs/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/dbsMonolingualView.php');
get($path  . 'api/dbs/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/dbsBilingualView.php');

get($path  . 'api/followingjesus/segments/$languageCodeHL', 'App/API/Videos/followingJesusOptions.php');

get($path  . 'api/jvideo/questions/$languageCodeHL', 'App/API/Videos/jVideoQuestionsMonolingual.php');
get($path  . 'api/jvideo/questions/$languageCodeHL1/$languageCodeHL2', 'App/API/Videos/jVideoQuestionsBilingual.php');
get($path  . 'api/jvideo/segments/$languageCodeHL/$languageCodeJF', 'App/API/Videos/jVideoSegments.php');
get($path  . 'api/jvideo/source/$segment/$languageCodeJF', 'App/API/Videos/jVideoSource.php');

get($path  . 'api/language/$languageCodeHL', 'App/API/Languages/languageDetails.php');
get($path  . 'api/language/languageCodeJF/$languageCodeHL', 'App/API/Languages/languageCodeJF.php');
get($path  . 'api/language/languageCodeJFFollowingJesus/$languageCodeHL', 'App/API/Languages/languageCodeJFFollowingJesus.php');
get($path  . 'api/languages/hindi', 'App/API/Languages/hindiLanguageOptions.php');
get($path  . 'api/languages/country/$countryCode', 'App/API/Languages/languagesForCountry.php');

get($path  . 'api/leadership/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/leadershipMonolingualPdf.php');
get($path  . 'api/leadership/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/leadershipBilingualPdf.php');
get($path  . 'api/leadership/studies', 'App/API/BibleStudies/leadershipStudyOptions.php');
get($path  . 'api/leadership/studies/$languageCodeHL1', 'App/API/BibleStudies/leadershipStudyOptions.php');
get($path  . 'api/leadership/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/leadershipMonolingualView.php');
get($path  . 'api/leadership/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/leadershipBilingualView.php');

get($path  . 'api/life_principles/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/lifeMonolingualPdf.php');
get($path  . 'api/life_principles/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/lifeBilingualPdf.php');
get($path  . 'api/life_principles/studies', 'App/API/BibleStudies/lifeStudyOptions.php');
get($path  . 'api/life_principles/studies/$languageCodeHL1', 'App/API/BibleStudies/lifeStudyOptions.php');
get($path  . 'api/life_principles/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/lifeMonolingualView.php');
get($path  . 'api/life_principles/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/lifeBilingualView.php');

// cjecl from here


get($path  . 'api/followingjesus/videocode/$languageCodeHL' , 'App/API/videoCodeForFollowingJesus.php');

get($path  . 'api/gospel/languages', 'App/API/Gospel/gospelLanguageOptions.php');
get($path  . 'api/gospel/view/$page', 'App/API/gospelPage.php');


get($path  . 'api/video/code/$title/$languageCodeHL', 'App/API/videoCodeFromTitle.php');


post($path  .'api/passage/text', 'App/API/passageForBible.php');


if (ENVIRONMENT == 'local') {
    get($path  , 'App/Views/indexLocal.php');
    get($path  . 'local', '/App/Views/indexLocal.php');
    post($path  . 'api/secure/bibles/weight/change', 'App/API/secure/bibleWeightChange.php');
    // Imports

    get($path  . 'import/bible/externalId', 'imports/updateBibleExternalId.php');
    get($path  . 'import/bible/languages', 'imports/addHLCodeToBible.php');
    get($path  . 'import/bibleBookNames/languages', 'imports/addHLCodeToBibleBookNames.php');
    get($path  . 'import/biblebrain/setup', 'imports/clearBibleBrainCheckDate.php');
    get($path  . 'import/biblebrain/bibles', 'imports/addBibleBrainBibles.php');
    get($path  . 'import/biblebrain/languages','imports/addBibleBrainLanguages.php');
    get($path  . 'import/biblebrain/language/details','imports/updateBibleBrainLanguageDetails.php');
    get($path  . 'import/biblegateway/bibles', 'imports/addBibleGatewayBibles.php');

    get($path  . 'import/country/languages/africa', 'imports/importLanguagesAfrica.php');
    get($path  . 'import/country/languages/jproject', 'imports/importLanguagesJProject.php');
    get($path  . 'import/country/languages/jproject2', 'imports/importLanguagesJProject2.php');
    get($path  . 'import/country/names', 'imports/checkCountryNames.php');
    get($path  . 'import/country/names/language', 'imports/addCountryNamesToLanguage.php');
    get($path  . 'import/country/names/language2', 'imports/addCountryNamesToLanguage2.php');
 
    get($path  . 'import/dbs/database', 'imports/UpdateDbsLanguages.php');
    get($path  . 'import/india', 'imports/importIndiaVideos.php');
    get($path  . 'import/leadership/database', 'imports/importLeadershipStudies.php');

    get($path  . 'import/life', 'imports/importLifePrinciples.php');
    get($path  . 'import/lumo', 'imports/importLumoVideos.php');
    get($path  . 'import/lumo/clean', 'imports/LumoClean.php');
    get($path  . 'import/lumo/segments', 'imports/LumoSegmentsAdd.php');
    get($path  . 'import/tracts', 'imports/bilingualTractsVerify.php');
    get($path  . 'import/video/segments', 'imports/importJesusVideoSegments.php');
    get($path  . 'import/video/segments/clean', 'imports/JFSegmentsClean.php');
    get($path  . 'import/video/languages', 'imports/videoLanguageCodesForJF.php');
    get($path  . 'import/video/jvideo/endTime', 'imports/addJVideoSegmentEndTime.php');

    get($path  . 'translate/dbs/words', 'translations/importRoutines/importDbsTranslationFromGoogle.php');
    get($path  . 'translate/leadership/words', 'translations/importRoutines/importLeadershipTranslationFromGoogle.php');
    get($path  . 'translate/life/words', 'translations/importRoutines/importLifePrincipleTranslationFromGoogle.php');
    get($path  . 'translate/video/words', 'translations/importRoutines/importVideoSegmentTranslationFromGoogle.php');
    get($path  . 'translate/words/$languageCodeBing1/$languageCodeBing2', 'App/API/translation/translateWordsThroughBing.php');


    // TESTS
    get($path  . 'tests/createQrCode',  'App/Tests/createQrCode.php');
    get($path  . 'test',  'App/Tests/test.php');

    //  Web Access
    get($path  . 'webpage',  'App/Tests/webpage.php');

    // word
    get($path  . 'test/word/passage/$externalId', 'App/Tests/canGetBibleWordPassageFromExternalId.php');

    // Bible Brain
    get($path  . 'test/biblebrain/language',  'App/Tests/canGetBibleBrainLanguageDetails.php');
    get($path  . 'test/biblebrain/bible/default',  'App/Tests/canGetBestBibleFromBibleBrain.php');
    get($path  . 'test/biblebrain/bible/formats',  'App/Tests/canGetBibleBrainBibleFormatTypes.php');
    get($path  . 'test/biblebrain/passage/json',  'App/Tests/canGetBibleBrainPassageTextJson.php');

    get($path  . 'test/biblebrain/passage/formatted', 'App/Tests/canGetBibleBrainPassageTextFormatted.php');
    get($path  . 'test/biblebrain/passage/usx', 'App/Tests/canGetBibleBrainPassageTextUsx.php');
    get($path  . 'test/biblebrain/languages/country',  'App/Tests/canGetLanguagesForCountryCode.php');

    // Bible Gateway
    get($path  . 'test/biblegateway',  'App/Tests/canGetBibleGatewayPassage.php');

    //YouVersion
    get($path  . 'test/youversion/link',  'App/Tests/canGetBibleYouVersionLink.php');

    // DBS
    get($path  . 'test/dbs/translation',  'App/Tests/canGetDBSTranslation.php');
    get($path  . 'test/dbs/bilingual',  'App/Tests/canMakeStandardBilingualDBS.php');
    get($path  . 'test/dbs/pdf',  'App/Tests/canPrintDbsPdf.php');


    //Bibles
    get($path. 'test/bibles/best',  'App/Tests/canGetBestBibleByLanguageCodeHL.php');
    get($path. 'test/passage/select',  'App/Tests/passageSelectControllerTest.php');
    get($path  . 'test/bible',  'App/Tests/biblePassageControllerTest.php');

    //Database
    get($path  . 'test/language/hl',  'App/Tests/canGetLanguageFromHL.php');



    get($path  . 'test/bible/reference/info',  'App/Tests/CanCreateBibleReferenceInfo.php');
    get($path  . 'test/passage/select',  'App/Tests/canSelectBiblePassageFromDatabaseOrExternal.php');

    get($path  . 'test/passage/stored',  'App/Tests/canSeePassageStored.php');

}
// any can be used for GETs or POSTs

// For GET or POST
// The 404.php which is inside the tests folder will be called
// The 404.php has access to $_GET and $_POST
any('/404','/App/Views/404.php');
