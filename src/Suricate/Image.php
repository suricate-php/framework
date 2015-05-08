<?php
namespace Suricate;

class Image
{
    private $source;
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

    }

    public function asGrayscale()
    {

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

    public function filter($filterType)
    {
        if (isset($this->filters[$filterType])) {

        } else {
            throw new \InvalidArgumentException('Unknown filter type ' . $filterType);
        }
        // See http://wideimage.sourceforge.net/documentation/manipulating-images/
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
}