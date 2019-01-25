<?php declare(strict_types=1);
namespace Suricate;

class Page
{
    protected $title;
    protected $encoding       = 'utf-8';
    protected $language       = 'en_US';
    protected $stylesheets    = [];
    protected $metas          = [];
    protected $scripts        = [];
    protected $rss            = [];
    protected $htmlClass      = [];

    public function __construct()
    {
    }

    /**
     * Set language passed to html tag
     *
     * @param string $language language to set
     * @return Page
     */
    public function setLanguage(string $language): Page
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Set encoding passed to html and rss tags
     *
     * @param string $encoding Encoding to set
     * @return Page
     */
    public function setEncoding(string $encoding): Page
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Set title of the page
     *
     * @param string $title Title of the page
     * @return Page
     */
    public function setTitle(string $title): Page
    {
        $this->title = $title;

        return $this;
    }

    //
    // Stylesheets
    //
    
    /**
     * Add a stylesheet
     * @param string $identifier Unique stylesheet identifier
     * @param string $url        Stylesheet URL
     * @param string $media      Stylesheet media (default: all)
     * @return Page
     */
    public function addStylesheet(string $identifier, string  $url, string $media = 'all'): Page
    {
        $this->stylesheets[$identifier] = [
            'url' => $url,
            'media' => $media
        ];

        return $this;
    }

    /**
     * Render stylesheets html tags
     * @return string Stylesheet HTML
     */
    protected function renderStylesheets()
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

    public function addHtmlClass($className)
    {
        $this->htmlClass[$className] = true;

        return $this;
    }

     /**
     * Add a RSS Feed
     * @param string $id  Unique stylesheet identifier
     * @param string $url Feed URL
     * @param string $title Title of the feed
     */
    public function addRss($id, $url, $title)
    {
        $this->rss[$id] = ['url' => $url, 'title' => $title];

        return $this;
    }

    protected function renderRss()
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

    protected function renderScripts()
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
        $this->metas[$name] = ['content' => $content, 'type' => 'name'];

        return $this;
    }

    public function addMetaProperty($name, $content)
    {
        $this->metas[$name] = ['content' => $content, 'type' => 'property'];
    }

    public function addMetaLink($name, $type, $href)
    {
        $this->metas[$name] = ['href' => $href, 'type' => 'rel', 'relType' => $type];
    }

    protected function renderMetas()
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
        $htmlClass = count($this->htmlClass) ? ' class="' . implode(' ', array_keys($this->htmlClass)) .'"' : '';
        $output  = '<!DOCTYPE html>' . "\n";
        $output .= '<html lang="' . substr($this->language, 0, 2) . '"' . $htmlClass . '>' . "\n";
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
