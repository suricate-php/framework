<?php

declare(strict_types=1);

namespace Suricate\Traits;

trait ImageFilter
{
    private $filters = [
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
    ];

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

    protected function filter()
    {
        $args = func_get_args();
        $filterType = array_shift($args);

        if (in_array($filterType, $this->filters)) {
            $params = [$this->source, constant($filterType)];
            $params = array_values(array_merge($params, $args));

            call_user_func_array('imagefilter', $params);

            return $this;
        }

        throw new \InvalidArgumentException(
            'Unknown filter type ' . $filterType
        );
    }
}
