<?php declare(strict_types=1);
namespace Suricate;

/**
 * @SuppressWarnings("StaticAccess")
 */
class Request
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD = 'HEAD';
    const HTTP_METHOD_OPTIONS = 'OPTIONS';

    private $method = self::HTTP_METHOD_GET;
    private $methods = [
        self::HTTP_METHOD_GET => 'GET',
        self::HTTP_METHOD_POST => 'POST',
        self::HTTP_METHOD_PUT => 'PUT',
        self::HTTP_METHOD_DELETE => 'DELETE',
        self::HTTP_METHOD_HEAD => 'HEAD',
        self::HTTP_METHOD_OPTIONS => 'OPTIONS'
    ];

    private $httpCodeString = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        250 => 'Low on Storage Space',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '306 Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        494 => 'Request Header Too Large',
        495 => 'Cert Error',
        496 => 'No Cert',
        497 => 'HTTP to HTTPS',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    private $httpCode;
    private $headers = [];
    private $requestUri = '';
    private $url;
    private $body;
    private $path;
    private $query;

    public function __construct()
    {
        $this->headers = [];
        $this->httpCode = 200;
    }

    public function parse()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->setRequestUri($_SERVER['REQUEST_URI']);
            $parseResult = parse_url($_SERVER['REQUEST_URI']);
            $this->path = dataGet($parseResult, 'path');
            $this->query = dataGet($parseResult, 'query');
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->setMethod($_SERVER['REQUEST_METHOD']);
        }
        if (isset($_POST['_method'])) {
            $this->setMethod($_POST['_method']);
        }
    }

    public function setMethod($method)
    {
        if (!isset($this->methods[$method])) {
            throw new \InvalidArgumentException(
                'Invalid HTTP Method ' . $method
            );
        }

        $this->method = $method;

        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
    public function getUrl()
    {
        return $this->url;
    }

    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;

        return $this;
    }

    public function getRequestUri()
    {
        return $this->requestUri;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public static function getPostParam($variable, $defaultValue = null)
    {
        if (array_key_exists($variable, $_POST)) {
            return $_POST[$variable];
        }
        return $defaultValue;
    }

    public static function getParam($variable, $defaultValue = null)
    {
        if (array_key_exists($variable, $_GET)) {
            return $_GET[$variable];
        }
        if (array_key_exists($variable, $_POST)) {
            return $_POST[$variable];
        }

        return $defaultValue;
    }

    public static function hasParam($variable)
    {
        return array_key_exists($variable, $_GET) ||
            array_key_exists($variable, $_POST);
    }

    /**
     * Set request headers
     *
     * @param array $headers Headers to set key => $value
     * @return Request
     */
    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Add specific header
     *
     * @param string $header header name
     * @param string $value  header value
     * @return Request
     */
    public function addHeader($header, $value): Request
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * Get request headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setContentType(
        string $contentType,
        $encoding = null
    ): Request {
        if ($encoding !== null) {
            $contentType .= '; charset=' . $encoding;
        }
        $this->addHeader('Content-type', $contentType);

        return $this;
    }

    public function setBody(string $body): Request
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function write()
    {
        if (!headers_sent()) {
            if (substr(php_sapi_name(), 0, 3) == 'cgi') {
                $headerString = 'Status: ' . $this->getStringForHttpCode();
            } else {
                $headerString = 'HTTP/1.1 ' . $this->getStringForHttpCode();
            }

            header($headerString);

            // Send headers
            foreach ($this->headers as $headerName => $headerValue) {
                header($headerName . ':' . $headerValue);
            }
        }

        /**
         TODO HANDLE HTTP RESPONSE CODE
         */
        if ($this->httpCode !== null) {
        }

        // Send body
        echo $this->body;
    }

    //
    // HTTP Code
    //

    public function setHttpCode($code): Request
    {
        $this->httpCode = $code;

        return $this;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Flash message
     *
     * @param string $type message type
     * @param array|string $data data to be flashed
     */
    public function flash(string $type, $data)
    {
        Flash::writeMessage($type, $data);

        return $this;
    }

    public function flashData($name, $value)
    {
        Flash::writeData($name, $value);

        return $this;
    }

    public function redirect($url, $httpCode = 302)
    {
        $this->setHttpCode($httpCode);
        $this->addHeader('Location', $url);

        $this->write();
        die();
    }

    public function redirectWithSuccess($url, $message)
    {
        $this->flash('success', $message)->redirect($url);
    }

    public function redirectWithInfo($url, $message)
    {
        $this->flash('info', $message)->redirect($url);
    }

    public function redirectWithError($url, $message)
    {
        $this->flash('error', $message)->redirect($url);
    }

    public function redirectWithData($url, $key, $value)
    {
        $this->flashData($key, $value)->redirect($url);
    }

    /**
     * Check if request has a 200 OK Code
     *
     * @return boolean
     */
    public function isOK(): bool
    {
        return $this->httpCode == 200;
    }

    /**
     * Check if request has a 3XX HTTP code
     *
     * @return boolean
     */
    public function isRedirect(): bool
    {
        return $this->httpCode >= 300 && $this->httpCode < 400;
    }

    /**
     * Check is request has a 4XX HTTP code
     *
     * @return boolean
     */
    public function isClientError(): bool
    {
        return $this->httpCode >= 400 && $this->httpCode < 500;
    }

    /**
     * Check if request has a 5XX HTTP code
     *
     * @return boolean
     */
    public function isServerError(): bool
    {
        return $this->httpCode >= 500 && $this->httpCode < 600;
    }

    private function getStringForHttpCode()
    {
        if (isset($this->httpCodeString[$this->httpCode])) {
            return $this->httpCode .
                ' ' .
                $this->httpCodeString[$this->httpCode];
        }
    }
}
