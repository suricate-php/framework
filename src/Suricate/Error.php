<?php declare(strict_types=1);
namespace Suricate;

use ErrorException;
/**
 * @property bool $report
 * @property bool $dumpContext
 * @property mixed $httpHandler Handler (object/closure/string) in charge of the error
 */
class Error extends Service
{
    protected $parametersList = [
        'report',
        'dumpContext',
        'httpHandler'
    ];

    public static function handleException($e, $context = null)
    {
        if ($e instanceof Exception\HttpException) {
            $httpHandler = Suricate::Error()->httpHandler;
            if (is_object($httpHandler) && ($httpHandler instanceof \Closure)) {
                $httpHandler($e);
                return;
            } elseif ($httpHandler != '') {
                $httpHandler = explode('::', $httpHandler);
                
                if (count($httpHandler) > 1) {
                    $userFunc = $httpHandler;
                } else {
                    $userFunc = head($httpHandler);
                }
                if (is_callable($userFunc)) {
                    call_user_func($userFunc, $e);
                }
                return;
            }
            
            Suricate::Error()->displayGenericHttpExceptionPage($e);
        }

        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        
        $json = [];
        $error = $e;
        do {
            $json[] = [
                'type' => get_class($error),
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => explode("\n", $error->getTraceAsString()),
            ];
        } while ($error = $error->getPrevious());
        Suricate::Logger()->error(json_encode($json));

        Suricate::Error()->displayExceptionPage($e, $context);
    }

    public static function handleError($code, $message, $file, $line, $context)
    {
        static::handleException(new ErrorException($message, $code, 0, $file, $line), $context);
    }

    public static function handleShutdownError()
    {
        if ($error = error_get_last()) {
            static::handleException(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }

    private function displayExceptionPage($e, $context = null)
    {
        if ($this->report || $this->report === null) {
            echo '<html>'."\n";
            echo '  <head>'."\n";
            echo '      <title>Oops, Uncaught Exception</title>'."\n";
            echo '      <style>'."\n";
            echo '          body{font-family: "Open Sans",arial,sans-serif; background: #FFF; color:#333; margin:2em}'."\n";
            echo '          code{background:#E0941B;border-radius:4px;padding:2px 6px}'."\n";
            echo '      </style>'."\n";
            echo '  </head>'."\n";
            echo '  <body>'."\n";
            echo '      <h1>Oops, uncaught exception !</h1>'."\n";
            echo '      <h2>This is embarrassing, but server made a booboo</h2>'."\n";
            echo '      <p><code>' . $e->getMessage() . '</code></p>'."\n";
            echo '      <h3>From:</h3>'."\n";
            echo '      <p><code>' . $e->getFile() . ' on line ' . $e->getLine() . '</code></p>'."\n";
            echo '      <h3>Call stack</h3>'."\n";
            echo '      <pre>' . $e->getTraceAsString() . '</pre>'."\n";
            if ($this->dumpContext) {
                echo '<h3>Context:</h3>';
                _p($context);
            }
            echo '  </body>'."\n";
            echo '</html>';
        } else {
            if ($e->getCode() <= 1) {
                $err = new Exception\HttpException('500');
                $this->displayGenericHttpExceptionPage($err);
            }
        }
        exit(1);
    }

    private function displayGenericHttpExceptionPage($e)
    {
        $response = Suricate::Request();

        if (is_readable(app_path() . '/views/Errors/' . $e->getStatusCode() . '.php')) {
            ob_start();
            include app_path() . '/views/Errors/' . $e->getStatusCode() . '.php';
            $body = ob_get_clean();
        } else {
            $innerHtml = '<h1>' . $e->getStatusCode()  .'</h1>';
            
            $page = new Page();
            $body = $page
                ->setTitle($e->getStatusCode())
                ->render($innerHtml);
        }
        
        $response
            ->setBody($body)
            ->setHttpCode($e->getStatusCode());
        foreach ($e->getHeaders() as $header => $value) {
            $response->addHeader($header, $value);
        }

        $response->write();
        die();
    }
}
