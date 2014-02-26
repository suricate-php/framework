<?php
namespace Fwk;

class Curl extends Service
{
    protected $parametersList   = array(
                                    'userAgent',
                                    'timeout',
                                    'proxyHost',
                                    'proxyPort',
                                    'referer',
                                    'cookie',
                                    'userAgent',
                                    'postFields',
                                    'login',
                                    'password'
                                );

    private $request;
    private $response;
    private $responseData;
    private $errorMsg;
    private $errorCode;

    public function __construct()
    {
        $this->request  = new Request();
        $this->response = new Request();
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

    public function setUserAgent($user_agent)
    {
        $this->userAgent = $user_agent;

        return $this;
    }
    
    public function send()
    {
        $ch     = curl_init($this->request->getUrl());
        
        $curlOptions = $this->generateCurlOptions();
        curl_setopt_array($ch, $curlOptions);

        $curlResponse       = curl_exec($ch);
        if ($curlResponse === false) {
            $this->errorMsg     = curl_error($ch);
            $this->errorCode    = curl_errno($ch);

            return false;
        } else {
            $this->responseData = curl_getinfo($ch);
            $this->response->setUrl($this->responseData['url']);
            $redirectCount      = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);

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

        }
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
        
        // Set timeout
        if ($this->getParameter('timeout') !== null) {
            $curlOptions[CURLOPT_CONNECTTIMEOUT] = $this->getParameter('timeout');
        }

        // Set proxy host
        if ($this->getParameter('proxyHost') !== null) {
            $curlOptions[CURLOPT_PROXY] = $this->getParameter('proxyHost');
        }

        // Set proxy port
        if ($this->getParameter('proxyPort') !== null) {
            $curlOptions[CURLOPT_PROXYPORT] = $this->getParameter('proxyPost');
        }

        if ($this->getParameter('referer') !== null) {
            $curlOptions[CURLOPT_REFERER] = $this->getParameter('referer');
        }

        if ($this->getParameter('cookie') !== null) {
            $curlOptions[CURLOPT_COOKIE] = $this->getParameter('cookie');
        }

        if ($this->getParameter('userAgent') !== null) {
            $curlOptions[CURLOPT_USERAGENT] = $this->getParameter('userAgent');
        }

        //
        // Method management
        //
        if ($this->request->getMethod() == Request::HTTP_METHOD_GET) {
            $curlOptions[CURLOPT_HTTPGET] = true;
        } elseif ($this->request->getMethod() == Request::HTTP_METHOD_POST) {
            $curlOptions[CURLOPT_POST] = true;
            if ($this->getParameter('postFields') !== null) {
                $curlOptions[CURLOPT_FIELDS] = $this->getParameters('postFields');
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
