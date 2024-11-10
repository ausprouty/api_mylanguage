<?php

namespace App\Traits;

trait MonolingualTemplatePathsTrait
{
    public static function getPathPdf(): string {
        return ROOT_RESOURCES . 'pdf/' . static::getPathPrefix() . '/';
    }

    public static function getUrlPdf(): string {
        return WEBADDRESS_RESOURCES . 'pdf/' . static::getPathPrefix() . '/';
    }

    public static function getPathView(): string {
        return ROOT_RESOURCES . 'view/' . static::getPathPrefix() . '/';
    }

    public static function getUrlView(): string {
        return WEBADDRESS_RESOURCES . 'view/' . static::getPathPrefix() . '/';
    }

    abstract protected static function getPathPrefix(): string;
}
