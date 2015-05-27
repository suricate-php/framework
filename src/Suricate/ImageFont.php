<?php
namespace Suricate;

class ImageFont
{
    const FONTTYPE_INTERNAL = 1;
    const FONTTYPE_TTF      = 2;

    private $colorResource;
    private $text;
    private $angle  = 0;
    private $size   = 8;
    private $fontType;
    private $font;
    private $image;

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
        $this->colorResource = imagecolorallocate($this->source, $color[0], $color[1], $color[2]);
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
        if ($this->fontType == self::FONTTYPE_INTERNAL) {
            imagestring($image, $this->font, $x, $y, $this->text, $this->colorResource);
        } else {
            imagettftext($image, $this->size, $this->angle, $x, $y, $this->colorResource, $this->font, $this->text);
        }
    }
}