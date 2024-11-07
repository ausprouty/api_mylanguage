<?php
writeLogDebug('indexLocal', 'We are in IndexLocal');

echo "
<p><a href= '/mylanguage-namespaced/translate/words/eng/deu'>Test Translation</a></p>
<p><a href= '/mylanguage-namespaced/tests/createQrCode'>CreateQRCode</a></p>

<p><a href = '/mylanguage-namespaced/api/life/view/2/eng00/frn00'>Principles 2 French-English</a></p>

<h2>DBS</h2>
<p><a href = '/import/bible/externalId'>Update ExternalID</a></p>
<p><a href = '/mylanguage-namespaced/test/dbs/bilingual'>Bi-lingual DBS</a></p>
<p><a href = '/mylanguage-namespaced/test/dbs/pdf'>Can Create Pdf</a></p>


<h1>API</h1>
<p><a href = '/mylanguage-namespaced/api/dbs/languages'>Language options for DBS</a></p>
<p><a href = '/mylanguage-namespaced/api/dbs/studies'>Study options for DBS</a></p>
<p><a href = '/mylanguage-namespaced/api/bibles/eng'>Bibles for Language</a></p>
<p><a href = '/mylanguage-namespaced/api/dbs/2/eng00/frn00'>DBS 2 French-English</a></p>
<p><a href = '/mylanguage-namespaced/api/dbs2/2/eng00/frn00'>New DBS 2 French-English</a></p>

<h1> Translation </h1>
<p><a href = '/mylanguage-namespaced/translate/video/words'>Import Translation of JESUS video words</a></p>
<p><a href = '/mylanguage-namespaced/translate/dbs/words'>Import Translation of DBS words</a></p>
<p><a href = '/mylanguage-namespaced/translate/life/words'>Import Translation of Life Principle words</a></p>
<p><a href = '/mylanguage-namespaced/translate/leadership/words'>Import Translation of Leadership words</a></p>

<h1> Import Routines for MyLanguage OOPHP</h1>
<p><a href = '/mylanguage-namespaced/import/country/languages/jproject'>Import Country Languages from JProject</a></p>
<p><a href = '/mylanguage-namespaced/import/country/languages/jproject2'>Import Country Languages from JProject2</a></p>

<p><a href = '/mylanguage-namespaced/import/lumo'>Import Lumo Videos</a></p>

<p><a href = '/mylanguage-namespaced/import/tracts'>Verify Bi-lingual Tract Information</a></p>
<p><a href = '/mylanguage-namespaced/import/bible/externalId'>Update ExternalID</a></p>

<p><a href = '/mylanguage-namespaced/import/bible/languages'>Import HL Language Codes into Bibles</a></p>
<p><a href = '/mylanguage-namespaced/import/bibleBookNames/languages'>Import HL Language Codes into Bible Book Names</a></p>


<p><a href = '/mylanguage-namespaced/import/biblebrain/languages'>Import Languages from BibleBrain</a></p>
<br>
<p><a href = '/mylanguage-namespaced/import/biblebrain/setup'>Reset Bible Brain Check Date for below</a></p>
<p><a href = '/mylanguage-namespaced/import/biblebrain/bibles'>Import Bibles from BibleBrain</a></p>
<p><a href = '/mylanguage-namespaced/import/biblegateway/bibles'>Import Bibles from BibleGateway</a></p>
<p><a href = '/mylanguage-namespaced/import/dbs/words'>Import DBS Words from local file</a></p>
<p><a href = '/mylanguage-namespaced/import/dbs/database'>Update DBS Database to show languages we have questions and Bibles for</a></p>


<h1> Tests for MyLanguage OOPHP</h1>
<p><a href= '/mylanguage-namespaced/test'>Current Test</a></p>

<h2>Web Access</h2>
<p><a href= '/mylanguage-namespaced/webpage'>Access Webpage -OK</a></p>

<h2>Bible Brain</h2>

<p><a href = '/mylanguage-namespaced/test/biblebrain/languages/country'>Languages for Country -OK</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/bible/default'>Default Bible for Language -OK</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/bible/formats'>Format Types -OK<</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/passage/json'>Bible Text Json -OK</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/passage/plain'>Bible Text Plain</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/passage/formatted'>Bible Text Formatted -OK</a></p>
<p><a href = '/mylanguage-namespaced/test/biblebrain/passage/usx'>Bible Text USX</a></p>

<h2>Bible Gateway</h2>
<p> <a href= '/mylanguage-namespaced/test/biblegateway'>Get Passage -OK</a></p>
<h2>Word(local)</h2>
<p> <a href= '/mylanguage-namespaced/test/word/passage/af'>Get af Passage from external id</a></p>

<h2>YOuVersion</h2>
<p> <a href= '/mylanguage-namespaced/test/youversion/link'>Get Passage Link</a></p>



<h2>Bibles</h2>
<p><a href = '/mylanguage-namespaced/test/bibles/best'> Best Bible for Language</a></p>
<p><a href = '/mylanguage-namespaced/test/passage/select'>Select Passage</a></p>



";