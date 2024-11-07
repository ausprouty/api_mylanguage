<?php
/*  see https://documenter.getpostman.com/view/12519377/Tz5p6dp7
*/
namespace App\Model\Data;

use App\Model\Data\WebsiteConnectionModel as WebsiteConnectionModel;

class BibleBrainConnectionModel extends WebsiteConnectionModel
{
    public function __construct(string $url){
      $this->url = $url . '&v=4&key=' .  BIBLE_BRAIN_KEY;
      parent::connect();
      $this->response = json_decode($this->response);
    }
}