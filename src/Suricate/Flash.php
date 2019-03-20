<?php declare(strict_types=1);
namespace Suricate;

class Flash
{
    const TYPE_SUCCESS  = 'success';
    const TYPE_INFO     = 'info';
    const TYPE_ERROR    = 'error';
    const TYPE_DATA     = 'data';

    public static $types = [
        self::TYPE_SUCCESS,
        self::TYPE_INFO,
        self::TYPE_ERROR,
        self::TYPE_DATA,
    ];

    private static $items = [];
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

    /**
     * Render success / info / error messages in HTML
     *
     * @return string
     */
    public static function renderMessages(): string
    {
        self::read();

        $availableTypes = [
            self::TYPE_SUCCESS   => 'success',
            self::TYPE_INFO      => 'info',
            self::TYPE_ERROR     => 'danger'
        ];

        $output = '';
        foreach ($availableTypes as $type => $displayAlias) {
            $currentMessage = self::getMessages($type);
            
            if (count($currentMessage)) {
                $output .= '<div class="alert alert-' . $displayAlias . '">' . implode('<br/>', (array) $currentMessage) . '</div>';
            }
        }

        return $output;
    }

    /**
     * Get flash data for a key
     *
     * @param string $key
     * @return mixed
     */
    public static function getData(string $key)
    {
        self::read();

        if (isset(self::$items[self::TYPE_DATA])
            && array_key_exists($key, self::$items[self::TYPE_DATA])) {
            return self::$items[self::TYPE_DATA][$key];
        }

        return null;
    }

    /**
     * Get flash message for a type
     *
     * @param string $type
     * @return array
     */
    public static function getMessages(string $type): array
    {
        self::read();

        if (isset(self::$items[$type])) {
            $result = self::$items[$type];
            unset(self::$items[$type]);
            return $result;
        }

        return [];
    }

    /**
     * Write flash message or data to session
     *
     * @param string $type
     * @param mixed $message
     * @throws \InvalidArgumentException
     * @return void
     */
    private static function write(string $type, $message)
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
            self::$consumed = false;

            return;
        }

        throw new \InvalidArgumentException("Unknown message type '$type'");
    }

    /**
     * Write message to flash storage
     *
     * @param string $type
     * @param string|array $message
     * @return void
     */
    public static function writeMessage(string $type, $message)
    {
        self::write($type, $message);
    }

    /**
     * Write data to flash storage
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public static function writeData(string $key, $data)
    {
        self::write(self::TYPE_DATA, [$key => $data]);
    }
}
