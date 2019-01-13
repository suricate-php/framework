<?php
namespace Suricate;

/**
 * Curl extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $userAgent
 * @property int $timeout
 * @property string $proxyHost
 * @property int $proxyPort
 * @property string $referer
 * @property string $cookie
 * @property string $userAgent
 * @property mixed $postFields
 * @property string $login
 * @property string $password
 * @property array $headers
 */

class Curl extends Service
{
    protected $parametersList   = [
        'userAgent',
        'timeout',
        'proxyHost',
        'proxyPort',
        'referer',
        'cookie',
        'userAgent',
        'postFields',
        'login',
        'password',
        'headers',
    ];

    private $request;
    private $response;
    private $responseData;
    private $errorMsg;
    private $errorCode;

    public function __construct()
    {
        parent::__construct();

        $this->request  = new Request();
        $this->response = new Request();
        $this->headers  = [];
    }

    public function setUrl($url)
    {
        $this->request->setUrl($url);

        return $this;
    }

    public function getUrl()
    {
        return $this->request->getUrl();
    }

    public function setMethod($method)
    {
        $this->request->setMethod($method);

        return $this;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function addHeader($headerLine)
    {
        $headers = $this->headers;
        $headers[] = $headerLine;

        $this->headers = $headers;

        return $this;
    }
    
    public function send()
    {
        $curlHandler     = curl_init($this->request->getUrl());

        curl_setopt_array($curlHandler, $this->generateCurlOptions());

        $curlResponse       = curl_exec($curlHandler);
        if ($curlResponse === false) {
            $this->errorMsg     = curl_error($curlHandler);
            $this->errorCode    = curl_errno($curlHandler);

            return false;
        }

        $this->responseData = curl_getinfo($curlHandler);
        $this->response->setUrl($this->responseData['url']);
        $redirectCount      = curl_getinfo($curlHandler, CURLINFO_REDIRECT_COUNT);

        $splittedResponse   = explode("\r\n\r\n", $curlResponse, $redirectCount + 2);
        $lastHeader         = $splittedResponse[$redirectCount];
    
        // get headers out of response
        $headers = explode("\n", trim($lastHeader));
        array_shift($headers);
    
        foreach ($headers as $headerLine) {
            preg_match('|^([\d\w\s_-]*):(.*)|', $headerLine, $matches);
            if (isset($matches[1])) {
                $this->response->addHeader($matches[1], trim($matches[2]));
            }
        }


        // Reponse data
        $this->response->setHttpCode($this->responseData['http_code']);
        $this->response->setBody(substr($curlResponse, $this->responseData['header_size']));

        return $this;
    }

    private function generateCurlOptions()
    {
        $curlOptions = array(
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_HEADER          => true,
                CURLINFO_HEADER_OUT     => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_SSL_VERIFYHOST  => false
            );
        
        $parametersMapping = array(
            CURLOPT_CONNECTTIMEOUT  => 'timeout',
            CURLOPT_PROXY           => 'proxyHost',
            CURLOPT_PROXYPORT       => 'proxyPort',
            CURLOPT_REFERER         => 'referer',
            CURLOPT_COOKIE          => 'cookie',
            CURLOPT_USERAGENT       => 'userAgent',
            CURLOPT_HTTPHEADER      => 'headers',
        );

        foreach ($parametersMapping as $curlKey => $optionKey) {
            if (($value = $this->getParameter($optionKey)) !== null) {
                $curlOptions[$curlKey] = $value;
            }
        }

        //
        // Method management
        //
        if ($this->request->getMethod() == Request::HTTP_METHOD_GET) {
            $curlOptions[CURLOPT_HTTPGET] = true;
        } elseif ($this->request->getMethod() == Request::HTTP_METHOD_POST) {
            $curlOptions[CURLOPT_POST] = true;
            if ($this->getParameter('postFields') !== null) {
                $curlOptions[CURLOPT_POSTFIELDS] = $this->getParameter('postFields');
            }
        } elseif ($this->request->getMethod() == Request::HTTP_METHOD_PUT) {
            $curlOptions[CURLOPT_PUT] = true;
        } elseif ($this->request->getMethod() == Request::HTTP_METHOD_HEAD) {
            $curlOptions[CURLOPT_NOBODY] = true;
        }

        return $curlOptions;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function getHttpCode()
    {
        return isset($this->responseData['http_code']) ? $this->responseData['http_code'] : null;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
