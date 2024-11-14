<?php
use App\Services\Web\WebsiteConnectionService as WebsiteConnectionService;

$url = 'https://hereslife.com';

$website = new WebsiteConnectionService($url);
echo "You should see the hereslife website below<br><hr>";
echo ($website->response);