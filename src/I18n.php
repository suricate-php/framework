<?php
namespace Fwk;

class I18n extends Service
{
    protected $parametersList = array(
        'locale'
    );
    private $baseLocaleDir = 'I18n';
    private $translations;

    public function i18nList()
    {
        $langDir    =  Fwk::App()->getParameter('root') . DIRECTORY_SEPARATOR
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

    public function load()
    {
        $filename    = Fwk::App()->getParameter('root') . DIRECTORY_SEPARATOR
            . $this->baseLocaleDir . DIRECTORY_SEPARATOR
            . $this->locale . DIRECTORY_SEPARATOR
            . 'language.php';
            
        if (is_readable($filename)) {
            $this->translations = include $filename;
        } else {
            Fwk::Logger()->debug(sprintf('Missing translation file for %s', $this->locale));
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
