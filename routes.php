<?php
$dir = __DIR__ ;
require_once __DIR__ .'/router.php';
if ( $dir == '/home/mylanguagenet/api.mylanguage.net.au'){
    require_once $dir .'/App/Configuration/.env.remote.php';
}
else{
    require_once $dir .'/App/Configuration/.env.local.php';
}
require_once  __DIR__.'/App/Configuration/my-autoload.inc.php';
##################################################
require_once __DIR__.'/App/Includes/writeLog.php';

get(WEB_ROOT . 'remote', 'App/views/indexRemote.php');

//API
get(WEB_ROOT . 'api/test/passage', 'App/API/BiblePassages/passageTest.php');

get(WEB_ROOT . 'api/ask/$languageCodeHL', 'App/API/askQuestions.php');
get(WEB_ROOT . 'api/bibles/$languageCodeHL', 'App/API/Bibles/biblesForLanguage.php');
get(WEB_ROOT . 'api/bibles/dbs/next/$languageCodeHL','App/API/Bibles/bibleForDbsNext.php');

get(WEB_ROOT . 'api/content/available/$languageCodeHL1/$languageCodeHL2', 'App/API/contentAvailable.php');

get(WEB_ROOT . 'api/createQrCode', 'App/API/createQrCode.php');

get(WEB_ROOT . 'api/dbs/languages', 'App/API/BibleStudies/dbsLanguageOptions.php');
get(WEB_ROOT . 'api/dbs/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/dbsMonolingualPdf.php');
get(WEB_ROOT . 'api/dbs/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/dbsBilingualPdf.php');
get(WEB_ROOT . 'api/dbs/studies', 'App/API/BibleStudies/dbsStudyOptions.php');
get(WEB_ROOT . 'api/dbs/studies/$languageCodeHL1', 'App/API/BibleStudies/dbsStudyOptions.php');
get(WEB_ROOT . 'api/dbs/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/dbsMonolingualView.php');
get(WEB_ROOT . 'api/dbs/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/dbsBilingualView.php');

get(WEB_ROOT . 'api/followingjesus/segments/$languageCodeHL', 'App/API/Videos/followingJesusOptions.php');

get(WEB_ROOT . 'api/jvideo/questions/$languageCodeHL', 'App/API/Videos/jVideoQuestionsMonolingual.php');
get(WEB_ROOT . 'api/jvideo/questions/$languageCodeHL1/$languageCodeHL2', 'App/API/Videos/jVideoQuestionsBilingual.php');
get(WEB_ROOT . 'api/jvideo/segments/$languageCodeHL/$languageCodeJF', 'App/API/Videos/jVideoSegments.php');
get(WEB_ROOT . 'api/jvideo/source/$segment/$languageCodeJF', 'App/API/Videos/jVideoSource.php');

get(WEB_ROOT . 'api/language/$languageCodeHL', 'App/API/Languages/languageDetails.php');
get(WEB_ROOT . 'api/language/languageCodeJF/$languageCodeHL', 'App/API/Languages/languageCodeJF.php');
get(WEB_ROOT . 'api/language/languageCodeJFFollowingJesus/$languageCodeHL', 'App/API/Languages/languageCodeJFFollowingJesus.php');
get(WEB_ROOT . 'api/languages/hindi', 'App/API/Languages/hindiLanguageOptions.php');
get(WEB_ROOT . 'api/languages/country/$countryCode', 'App/API/Languages/languagesForCountry.php');

get(WEB_ROOT . 'api/leadership/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/leadershipMonolingualPdf.php');
get(WEB_ROOT . 'api/leadership/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/leadershipBilingualPdf.php');
get(WEB_ROOT . 'api/leadership/studies', 'App/API/BibleStudies/leadershipStudyOptions.php');
get(WEB_ROOT . 'api/leadership/studies/$languageCodeHL1', 'App/API/BibleStudies/leadershipStudyOptions.php');
get(WEB_ROOT . 'api/leadership/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/leadershipMonolingualView.php');
get(WEB_ROOT . 'api/leadership/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/leadershipBilingualView.php');

get(WEB_ROOT . 'api/life_principles/pdf/$lesson/$languageCodeHL1', 'App/API/BibleStudies/lifeMonolingualPdf.php');
get(WEB_ROOT . 'api/life_principles/pdf/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/lifeBilingualPdf.php');
get(WEB_ROOT . 'api/life_principles/studies', 'App/API/BibleStudies/lifeStudyOptions.php');
get(WEB_ROOT . 'api/life_principles/studies/$languageCodeHL1', 'App/API/BibleStudies/lifeStudyOptions.php');
get(WEB_ROOT . 'api/life_principles/view/$lesson/$languageCodeHL1', 'App/API/BibleStudies/lifeMonolingualView.php');
get(WEB_ROOT . 'api/life_principles/view/$lesson/$languageCodeHL1/$languageCodeHL2', 'App/API/BibleStudies/lifeBilingualView.php');

// cjecl from here


get(WEB_ROOT . 'api/followingjesus/videocode/$languageCodeHL' , 'App/API/videoCodeForFollowingJesus.php');

get(WEB_ROOT . 'api/gospel/languages', 'App/API/Gospel/gospelLanguageOptions.php');
get(WEB_ROOT . 'api/gospel/view/$page', 'App/API/gospelPage.php');


get(WEB_ROOT . 'api/video/code/$title/$languageCodeHL', 'App/API/videoCodeFromTitle.php');


post(WEB_ROOT .'api/passage/text', 'App/API/passageForBible.php');


if (WEB_ROOT == '/mylanguage-namespaced/'){
  //  get(WEB_ROOT , 'App/Views/index.php');
    get(WEB_ROOT , 'App/Views/indexLocal.php');
    get(WEB_ROOT . 'local', '/App/Views/indexLocal.php');
    post(WEB_ROOT . 'api/secure/bibles/weight/change', 'App/API/secure/bibleWeightChange.php');
    // Imports

    get(WEB_ROOT . 'import/bible/externalId', 'imports/updateBibleExternalId.php');
    get(WEB_ROOT . 'import/bible/languages', 'imports/addHLCodeToBible.php');
    get(WEB_ROOT . 'import/bibleBookNames/languages', 'imports/addHLCodeToBibleBookNames.php');
    get(WEB_ROOT . 'import/biblebrain/setup', 'imports/clearBibleBrainCheckDate.php');
    get(WEB_ROOT . 'import/biblebrain/bibles', 'imports/addBibleBrainBibles.php');
    get(WEB_ROOT . 'import/biblebrain/languages','imports/addBibleBrainLanguages.php');
    get(WEB_ROOT . 'import/biblebrain/language/details','imports/updateBibleBrainLanguageDetails.php');
    get(WEB_ROOT . 'import/biblegateway/bibles', 'imports/addBibleGatewayBibles.php');

    get(WEB_ROOT . 'import/country/languages/africa', 'imports/importLanguagesAfrica.php');
    get(WEB_ROOT . 'import/country/languages/jproject', 'imports/importLanguagesJProject.php');
    get(WEB_ROOT . 'import/country/languages/jproject2', 'imports/importLanguagesJProject2.php');
    get(WEB_ROOT . 'import/country/names', 'imports/checkCountryNames.php');
    get(WEB_ROOT . 'import/country/names/language', 'imports/addCountryNamesToLanguage.php');
    get(WEB_ROOT . 'import/country/names/language2', 'imports/addCountryNamesToLanguage2.php');
 
    get(WEB_ROOT . 'import/dbs/database', 'imports/UpdateDbsLanguages.php');
    get(WEB_ROOT . 'import/india', 'imports/importIndiaVideos.php');
    get(WEB_ROOT . 'import/leadership/database', 'imports/importLeadershipStudies.php');

    get(WEB_ROOT . 'import/life', 'imports/importLifePrinciples.php');
    get(WEB_ROOT . 'import/lumo', 'imports/importLumoVideos.php');
    get(WEB_ROOT . 'import/lumo/clean', 'imports/LumoClean.php');
    get(WEB_ROOT . 'import/lumo/segments', 'imports/LumoSegmentsAdd.php');
    get(WEB_ROOT . 'import/tracts', 'imports/bilingualTractsVerify.php');
    get(WEB_ROOT . 'import/video/segments', 'imports/importJesusVideoSegments.php');
    get(WEB_ROOT . 'import/video/segments/clean', 'imports/JFSegmentsClean.php');
    get(WEB_ROOT . 'import/video/languages', 'imports/videoLanguageCodesForJF.php');
    get(WEB_ROOT . 'import/video/jvideo/endTime', 'imports/addJVideoSegmentEndTime.php');

    get(WEB_ROOT . 'translate/dbs/words', 'translations/importRoutines/importDbsTranslationFromGoogle.php');
    get(WEB_ROOT . 'translate/leadership/words', 'translations/importRoutines/importLeadershipTranslationFromGoogle.php');
    get(WEB_ROOT . 'translate/life/words', 'translations/importRoutines/importLifePrincipleTranslationFromGoogle.php');
    get(WEB_ROOT . 'translate/video/words', 'translations/importRoutines/importVideoSegmentTranslationFromGoogle.php');
    get(WEB_ROOT . 'translate/words/$languageCodeBing1/$languageCodeBing2', 'App/API/translation/translateWordsThroughBing.php');


    // TESTS
    get(WEB_ROOT . 'tests/createQrCode',  'App/Tests/createQrCode.php');
    get(WEB_ROOT . 'test',  'App/Tests/test.php');

    //  Web Access
    get(WEB_ROOT . 'webpage',  'App/Tests/webpage.php');

    // word
    get(WEB_ROOT . 'test/word/passage/$externalId', 'App/Tests/canGetBibleWordPassageFromExternalId.php');

    // Bible Brain
    get(WEB_ROOT . 'test/biblebrain/language',  'App/Tests/canGetBibleBrainLanguageDetails.php');
    get(WEB_ROOT . 'test/biblebrain/bible/default',  'App/Tests/canGetBestBibleFromBibleBrain.php');
    get(WEB_ROOT . 'test/biblebrain/bible/formats',  'App/Tests/canGetBibleBrainBibleFormatTypes.php');
    get(WEB_ROOT . 'test/biblebrain/passage/json',  'App/Tests/canGetBibleBrainPassageTextJson.php');

    get(WEB_ROOT . 'test/biblebrain/passage/formatted', 'App/Tests/canGetBibleBrainPassageTextFormatted.php');
    get(WEB_ROOT . 'test/biblebrain/passage/usx', 'App/Tests/canGetBibleBrainPassageTextUsx.php');
    get(WEB_ROOT . 'test/biblebrain/languages/country',  'App/Tests/canGetLanguagesForCountryCode.php');

    // Bible Gateway
    get(WEB_ROOT . 'test/biblegateway',  'App/Tests/canGetBibleGatewayPassage.php');

    //YouVersion
    get(WEB_ROOT . 'test/youversion/link',  'App/Tests/canGetBibleYouVersionLink.php');

    // DBS
    get(WEB_ROOT . 'test/dbs/translation',  'App/Tests/canGetDBSTranslation.php');
    get(WEB_ROOT . 'test/dbs/bilingual',  'App/Tests/canMakeStandardBilingualDBS.php');
    get(WEB_ROOT . 'test/dbs/pdf',  'App/Tests/canPrintDbsPdf.php');


    //Bibles
    get (WEB_ROOT. 'test/bibles/best',  'App/Tests/canGetBestBibleByLanguageCodeHL.php');
    get (WEB_ROOT. 'test/passage/select',  'App/Tests/passageSelectControllerTest.php');
    get(WEB_ROOT . 'test/bible',  'App/Tests/biblePassageControllerTest.php');

    //Database
    get(WEB_ROOT . 'test/language/hl',  'App/Tests/canGetLanguageFromHL.php');





    get(WEB_ROOT . 'test/bible/reference/info',  'App/Tests/CanCreateBibleReferenceInfo.php');
    get(WEB_ROOT . 'test/passage/select',  'App/Tests/canSelectBiblePassageFromDatabaseOrExternal.php');

    get(WEB_ROOT . 'test/passage/stored',  'App/Tests/canSeePassageStored.php');

}
// any can be used for GETs or POSTs

// For GET or POST
// The 404.php which is inside the tests folder will be called
// The 404.php has access to $_GET and $_POST
any('/404','/App/Views/404.php');
