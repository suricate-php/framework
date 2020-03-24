<?php

declare(strict_types=1);

namespace Suricate;

class ImageFont
{
    const FONTTYPE_INTERNAL = 1;
    const FONTTYPE_TTF = 2;

    private $color = [0, 0, 0];
    private $text;
    private $angle = 0;
    private $size = 8;
    private $fontType;
    private $font;

    public function __construct($text = '')
    {
        $this->text = $text;
    }

    public function font($fontFile)
    {
        $this->fontType = is_int($fontFile)
            ? self::FONTTYPE_INTERNAL
            : self::FONTTYPE_TTF;

        $this->font = $fontFile;
    }

    public function size($size)
    {
        $this->size = $size;

        return $this;
    }

    public function angle($angle)
    {
        $this->angle = $angle;

        return $this;
    }

    public function color(array $color)
    {
        $this->color = $color;

        return $this;
    }

    private function createColor($image)
    {
        return imagecolorallocate(
            $image,
            $this->color[0],
            $this->color[1],
            $this->color[2]
        );
    }

    public function align($align)
    {
    }

    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    public function valign($align)
    {
    }

    /**
     * Get text bounding box
     *
     * @return void
     */
    public function getBoundingBox(): array
    {
        return imageftbbox($this->size, $this->angle, $this->font, $this->text);
    }

    public function apply(&$image, $x = 0, $y = 0)
    {
        $colorResource = $this->createColor($image);

        if ($this->fontType == self::FONTTYPE_INTERNAL) {
            imagestring(
                $image,
                $this->font,
                $x,
                $y,
                $this->text,
                $colorResource
            );
            return;
        }
        imagettftext(
            $image,
            $this->size,
            $this->angle,
            $x,
            $y,
            $colorResource,
            $this->font,
            $this->text
        );
    }
}
