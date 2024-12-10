<?php

namespace App\Services;

use Endroid\QrCode\QrCode;

class QrCodeGeneratorService
{
    private string $url;
    private int $size;
    private string $filePath;
    private string $qrCodeUrl;

    public function initialize(string $url, int $size, string $fileName): void
    {
        $this->url = $url;
        $this->size = $size;
        $this->filePath = ROOT_RESOURCES . 'qrcodes/' . $fileName;
        $this->qrCodeUrl = Config::getDir('web.webaddress_resources') . 'qrcodes/' . $fileName;
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
