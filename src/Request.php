<?php
namespace Fwk;

class Request
{
    const HTTP_METHOD_GET       = 'GET';
    const HTTP_METHOD_POST      = 'POST';
    const HTTP_METHOD_PUT       = 'PUT';
    const HTTP_METHOD_DELETE    = 'DELETE';
    const HTTP_METHOD_HEAD      = 'HEAD';
    const HTTP_METHOD_OPTIONS   = 'OPTIONS';

    private $method = self::HTTP_METHOD_GET;
    private $methods = array(
            self::HTTP_METHOD_GET       => 'GET',
            self::HTTP_METHOD_POST      => 'POST',
            self::HTTP_METHOD_PUT       => 'PUT',
            self::HTTP_METHOD_DELETE    => 'DELETE',
            self::HTTP_METHOD_HEAD      => 'HEAD',
            self::HTTP_METHOD_OPTIONS   => 'OPTIONS'
        );

    private $httpCodeString = array(
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
        );

    private $httpCode;
    private $headers;
    private $requestUri;
    private $url;
    private $body;

    public function __construct()
    {
        $this->headers  = array();
        $this->httpCode = 200;
    }

    public function parse()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->setRequestUri($_SERVER['REQUEST_URI']);
        }
    }

    public function setMethod($method)
    {
        if (!isset($this->methods[$method])) {
            throw new \InvalidArgumentException('Invalid HTTP Method ' . $method);
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

    public static function getPostParam($variable, $defaultValue = null)
    {
        if (isset($_POST[$variable])) {
            return $_POST[$variable];
        }
        return $defaultValue;
    }

    public static function getParam($variable, $defaultValue = null)
    {
        if (isset($_GET[$variable])) {
            return $_GET[$variable];
        } elseif (isset($_POST[$variable])) {
            return $_POST[$variable];
        } else {
            return $defaultValue;
        }
    }

    public static function hasParam($variable)
    {
        return isset($_GET[$variable]) || isset($_POST[$variable]);
    }

       
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }


    public function setContentType($content_type, $encoding = null)
    {
        if ($encoding !== null) {
            $content_type .= '; charset=' . $encoding;
        }
        $this->addHeader('Content-type', $content_type);

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }


    public function write()
    {
        if (!headers_sent()) {
            if (substr(php_sapi_name(), 0, 3) == 'cgi') {
                $headerString = 'Status: ' . self::getStringForHttpCode();
            } else {
                $headerString = 'HTTP/1.1 ' . self::getStringForHttpCode();
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

    public function setHttpCode($code)
    {
        $this->httpCode = $code;

        return $this;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function redirect($url, $httpCode = 302)
    {
        $this->setHttpCode($httpCode);
        $this->addHeader('Location', $url);

        $this->write();
        die();
    }

    public function isOK()
    {
        return ( $this->httpCode == 200 );
    }

    public function isRedirect()
    {
        return ( $this->httpCode >= 300 && $this->httpCode < 400 );
    }

    public function isClientError()
    {
        return ( $this->httpCode >= 400 && $this->httpCode < 500 );
    }

    public function isServerError()
    {
        return ( $this->httpCode >= 500 && $this->httpCode < 600 );
    }

    private function getStringForHttpCode()
    {
        if (isset($this->httpCodeString[$this->httpCode])) {
            return $this->httpCode . ' ' . $this->httpCodeString[$this->httpCode];
        }
    }
}
