<?php
namespace Suricate;

class ImageShape
{
    private $image;
    private $color = [0, 0, 0];

    public function setImage(&$image)
    {
        $this->image = $image;

        return $this;
    }

    public function color($color)
    {
        $this->color = $color;

        return $this;
    }

    public function createColor()
    {
        return imagecolorallocate($this->image, $this->color[0], $this->color[1], $this->color[2]);
    }

    public function drawLine($x1, $y1, $x2, $y2)
    {
        imageline($this->image, $x1, $y1, $x2, $y2, $this->createColor());

        return $this;
    }
}
