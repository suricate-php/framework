<?php

declare(strict_types=1);

namespace Suricate;

class Console
{
    /** @var Suricate */
    private $app;

    /** @var array */
    private $args;

    public function __construct(Suricate $app, array $args)
    {
        $this->app = $app;
        $this->args = $args;
        array_shift($this->args);
    }

    public function execute()
    {
        $command = $this->args[0] ?: '';

        switch ($command) {
            case 'route':
                $command = new Console\Route($this->app);
                $retval = $command->execute();
                break;
            default:
                echo "Unknown command";
                $retval = 1;
                break;
        }

        return $retval;
    }

    public static function coloredString(string $string, $color)
    {
        $availableColors = [
            'black' => '0;30',
            'dark_gray' => '1;30',
            'blue' => '0;34',
            'light_blue' => '1;34',
            'green' => '0;32',
            'light_green' => '1;32',
            'cyan' => '0;36',
            'light_cyan' => '1;36',
            'red' => '0;31',
            'light_red' => '1;31',
            'purple' => '0;35',
            'light_purple' => '1;35',
            'brown' => '0;33',
            'yellow' => '1;33',
            'light_gray' => '0;37',
            'white' => '1;37'
        ];
        $shellColor = $availableColors[$color] ?: $availableColors['white'];

        return sprintf("\033[%sm%s\033[0m", $shellColor, $string);
    }
}
