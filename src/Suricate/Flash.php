<?php
namespace Suricate;

class Flash
{
    public static $types = array(
        'success',
        'info',
        'error',
        'data'
    );

    private static $items = array();
    private static $consumed = false;

    public static function read()
    {
        if (!self::$consumed) {
            // Get notification
            self::$items = (array) Suricate::Session()->read('flash');

            // Erase (consume)
            Suricate::Session()->destroy('flash');
            self::$consumed = true;
        }
    }

    public static function renderMessages()
    {
        /**
         TODO : call user defined view
         */
        self::read();
        $output = '';
        $availableTypes = array('success' => 'success', 'info' => 'info', 'error' => 'danger');
        
        foreach ($availableTypes as $type => $displayAlias) {
            $currentMessage = dataGet(self::$items, $type, null);
            
            if ($currentMessage !== null) {
                $output .= '<div class="alert alert-' . $displayAlias . '">' . implode('<br/>', (array) $currentMessage) . '</div>';
            }
        }

        return $output;
    }

    public static function getData($key)
    {
        self::read();

        if (isset(self::$items['data']) && array_key_exists($key, self::$items['data'])) {
            return self::$items['data'][$key];
        } else {
            return null;
        }
    }

    public static function write($type, $message)
    {

        if (in_array($type, static::$types)) {
            $currentSessionData = Suricate::Session()->read('flash');

            if (isset($currentSessionData[$type]) && is_array($currentSessionData[$type])) {
                $newData = array_merge($currentSessionData[$type], (array) $message);
            } else {
                $newData = (array) $message;
            }

            $currentSessionData[$type] = $newData;
            Suricate::Session()->write('flash', $currentSessionData);
        }
    }

    public static function writeMessage($type, $message)
    {

    }

    public static function writeData($key, $data)
    {

    }
}
