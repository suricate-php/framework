<?php
namespace Suricate;

use ErrorException;

class Error extends Service
{
    protected $parametersList   = array(
        'report',
        'dumpContext',
        'httpHandler'
        );

    public static function handleException($e, $context = null)
    {

        if ($e instanceof Exception\HttpException) {
            $httpHandler = Suricate::Error()->httpHandler;
            if (is_object($httpHandler) && ($httpHandler instanceof \Closure)) {
                $httpHandler($e);
                return;
            } else {
                $httpHandler = explode('::', $httpHandler);
                if (count($httpHandler) > 1) {
                    call_user_func($httpHandler, $e);
                } else {
                    call_user_func(head($httpHandler), $e);
                }

                return;
            }
            
            Suricate::Error()->displayGenericHttpExceptionPage($e);
        }

        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        /**
        TODO : put error in logger
         */
        Suricate::Error()->displayGenericExceptionPage($e, $context);
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

    private function displayGenericExceptionPage($e, $context = null)
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
            
        }
        exit(1);
    }

    private function displayGenericHttpExceptionPage($e)
    {
        $innerContent = '<h1>' . $e->getStatusCode() . '</h1>';
        $page = new Page();
        $page->setTitle($e->getStatusCode());
        
        $response = Suricate::Request();
        $response
            ->setBody($page->render($innerContent))
            ->setHttpCode($e->getStatusCode());


        $response->write();
        die();
    }
}
