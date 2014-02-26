<?php
namespace Fwk;

class Page
{
    private $title;
    private $encoding       = 'utf-8';
    private $language       = 'en_US';
    private $stylesheets    = array();
    private $metas          = array();
    private $scripts        = array();
    private $rss            = array();

    public function __construct()
    {

    }


    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    //
    // Stylesheets
    //
    
    /**
     * Add a stylesheet
     * @param string $id  Unique stylesheet identifier
     * @param string $url Stylesheet URL
     * @param string $media Stylesheet media (default: all)
     */
    public function addStylesheet($id, $url, $media = 'all')
    {
        $this->stylesheets[$id] = array('url' => $url, 'media' => $media);

        return $this;
    }

    /**
     * Render stylesheets html tags
     * @return string Stylesheet HTML
     */
    private function renderStylesheets()
    {
        $output = '';
        foreach ($this->stylesheets as $id => $stylesheet) {
            $output .= '<link rel="stylesheet"';
            $output .= ' id="' . $id . '"';
            $output .= ' href="' . $stylesheet['url'] . '"';
            $output .= ' type="text/css"';
            $output .= ' media="' . $stylesheet['media'] . '"';
            $output .= '/>' . "\n";
        }

        return $output;
    }

     /**
     * Add a RSS Feed
     * @param string $id  Unique stylesheet identifier
     * @param string $url Feed URL
     * @param string $title Title of the feed
     */
    public function addRss($id, $url, $title)
    {
        $this->rss[$id] = array('url' => $url, 'title' => $title);

        return $this;
    }

    private function renderRss()
    {
        $output = '';
        foreach ($this->rss as $id => $rss) {
            $output .= '<link rel="alternate"';
            $output .= ' id="' . $id . '"';
            $output .= ' href="' . $rss['url'] . '"';
            $output .= ' type="application/rss+xml"';
            $output .= ' media="' . htmlentities($rss['title'], ENT_COMPAT, $this->encoding) . '"';
            $output .= '/>' . "\n";
        }
        return $output;
    }

    //
    // Scripts
    //
    public function addScript($id, $url)
    {
        $this->scripts[$id] = $url;

        return $this;
    }

    private function renderScripts()
    {
        $output = '';
        
        foreach ($this->scripts as $currentScriptUrl) {
            $output .= '<script type="text/javascript" src="' . $currentScriptUrl . '"></script>' . "\n";
        }

        return $output;
    }

    //
    // Metas
    //
    public function addMeta($name, $content)
    {
        $this->metas[$name] = array('content' => $content, 'type' => 'name');

        return $this;
    }

    public function addMetaProperty($name, $content)
    {
        $this->metas[$name] = array('content' => $content, 'type' => 'property');
    }

    public function addMetaLink($name, $type, $href)
    {
        $this->metas[$name] = array('href' => $href, 'type' => 'rel', 'relType' => $type);
    }

    private function renderMetas()
    {
        $output = '';
        foreach ($this->metas as $name => $metaData) {
            if ($metaData['type'] == 'name') {
                $output .= '<meta name="' . $name . '" content="' . $metaData['content'] . '"/>' . "\n";
            } elseif ($metaData['type'] == 'property') {
                $output .= '<meta property="' . $name . '" content="' . $metaData['content'] . '"/>' . "\n";
            } elseif ($metaData['type'] == 'rel') {
                $output .= '<link rel="' . $metaData['relType'] . '" href="' . $metaData['href'] . '"/>'."\n";
            }
        }

        return $output;
    }

    public function render($content = '')
    {
        $output  = '<!DOCTYPE html>' . "\n";
        $output .= '<html lang="' . substr($this->language, 0, 2) . '">' . "\n";
        $output .= '    <head>' . "\n";
        $output .= '        <title>' . $this->title . '</title>' . "\n";
        $output .= '        <meta http-equiv="Content-Type" content="text/html; charset=' . $this->encoding . '" />'."\n";
        $output .=          $this->renderMetas();
        $output .=          $this->renderStylesheets();
        $output .=          $this->renderScripts();
        $output .=          $this->renderRss();
        $output .= '    </head>' . "\n";
        $output .= '    <body>' . "\n";
        $output .= $content;
        $output .= '    </body>'."\n";
        $output .= '</html>' . "\n";

        return $output;
    }
}
