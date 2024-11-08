<?php
/*  see https://documenter.getpostman.com/view/12519377/Tz5p6dp7
*/
namespace App\Models\Data;

use App\Services\WebsiteConnectionService as WebsiteConnectionService;

class BibleBrainConnectionModel extends WebsiteConnectionService
{
    public function __construct(string $url){
      $this->url = $url . '&v=4&key=' .  BIBLE_BRAIN_KEY;
      parent::connect();
      $this->response = json_decode($this->response);
    }
}