<?php

declare(strict_types=1);

namespace Suricate;

class ImageFont
{
    const FONTTYPE_INTERNAL = 1;
    const FONTTYPE_TTF = 2;

    private $color;
    private $text;
    private $angle = 0;
    private $size = 8;
    private $fontType;
    private $font;
    private $image;

    public function __construct($text = '')
    {
        $this->text = $text;
    }

    public function font($fontFile)
    {
        if (is_int($fontFile)) {
            $this->fontType = self::FONTTYPE_INTERNAL;
        } else {
            $this->fontType = self::FONTTYPE_TTF;
        }

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

    public function color($color)
    {
        $this->color = $color;
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
