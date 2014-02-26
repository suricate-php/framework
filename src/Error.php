<?php
namespace Fwk;

use ErrorException;

class Error extends Service
{
    protected $parametersList   = array(
        'report'
        );

    public static function handleException($e)
    {
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        
        /**
        TODO : put error in logger
         */
        $errorHandler = Fwk::Error();

        if ($errorHandler !== null && $errorHandler->report) {
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
            echo '      <h2>This is embarrassing, but server made a boo boo</h2>'."\n";
            echo '      <p><code>' . $e->getMessage() . '</code></p>'."\n";
            echo '      <h3>From :</h3>'."\n";
            echo '      <p><code>' . $e->getFile() . ' on line ' . $e->getLine() . '</code></p>'."\n";
            echo '      <h3>Call stack</h3>'."\n";
            echo '      <pre>' . $e->getTraceAsString() . '</pre>'."\n";
            echo '  </body>'."\n";
            echo '</html>';
            
        }
        exit(1);
    }

    public static function handleError($code, $message, $file, $line, $context)
    {
        static::handleException(new ErrorException($message, $code, 0, $file, $line));
    }

    public static function handleShutdownError()
    {
        if ($error = error_get_last()) {
            extract($error);

            static::handleException(new ErrorException($message, $type, 0, $file, $line));
        }
    }
}
