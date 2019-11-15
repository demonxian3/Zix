<?php
declare(strict_types=1);

namespace Khazix\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StatusCodeInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\IResponse;

/**
 * Notice: Case-Sensitive!!!
 * 特殊说明: 这里虽然遵循 PSR-7，但有一点例外，就是 headers name 区分大小写
 * 原文档的意思是 http头 是操作是忽略大小写的，但要保留原来的大小写，即在不修改
 * headers 键名为全小写的基础上进行忽略大小写的操作，这样弄起来会比较繁琐，比如:
 * 设置头部 setHeader('content-type', 'application/json'); 那么程序就要以 
 * strcasecmp 进行匹配 headers 是否存在该 Content-Type 的键值，如果存在就
 * 以 Content-Type 作为键值，否则以用户输入的 content-type 作为键值，这样搞
 * 不如干脆直接大小写敏感，相对好实现一些！！
 *
 */
class Response implements ResponseInterface, StatusCodeInterface, IResponse
{
    /** @var string HTTP Protocol Version */
    private $version = '1.1';

    /** @var int HTTP response code */
    private $code = self::STATUS_OK;

    /** @var string Reason phrase for status code */
    private $reason = "";

    /** @var array headers */
    private $headers = [];

    /** @var string Message body */
    private $body = "";

    /** @var bool Whether warn on possible problem with data in output buffer */
    public $warnOnBuffer = true;

    /** @var string cookie path */
    public $path = '/';

    /** @var string cookie domain */
    public $domain = '';

    /** @var bool cookie secure: only use in https*/
    public $secure = false;

    /** @var bool cookie httponly: cannot be access by js */
    public $httpOnly = false;
    
    
    public function __construct()
    {
        if ($_SERVER['SERVER_PROTOCOL']) {
            $this->version = substr($_SERVER['SERVER_PROTOCOL'], 5);
        }

        if (is_int($code = http_response_code())) {
            $this->code = $code;
            $this->reason = self::STATUS_REASON_PHRASE_MAP[$this->code] ?? '';
        }

        foreach (headers_list() as $header) {
            $a = strpos($header, ':');
            $name = substr($header, 0 , $a);

            if ($name !== 'Content-Type') {
                header_remove($name);
            }

            if (!array_key_exists($name, $this->headers)) {
                $this->headers[$name] = array();
            }

            array_push($this->headers[$name], substr($header, $a+2));
        }

    }

    /** {@inheritdoc} */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    public function setProtocolVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /** {@inheritdoc} */
    public function withProtocolVersion(string $version)
    {
        $dolly = clone $this;
        $dolly->version = $version;
        return $dolly;
    }

    /** {@inheritdoc} */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /** {@inheritdoc} */
    public function getReasonPhrase(): string
    {
        return $this->reason;
    }

    /** 
     * Sets HTTP response code.
     * @throws Exception  if code is invalid
     * */
    public function setStatus($code, $reasonPhrase = ''): self
    {
        if ($code < 100 || $code > 599) {
            throw new \Exception("Bad Http response '$code'.");
        }

        $this->code = $code;
        $this->reason = self::STATUS_REASON_PHRASE_MAP[$this->code] ?? $reasonPhrase;
        return $this;
    }

    /** {@inheritdoc} */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $dolly = clone $this;
        $dolly->setStatus($code, $reasonPhrase);
        return $dolly;
    }

    /** 
     * {@inheritdoc} 
     * @return string[][] Returns an associative array of the message's headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** {@inheritdoc} */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    public function hasHeader_old(string $name): bool
    {
        $name .= ':';
        $len = strlen($name);
        foreach (headers_list() as $item) {
            if (strncasecmp($item, $name, $len) === 0) {
                return true;
            }
        }
        return false;
    }

    /** 
     * {@inheritdoc} 
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     */
    public function getHeader(string $header): array
    {
        return $this->headers[$header] ?? [];
    }

    public function getHeader_old(string $name): ?string
    {
        $name .= ':';
        $len = strlen($name);
        foreach (headers_list() as $item) {
            if (strncasecmp($item, $name, $len) === 0) {
                return ltrim(substr($item, $len));
            }
        }
        return null;
    }

    /** 
     * {@inheritdoc} 
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     */
    public function getHeaderLine(string $name): string
    {
        return $this->headers[$name] ? implode(',',$this->headers[$name]) : '';
    }


    /**
     * set HTTP header and replaces a previous one.
     * @return self
     */
    public function setHeader(string $name, $value): self
    {
        if (strcasecmp($name, 'Content-Length') === 0 && ini_get('zlib.output_compression')) {
            //ignore, PHP bug #44164
        } 

        $this->headers[$name] = (array)$value;
        return $this;
    }

    /**
     * Adds HTTP header.
     * @return self
     */
    public function addHeader(string $name, string $value): self
    {
        if (!array_key_exists($name, $this->headers)) {
            $this->headers[$name] = array();
        }

        array_push($this->headers[$name], $value);
        return $this;
    }

    public function addHeader_old(string $name, string $value): self
    {
        self::checkHeaders();
        header($name . ': '. $value, false, $this->code);
        return $this;
    }

    /**
     * delete HTTP header.
     * @return self
     */
    public function deleteHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function clearHeader(): self
    {
        $this->headers = [];
        return $this;
    }

    /** {@inheritdoc} */
    public function withHeader($name, $value)
    {
        $dolly = clone $this;
        $dolly->setHeader($name, $value);
        return $dolly;
    }

    /** {@inheritdoc} */
    public function withAddedHeader(string $name, string $value)
    {
        $dolly = clone $this;
        $dolly->addHeader($name, $value);
        return $dolly;
    }

    /** {@inheritdoc} */
    public function withoutHeader(string $name)
    {
        $dolly = clone $this;
        $dolly->deleteHeader($name);
        return $dolly;
    }

    /**
     * Sends a Content-type HTTP header.
     * @return self
     */
    public function setContentType(string $type, string $charset = null): self
    {
        $this->setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
        return $this;
    }

    /**
     * Redirects to a new URL. Note: call exit() after it.
     * @throws Exception  if HTTP headers have been sent
     */
    public function redirect(string $url, int $code = self::STATUS_FOUND): void
    {
        $this->setStatus($code);
        $this->setHeader('Location', $url);
        if (preg_match('#^https?:|^\s*+[a-z0-9+.-]*+[^:]#i', $url)) {
            $escapedUrl = htmlspecialchars($url, ENT_IGNORE | ENT_QUOTES, 'UTF-8');
            $this->body = "<h1>Redirect</h1>\n\n<p><a href=\"$escapedUrl\">Please click here to continue</a>.</p>";
        }
        $this->sendHeaders();
    }


    /** {@inheritdoc} */
    public function getBody()
    {
    }

    /** {@inheritdoc} */
    public function setBody($body)
    {
    }

    /** {@inheritdoc} */
    public function withBody(StreamInterface $body)
    {
    }

    public function sendHeaders(): self
    {
        self::checkHeaders();
    
        $protocol = 'HTTP/' . $this->version;

        header("{$protocol} {$this->code} {$this->reason}");

        foreach ($this->headers as $name => $values) {
            if ($name === 'Content-Type') {
                header_remove('Content-Type');
            }

            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        return $this;
    }

    public function setCookie(string $name, string $value, int $second = 0): void
    {
        $expires = $second > 0 ? time()+$second : 0;
        setCookie($name, $value, $expires, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }


    public function deleteCookie(string $name): void
    {
        setCookie($name, '', 0, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }


    public function isSent(): bool
    {
        if (headers_sent($file, $line)){
            return true;
        } else if (ob_get_length() && !array_filter(ob_get_status(true), function (array $i): bool { return !$i['chunk_size']; })) {
            return true;
        }

        return false;
            
    }

    private function checkHeaders(): void
    {
        if (PHP_SAPI === 'cli') {
        } elseif (headers_sent($file, $line)) {
            throw new \Exception('Cannot send header after HTTP headers have been sent' . ($file ? " (output started at $file:$line)." : '.'));

        } elseif (
            $this->warnOnBuffer &&
            ob_get_length() &&
            !array_filter(ob_get_status(true), function (array $i): bool { return !$i['chunk_size']; })
        ) {
            trigger_error('Possible problem: you are sending a HTTP header while already having some data in output buffer. Try start session earlier.');
        }
    }

}
