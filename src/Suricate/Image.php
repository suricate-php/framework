<?php

declare(strict_types=1);

namespace Suricate;

use InvalidArgumentException;
use RuntimeException;

class Image
{
    use Traits\ImageFilter;

    public $source;
    private $destination;

    private $width;
    private $height;

    public function load($filename)
    {
        if (is_file($filename) && ($imgString = file_get_contents($filename))) {
            $imgString = @imagecreatefromstring($imgString);
            if ($imgString !== false) {
                $this->source = $imgString;

                if (is_callable('exif_read_data')) {
                    $exif = @exif_read_data($filename, 'IFD0');
                    $exifOrientation = $exif['Orientation'] ?? 0;
                    $orientation = 0;
                    if (in_array($exifOrientation, [3, 6, 8])) {
                        $orientation = $exifOrientation;
                    }
                    if ($orientation == 3) {
                        $this->source = imagerotate($this->source, 180, 0);
                    }
                    if ($orientation == 8) {
                        $this->source = imagerotate($this->source, 90, 0);
                    }
                    if ($orientation == 6) {
                        $this->source = imagerotate($this->source, -90, 0);
                    }
                }
                $this->destination = $this->source;
                $this->width = imagesx($this->source);
                $this->height = imagesy($this->source);
                return $this;
            }

            throw new InvalidArgumentException(
                'Cannot load ' . $filename . ', not an image'
            );
        }

        throw new InvalidArgumentException(
            'Cannot load ' . $filename . ', file unreadable'
        );
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getResource()
    {
        return $this->source;
    }

    public function setResource($source)
    {
        if (!is_resource($source) && !($source instanceof \GdImage)) {
            throw new InvalidArgumentException("Invalid source");
        }

        $this->source = $source;
        $this->width = imagesx($this->source);
        $this->height = imagesy($this->source);

        return $this;
    }

    public function create(int $width, int $height)
    {
        $this->source = imagecreatetruecolor($width, $height);
        $this->destination = $this->source;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Fill an image with a color
     *
     * @param integer $pointX  X point coordinate
     * @param integer $pointY  Y point coordinate
     * @param array   $color   RGB color
     *
     */
    public function fill(int $pointX, int $pointY, $color = [0, 0, 0])
    {
        $color = imagecolorallocate(
            $this->destination,
            $color[0],
            $color[1],
            $color[2]
        );

        imagefill($this->destination, $pointX, $pointY, $color);

        return $this->chain();
    }

    /**
     * Return true if image is in portrait mode
     *
     * @return boolean
     */
    public function isPortrait(): bool
    {
        return $this->width < $this->height;
    }

    /**
     * Return true if image is in landscape mode
     *
     * @return boolean
     */
    public function isLandscape(): bool
    {
        return $this->height < $this->width;
    }

    /**
     * Apply modification to destination image
     *
     */
    public function chain(): self
    {
        $this->source = $this->destination;
        $this->width = imagesx($this->source);
        $this->height = imagesy($this->source);

        return $this;
    }

    public function resize($width = null, $height = null)
    {
        if ($this->source) {
            if ($width == null) {
                $width = intval(
                    round(($height / $this->height) * $this->width, 0)
                );
            } elseif ($height == null) {
                $height = intval(
                    round(($width / $this->width) * $this->height, 0)
                );
            }

            $this->destination = imagecreatetruecolor($width, $height);
            if ($this->destination === false) {
                throw new RuntimeException("Can't create destination image");
            }
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

        $cropWidthHalf = round($width / 2);
        $cropHeightHalf = round($height / 2);

        $x1 = intval(max(0, $centerX - $cropWidthHalf));
        $y1 = intval(max(0, $centerY - $cropHeightHalf));

        $this->destination = imagecreatetruecolor($width, $height);
        if ($this->destination === false) {
            throw new RuntimeException("Can't create destination image");
        }
        imagecopy(
            $this->destination,
            $this->source,
            0,
            0,
            $x1,
            $y1,
            $width,
            $height
        );

        return $this->chain();
    }

    public function resizeCanvas(
        $width,
        $height,
        $position = null,
        $color = [0, 0, 0]
    ) {
        $this->destination = imagecreatetruecolor($width, $height);
        if ($this->destination === false) {
            throw new RuntimeException("Can't create destination image");
        }
        $colorRes = imagecolorallocate(
            $this->destination,
            $color[0],
            $color[1],
            $color[2]
        );
        $imageObj = new Image();
        $imageObj->width = $width;
        $imageObj->height = $height;
        imagefill($this->destination, 0, 0, $colorRes);

        if ($position !== null) {
            list($x, $y) = $imageObj->getCoordinatesFromString(
                $position,
                $this->width,
                $this->height
            );
        } else {
            $x = 0;
            $y = 0;
        }
        imagecopy(
            $this->destination,
            $this->source,
            $x,
            $y,
            0,
            0,
            $this->width,
            $this->height
        );

        return $this->chain();
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

    public function merge(
        $source,
        $position = null,
        $x = null,
        $y = null,
        $percent = 100
    ) {
        if ($source instanceof \Suricate\Image) {
        } else {
            $source = with(new Image())->load($source);
        }

        if ($position !== null) {
            list($x, $y) = $this->getCoordinatesFromString(
                $position,
                $source->width,
                $source->height
            );
        }
        $x = $x !== null ? $x : 0;
        $y = $y !== null ? $y : 0;

        // Handle transparent image
        // creating a cut resource
        $cut = imagecreatetruecolor($source->getWidth(), $source->getHeight());
        if ($cut === false) {
            throw new RuntimeException("Can't create destination image");
        }
        // copying relevant section from background to the cut resource
        imagecopy(
            $cut,
            $this->destination,
            0,
            0,
            $x,
            $y,
            $source->getWidth(),
            $source->getHeight()
        );

        // copying relevant section from watermark to the cut resource
        imagecopy(
            $cut,
            $source->source,
            0,
            0,
            0,
            0,
            $source->getWidth(),
            $source->getHeight()
        );

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

    /**
     * Export image
     *
     * @param string $outputType output format
     * @param integer $quality   Output quality, when available
     *
     * @return void
     */
    public function export($outputType, $quality = 70)
    {
        switch ($outputType) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->source, null, $quality);
                break;
            case 'png':
                imagepng($this->source);
                break;
            case 'gif':
                imagegif($this->source);
                break;
            case 'webp':
                imagewebp($this->source, null, $quality);
                break;
            default:
                throw new InvalidArgumentException(
                    sprintf("Invalid output format %s", $outputType)
                );
        }
    }

    public function save($filename, $outputType = null, $quality = 70)
    {
        $result = false;

        $extension =
            $outputType === null
            ? pathinfo($filename, PATHINFO_EXTENSION)
            : $outputType;

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
                case 'webp':
                    $result = imagewebp($this->source, $filename, $quality);
                    break;
            }
        }

        return $result;
    }

    private function getCoordinatesFromString(
        $position,
        $offsetWidth = 0,
        $offsetHeight = 0
    ) {
        switch ($position) {
            case 'top':
                $x = floor($this->width / 2 - $offsetWidth / 2);
                $y = 0;
                break;
            case 'top-right':
                $x = $this->width - $offsetWidth;
                $y = 0;
                break;
            case 'left':
                $x = 0;
                $y = floor($this->height / 2 - $offsetHeight / 2);
                break;
            case 'center':
                $x = floor($this->width / 2 - $offsetWidth / 2);
                $y = floor($this->height / 2 - $offsetHeight / 2);
                break;
            case 'right':
                $x = $this->width - $offsetWidth;
                $y = floor($this->height / 2 - $offsetHeight / 2);
                break;
            case 'bottom-left':
                $x = 0;
                $y = $this->height - $offsetHeight;
                break;
            case 'bottom':
                $x = floor($this->width / 2 - $offsetWidth / 2);
                $y = $this->height - $offsetHeight;
                break;
            case 'bottom-right':
                $x = $this->width - $offsetWidth;
                $y = $this->height - $offsetHeight;
                break;

            case 'top-left':
            default:
                $x = 0;
                $y = 0;
                break;
        }

        return [intval($x), intval($y)];
    }
}
