<?php
namespace Suricate;

class Image
{
    public $source;
    private $destination;

    private $width;
    private $height;
    private $filters = array(
        'IMG_FILTER_NEGATE',
        'IMG_FILTER_GRAYSCALE',
        'IMG_FILTER_BRIGHTNESS',
        'IMG_FILTER_CONTRAST',
        'IMG_FILTER_COLORIZE',
        'IMG_FILTER_EDGEDETECT',
        'IMG_FILTER_EMBOSS',
        'IMG_FILTER_GAUSSIAN_BLUR',
        'IMG_FILTER_SELECTIVE_BLUR',
        'IMG_FILTER_MEAN_REMOVAL',
        'IMG_FILTER_SMOOTH',
        'IMG_FILTER_PIXELATE'
        );

    public function __construct()
    {
    }

    public function load($filename)
    {
        if (is_file($filename)) {
            $this->source = imagecreatefromstring(file_get_contents($filename));
            if ($this->source !== false) {
                $this->destination = $this->source;
                $this->width = imagesx($this->source);
                $this->height = imagesy($this->source);
            }
        } else {
            throw new \InvalidArgumentException('Cannot load ' . $filename);
        }
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function isPortrait()
    {
        return $this->width < $this->height;
    }

    public function isLandscape()
    {
        return $this->height < $this->width;
    }

    public function chain()
    {
        $this->source   = $this->destination;
        $this->width    = imagesx($this->source);
        $this->height   = imagesy($this->source);

        return $this;
    }

    public function resize($width = null, $height = null)
    {
        if ($this->source) {
            if ($width == null) {
                $width = round(($height / $this->height) * $this->width);
            } elseif ($height == null) {
                $height = round(($width / $this->width) * $this->height);
            }

            $this->destination = imagecreatetruecolor($width,$height); 
            imagecopyresampled(
                $this->destination,
                $this->source,
                0,
                0,
                0,
                0,
                $width,
                $height,
                $this->width,
                $this->height
            );

            return $this->chain();
        }
        return $this;
    }

    public function crop($width, $height)
    {
        $centerX = round($this->width / 2);
        $centerY = round($this->height / 2);

        $cropWidthHalf  = round($width / 2);
        $cropHeightHalf = round($height / 2);

        $x1 = max(0, $centerX - $cropWidthHalf);
        $y1 = max(0, $centerY - $cropHeightHalf);
        
        $this->destination = imagecreatetruecolor($width, $height);
        imagecopy($this->destination, $this->source, 0, 0, $x1, $y1, $width, $height);

        return $this->chain();
    }

    public function asNegative()
    {
        return $this->filter('IMG_FILTER_NEGATE');
    }

    public function asGrayscale()
    {
        return $this->filter('IMG_FILTER_GRAYSCALE');
    }

    public function setBrightness($level)
    {
        return $this->filter('IMG_FILTER_BRIGHTNESS', $level);
    }

    public function setContrast($level)
    {
        return $this->filter('IMG_FILTER_CONTRAST', $level);
    }

    public function colorize($r, $g, $b, $alpha)
    {
        $this->filter('IMG_FILTER_COLORIZE', $r, $g, $b, $alpha);

        return $this;
    }

    public function detectEdge()
    {
        return $this->filter('IMG_FILTER_EDGEDETECT');
    }

    public function emboss()
    {
        return $this->filter('IMG_FILTER_EMBOSS');
    }

    public function blur()
    {
        return $this->filter('IMG_FILTER_GAUSSIAN_BLUR');
    }

    public function selectiveBlur()
    {
        return $this->filter('IMG_FILTER_SELECTIVE_BLUR');
    }

    public function meanRemoval()
    {
        return $this->filter('IMG_FILTER_MEAN_REMOVAL');
    }

    public function smooth($level)
    {
        return $this->filter('IMG_FILTER_SMOOTH', $level);
    }

    public function pixelate($size)
    {
        return $this->filter('IMG_FILTER_PIXELATE', $size);
    }

    public function rotate()
    {

    }

    public function mirror()
    {

    }

    public function flip()
    {

    }

    public function merge($source, $position = null, $x = null, $y = null, $percent = 100)
    {
        if ($source instanceof Suricate\Image) {

        } else {
            $source = with(new Image())->load($source);
        }

        if ($position !== null) {
            list($x, $y) = $this->getCoordinatesFromString($position, $source->width, $source->height);
        }
        $x = $x !== null ? $x : 0;
        $y = $y !== null ? $y : 0;

        // Handle transparent image
        // creating a cut resource 
        $cut = imagecreatetruecolor($source->getWidth(), $source->getHeight()); 

        // copying relevant section from background to the cut resource 
        imagecopy($cut, $this->destination, 0, 0, $x, $y, $source->getWidth(), $source->getHeight()); 
        
        // copying relevant section from watermark to the cut resource 
        imagecopy($cut, $source->source, 0, 0, 0, 0, $source->getWidth(), $source->getHeight()); 

        imagecopymerge(
            $this->destination,
            $cut,
            $x,
            $y,
            0,
            0,
            $source->getWidth(),
            $source->getHeight(),
            $percent
        );

        return $this->chain();
    }

    public function writeText($text, $x = 0, $y = 0, \Closure $callback = null)
    {
        if ($x < 0) {
            $x = $this->width + $x;
        }
        if ($y < 0) {
            $y = $this->height + $y;
        }
        $imageFont = new ImageFont($text);

        if ($callback != null) {
            $callback($imageFont);
        }

        $imageFont->apply($this->source, $x, $y);
        
        return $this;
    }

    public function line($x1, $y1, $x2, $y2, \Closure $callback = null)
    {
        $imageShape = new ImageShape();
        $imageShape->setImage($this->source);
        if ($callback != null) {
            $callback($imageShape);
        }

        $imageShape->drawLine($x1, $y1, $x2, $y2);

        return $this;
    }

    protected function filter()
    {
        $args = func_get_args();
        $filterType = array_shift($args);
        
        if (in_array($filterType, $this->filters)) {
            $params = array(
                $this->source,
                constant($filterType)
            );
            $params = array_merge($params, $args);

            call_user_func_array('imagefilter', $params);
            
            return $this;
        } else {
            throw new \InvalidArgumentException('Unknown filter type ' . $filterType);
        }
    }

    public function save($filename, $outputType = null, $quality = 70)
    {
        $result = false;
        if ($outputType === null) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
        } else {
            $extension = $outputType;
        }
        if ($extension !== false) {
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $result = imagejpeg($this->source, $filename, $quality);
                    break;
                case 'png':
                    $result = imagepng($this->source, $filename);
                    break;
                case 'gif':
                    $result = imagegif($this->source, $filename);
                    break;
            }
        }

        return $result;
    }

    private function getCoordinatesFromString($position, $offsetWidth = 0, $offsetHeight = 0)
    {
        switch ($position) {
            case 'top-left':
                $x = 0;
                $y = 0;
                break;
            case 'top':
                $x = floor(($this->width / 2) - ($offsetWidth / 2));
                $y = 0;
                break;
            case 'top-right':
                $x = $this->width - $offsetWidth;
                $y = 0;
                break;
            case 'left':
                $x = 0;
                $y = floor(($this->height / 2) - ($offsetHeight / 2));
                break;
            case 'center':
                $x = floor(($this->width / 2) - ($offsetWidth / 2));
                $y = floor(($this->height / 2) - ($offsetHeight / 2));
                break;
            case 'right':
                $x = $x = $this->width - $offsetWidth;
                $y = floor(($this->height / 2) - ($offsetHeight / 2));
                break;
            case 'bottom-left':
                $x = 0;
                $y = $this->height - $offsetHeight;
                break;
            case 'bottom':
                $x = floor(($this->width / 2) - ($offsetWidth / 2));
                $y = $this->height - $offsetHeight;
                break;
            case 'bottom-right':
                $x = $x = $this->width - $offsetWidth;
                $y = $this->height - $offsetHeight;
                break;
        }

        return [$x, $y];
    }
}