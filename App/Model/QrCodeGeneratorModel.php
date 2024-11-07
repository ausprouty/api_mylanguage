<?php
namespace App\Model;

require_once ROOT_VENDOR . 'autoload.php';

use Endroid\QrCode\QrCode;

class QrCodeGeneratorModel
{
    private $url;
    private $size;
    private $filePath;
    private $QrCodeUrl;

    public function __construct($url, $size, $fileName)
    {
        $this->url = $url;
        $this->size = $size;
        $this->filePath = ROOT_RESOURCES . 'qrcodes/' .$fileName;
        $this->QrCodeUrl = WEBADDRESS_RESOURCES . 'qrcodes/' . $fileName;
    }

    public function generateQrCode()
    {
        $qrCode = new QrCode($this->url);
        $qrCode->setSize($this->size);
        $qrCode->writeFile($this->filePath);
    }
    public function getQrCodeUrl(){
        return $this->QrCodeUrl;
    }

}


