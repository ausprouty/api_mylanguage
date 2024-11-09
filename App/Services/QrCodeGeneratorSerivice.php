<?php

namespace App\Services;

use Endroid\QrCode\QrCode;

class QrCodeGeneratorService
{
    private $url;
    private $size;
    private $filePath;
    private $qrCodeUrl;

    public function __construct($url, $size, $fileName)
    {
        $this->url = $url;
        $this->size = $size;
        $this->filePath = ROOT_RESOURCES . 'qrcodes/' . $fileName;
        $this->qrCodeUrl = WEBADDRESS_RESOURCES . 'qrcodes/' . $fileName;
    }

    public function generateQrCode(): void
    {
        $qrCode = new QrCode($this->url);
        $qrCode->setSize($this->size);
        $qrCode->writeFile($this->filePath);
    }

    public function getQrCodeUrl(): string
    {
        return $this->qrCodeUrl;
    }
}
