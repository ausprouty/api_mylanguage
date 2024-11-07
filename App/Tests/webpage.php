<?php
use App\Models\Data\WebsiteConnectionModel as WebsiteConnectionModel;

$url = 'https://hereslife.com';

$website = new WebsiteConnectionModel($url);
echo "You should see the hereslife website below<br><hr>";
echo ($website->response);