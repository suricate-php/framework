<?php
namespace Fwk;

class Image
{
    private $source;
    private $destination;

    private $width;
    private $height;

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

    public function resize($width, $height)
    {
        if ($this->source) {
            $testH = round(($width / $this->width) * $this->height);
            $testW = round(($height / $this->height) * $this->width);
            
            if ($testH > $height) {
                $width = $testW;
            } else {
                $height = $testH;
            }
        
            $this->destination = ImageCreateTrueColor($width,$height); 
            ImageCopyResampled(
                $this->destination,
                $this->source,
                0,
                0,
                0,
                0,
                $width,$height,$this->width,
                $this->height
            );
        }
        return $this;
    }

    public function crop()
    {

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
                    $result = imagejpeg($this->destination, $filename, $quality);
                    break;
                case 'png':
                    $result = imagepng($this->destination, $filename);
                    break;
                case 'gif':
                    $result = imagegif($this->destination, $filename);
                    break;
            }
        }

        return $this;
    }
}