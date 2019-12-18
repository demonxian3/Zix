<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 **/

declare(strict_types=1);

namespace Psr\Http\Message;

interface IResponse {
    /**
     * Sets HTTP protocol version.
     * @return static
     */
    function setProtocolVersion(string $version);

    /**
     * Returns HTTP protocol version.
     */
    function getProtocolVersion(): string;

    /**
     * Returns HTTP reason phrase.
     */
    function getReasonPhrase(): string;

    /**
     * Sends a HTTP header and replaces a previous one.
     * @return static
     */
    function setHeader(string $name, string $value);

    /**
     * Adds HTTP header.
     * @return static
     */
    function addHeader(string $name, string $value);

    /**
     * @return static
     */
    function deleteHeader(string $name);

    /**
     * Sends a Content-type HTTP header.
     * @return static
     */
    function setContentType(string $type, string $charset = null);

    /**
     * Redirects to a new URL.
     */
    function redirect(string $url, int $code = self::S302_FOUND): void;

    /**
     * Returns value of an HTTP header.
     */
    function getHeader(string $header): array;

    /**
     * Returns a associative array of headers to sent.
     * @return string[][]
     */
    function getHeaders(): array;

    /**
     * Sends a cookie.
     * @param  string|int|\DateTimeInterface $expire  time, value 0 means "until the browser is closed"
     * @return static
     */
    function setCookie(string $name, string $value, int $second = 0);

    /**
     * Deletes a cookie.
     */
    function deleteCookie(string $name);

    /**
     * @param  string|\Closure  $body
     * @return static
     */
    function setBody($body);

    /**
     * @return string|\Closure
     */
    function getBody();
}
