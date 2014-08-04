<?php
namespace Suricate;

/**
 * Translation extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $locale
 */

class I18n extends Service
{
    protected $parametersList = array(
        'locale'
    );
    private $baseLocaleDir = 'i18n';
    private $translations;

    /**
     * Get the list of installed languages for application
     * @return array Array of available languages of application
     */
    public function i18nList()
    {
        $langDir    =  app_path() . DIRECTORY_SEPARATOR
            . $this->baseLocaleDir;

        $langList   = array();
        $iterator   = new \DirectoryIterator($langDir);

        foreach ($iterator as $currentFile) {
            if ($currentFile->isDir() 
                && !$currentFile->isDot()
                && is_readable($currentFile->getPathname() . DIRECTORY_SEPARATOR . 'language.php')) {
                $langList[$currentFile->getFilename()] = $currentFile->getFilename();
            }
        }
        asort($langList);
        
        return $langList;
    }

    public function load($locale = null)
    {
        $filename    = app_path() . DIRECTORY_SEPARATOR
            . $this->baseLocaleDir . DIRECTORY_SEPARATOR
            . $locale . DIRECTORY_SEPARATOR
            . 'language.php';

        if (is_readable($filename)) {
            $this->locale       = $locale;
            $this->translations = include $filename;
        } else {
            Suricate::Logger()->debug(sprintf('Missing translation file for %s', $this->locale));
        }
    }

    public function get()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            $str    = $args[0];

            if ($this->translations === null) {
                $this->load($this->locale);
            }

            if (isset($this->translations[$str])) {
                if (isset($args[1])) {
                    array_shift($args);
                    return vsprintf($this->translations[$str], $args);
                } else {
                    return $this->translations[$str];
                }
            } else {
                return $str;
            }
        }
    }

    public function choice()
    {
        $args = func_get_args();

        $str    = array_shift($args);
        $number = array_shift($args);

        if ($this->translations === null) {
            $this->load($this->locale);
        }
        if (isset($this->translations[$str])) {
            if (strpos($this->translations[$str], '|') !== false) {
                list($single, $plural) = explode('|', $this->translations[$str]);
                if ($number > 1) {
                    return vsprintf($plural, $args);
                } else {
                    return vsprintf($single, $args);
                }
            } else {
                return $this->translations[$str];
            }
        } else {
            return $str;
        }


    }

    public function has($str)
    {
        return isset($this->translations[$str]);
    }
}
