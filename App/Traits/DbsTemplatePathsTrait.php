<?php

namespace App\Traits;

/**
 * Trait TemplatePathsTrait
 *
 * Provides standardized methods for generating paths and URLs for PDF and HTML view files
 * based on a specified path prefix. This trait can be used in both monolingual and bilingual
 * controllers, where each controller provides its own unique path prefix by implementing the
 * `getPathPrefix` method.
 *
 * This trait centralizes path logic for accessing and storing resources, ensuring consistent
 * directory structures across various resources such as PDFs and view templates.
 *
 * @package App\Traits
 */
trait TemplatePathsTrait
{
    /**
     * Returns the server file path for PDF files associated with the resource.
     *
     * @return string The absolute path to the PDF directory, including the specific path prefix.
     */
    public static function getPathPdf(): string {
        return ROOT_RESOURCES . 'pdf/' . static::getPathPrefix() . '/';
    }

    /**
     * Returns the web-accessible URL for PDF files associated with the resource.
     *
     * @return string The URL to the PDF directory, including the specific path prefix.
     */
    public static function getUrlPdf(): string {
        return WEBADDRESS_RESOURCES . 'pdf/' . static::getPathPrefix() . '/';
    }

    /**
     * Returns the server file path for HTML view files associated with the resource.
     *
     * @return string The absolute path to the HTML view directory, including the specific path prefix.
     */
    public static function getPathView(): string {
        return ROOT_RESOURCES . 'view/' . static::getPathPrefix() . '/';
    }

    /**
     * Returns the web-accessible URL for HTML view files associated with the resource.
     *
     * @return string The URL to the HTML view directory, including the specific path prefix.
     */
    public static function getUrlView(): string {
        return WEBADDRESS_RESOURCES . 'view/' . static::getPathPrefix() . '/';
    }

    /**
     * Provides the prefix for the path and URL directory structure.
     *
     * Implement this method in the class that uses the trait to specify a prefix,
     * such as "principle" or "leadership". This allows different controllers to have
     * unique directory paths.
     *
     * @return string The path prefix to be used in generating paths and URLs.
     */
    abstract protected static function getPathPrefix(): string;
}
