<?php
declare(strict_types=1);

namespace Khazix\Http;

use Psr\Http\Message\IRequest;

class Request implements  IRequest
{
    /** @string script absolute url */
    private $url;

    /** @string http method */
    private $method;

    /** @string http scheme */
    private $scheme;

    /** @string server host */
    private $host;

    /** @string server port */
    private $port;
    
    /** @string client ip address */
    private $remoteAddr;

    /** @string client host */
    private $remoteHost;

    /** @array headers */
    private $headers;

    /** @string rawBody */
    private $rawBody;

    /** @array parse raw body */
    private $body;


    public function __construct()
    {
        $this->url = $this->gainUrl();
        $this->method = $this->gainMethod();
        $this->headers = $this->gainHeaders();
        $this->rawBody = $this->gainRawBody();
        parse_str($this->rawBody, $this->body);
        list($this->scheme, $this->host, $this->port) = $this->gainServer();
        list($this->remoteAddr, $this->remoteHost) = $this->gainClient();
    }

    public function gainUrl(): string
    {
        return $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] .':'. $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
    }

    public function gainMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        if ( $method === 'POST' && preg_match('#^[A-Z]+$#D', $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')) {
           $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }
        return $method;
    }

    public function gainServer(): array
    {
        $scheme = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https' : 'http';

        if ( 
            (isset($_SERVER[$tmp = 'HTTP_HOST']) || isset($_SERVER[$tmp = 'SERVER_NAME']))
            && preg_match('#^([a-z0-9_.-]+|\[[a-f0-9:]+\])(:\d+)?$#Di', $_SERVER[$tmp], $pair)
        ) { 
            $host = strtolower($pair[1]);
            if (isset($pair[2])) { 
                $port = (int)substr($pair[2], 1);
            } elseif (isset($_SERVER['SERVER_PORT'])) { 
                $port = (int)$_SERVER['SERVER_PORT'];
            } 
        } 

        return array($scheme, $host , $port);
    }

    public function gainHeaders(): array
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strncmp($k, 'HTTP_', 5) == 0) {
                $k = substr($k, 5);
            } elseif (strncmp($k, 'CONTENT_', 8)) {
                continue;
            }
            $headers[strtr($k, '_', '-')] = $v;
        }
        return $headers;
    }

    public function gainClient(): array
    {
        $remoteAddr = !empty($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR'], '[]') : null; // workaround for PHP 7.3
        $remoteHost = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
        return [$remoteAddr, $remoteHost];
    }

    public function gainRawBody(): string
    {
        return file_get_contents('php://input');
    }


    public function getUrl(): string
    {
        return $this->url;
    }

    public function getQuery(string $key = null)
    {
        if ($key) {
            return $_GET[$key];
        }

        return $_GET;
    }

    public function getPost(string $key = null)
    {
        if ($key) {
            return $_POST[$key];
        }
        return $_POST;
    }

    public function getPatch(string $key = null)
    {
        if (!$this->isMethod('patch')) {
            return null;
        }
        return $this->getBody($key);
    }

    public function getPut(string $key = null)
    {
        if (!$this->isMethod('put')) {
            return null;
        }
        return $this->getBody($key);
    }

    public function getDelete(string $key = null)
    {
        if (!$this->isMethod('delete')) {
            return null;
        }
        return $this->getBody($key);
    }

    public function getFile(string $key)
    {
        return $_FILES[$key] ?? null;
    }

    public function getFiles(): array
    {
        return $_FILES;
    }

    public function getCookie(string $key)
    {
        return $_COOKIE[$key];
    }

    public function getCookies(): array
    {
        return $_COOKIE;
    }

    public function getSession(string $key)
    {
        return $_SESSION[$key];
    }

    public function getSessions(): array
    {
        return $_SESSION;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    public function getHeader(string $header): ?string
    {
        $header = ucfirst($header);
        return $this->headers[$header] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getReferer(): ?string
    {
        return isset($this->headers['referer']) ? $this->headers['referer'] : null;
    }

    public function isSecured(): bool
    {
        return $this->scheme === 'https';
    }

    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function getRemoteAddress(): ?string
    {
        return $this->remoteAddr;
    }

    public function getRemoteHost(): ?string
    {
        if ($this->remoteHost === null && $this->remoteAddr!== null) {
            $this->remoteHost = gethostbyaddr($this->remoteAddr);
        }
        return $this->remoteHost;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function getRequest(string $key = null)
    {
        if ($key && $this->body) {
            return $this->body[$key];
        }

        return $this->body;
    }

    public function checkRequest(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->body[$key])) {
                return false;
            }
        }

        return true;
    }

    public function extractRequest(string ...$keys): array
    {
        $request = array();
        foreach ($keys as $key) {
            if (isset($this->body[$key])) {
                $request[$key] = $this->body[$key];
            }
        }

        return $request;
    }

    public function getBody(string $key = null)
    {
        if ($key && $this->body) {
            return $this->body[$key];
        }

        return $this->body;
    }

    public function detectLanguage(array $langs): ?string
    {
        $header = $this->getHeader('Accept-Language');
        if (!$header) {
            return null;
        }

        $s = strtolower($header);  // case insensitive
        $s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
        rsort($langs);             // first more specific
        preg_match_all('#(' . strtolower(implode('|', $langs)) . ')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

        if (!$matches[0]) {
            return null;
        }

        $max = 0;
        $lang = null;
        foreach ($matches[1] as $key => $value) {
            $q = $matches[2][$key] === '' ? 1.0 : (float) $matches[2][$key];
            if ($q > $max) {
                $max = $q;
                $lang = $value;
            }
        }

        return $lang;
    }

}
