<?php

declare(strict_types=1);

namespace Suricate;

/**
 * @property string $id Formitem id attribute
 * @SuppressWarnings("StaticAccess")
 */
class FormItem
{
    public $objectHtmlProperties = [
        'type',
        'name',
        'id',
        'class',
        'accept',
        'capture',
        'value',
        'checked',
        'rows',
        'cols',
        'placeholder',
        'tabindex',
        'accesskey',
        'disabled',
        'spellcheck',
        'events',
        'multiple',
        'autocomplete',
        'autofocus',
        'required',
        'pattern',
        'min',
        'minlength',
        'step',
        'max',
        'maxlength',
        'src',
        'readonly',
        'data', // Array for data-attributes
    ];
    public $label;
    public $objectHtmlValues = [];
    public static $encoding = 'UTF-8';

    public function __construct($itemData = [])
    {
        foreach ($itemData as $itemProperty => $itemValue) {
            $this->$itemProperty = $itemValue;
        }
    }

    public function __get($name)
    {
        if (isset($this->objectHtmlValues[$name])) {
            return $this->objectHtmlValues[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->objectHtmlProperties)) {
            $this->objectHtmlValues[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->objectHtmlValues);
    }

    public static function input(
        $type,
        $name,
        $value = null,
        $label = null,
        $htmlAttributes = []
    ) {
        $itemData = [];
        $itemData['type'] = $type;
        $itemData['name'] = $name;
        $itemData['value'] = $value;
        $itemData['label'] = $label;
        if ($label !== null && !isset($htmlAttributes['id'])) {
            $itemData['id'] = $name;
        }
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output = $item->renderLabel();
        $output .= '<input';
        $output .= $item->renderAttributes();
        $output .= '/>';

        return $output;
    }

    public static function text(
        $name,
        $value = null,
        $label = null,
        $htmlAttributes = []
    ) {
        return static::input('text', $name, $value, $label, $htmlAttributes);
    }

    public static function password(
        $name,
        $value,
        $label = null,
        $htmlAttributes = []
    ) {
        return static::input(
            'password',
            $name,
            $value,
            $label,
            $htmlAttributes
        );
    }

    public static function number(
        $name,
        $value,
        $label = null,
        $htmlAttributes = []
    ) {
        return static::input('number', $name, $value, $label, $htmlAttributes);
    }

    public static function button(
        $name,
        $value,
        $label = null,
        $htmlAttributes = []
    ) {
        return static::input('button', $name, $value, $label, $htmlAttributes);
    }

    public static function checkbox(
        $name,
        $value = 1,
        $checked = false,
        $label = null,
        $htmlAttributes = []
    ) {
        if (!isset($htmlAttributes['checked']) && $checked) {
            $htmlAttributes['checked'] = 'checked';
        }

        return static::input(
            'checkbox',
            $name,
            $value,
            $label,
            $htmlAttributes
        );
    }

    public static function file($name, $label = null, $htmlAttributes = [])
    {
        return static::input('file', $name, null, $label, $htmlAttributes);
    }

    public static function hidden($name, $value, $htmlAttributes = [])
    {
        return static::input('hidden', $name, $value, null, $htmlAttributes);
    }

    public static function image($name, $url, $htmlAttributes = [])
    {
        $htmlAttributes['src'] = $url;

        return static::input('image', $name, null, null, $htmlAttributes);
    }

    public static function radio(
        $name,
        $availableValues = [],
        $value = null,
        $label = null,
        $htmlAttributes = []
    ) {
        $itemData = [];
        $itemData['name'] = $name;
        $itemData['value'] = $value;
        $itemData['label'] = $label;
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output = $item->renderLabel();
        $output .= '<div class="radio-list">';
        foreach ($availableValues as $currentValue => $currentLabel) {
            $htmlAttributes = ['id' => $name . '-' . $currentValue];
            if ($currentValue == $value) {
                $htmlAttributes['checked'] = 'checked';
            }

            $output .=
                '<div class="radio-item">' .
                FormItem::input(
                    'radio',
                    $name,
                    $currentValue,
                    $currentLabel,
                    $htmlAttributes
                ) .
                '</div>';
        }
        $output .= '</div>';

        return $output;
    }

    public static function reset($value = null, $htmlAttributes = [])
    {
        return static::input('reset', null, $value, null, $htmlAttributes);
    }

    public static function select(
        $name,
        $availableValues = [],
        $value = null,
        $label = null,
        $htmlAttributes = []
    ) {
        $itemData = [];
        $itemData['name'] = $name;
        $itemData['value'] = $value;
        $itemData['label'] = $label;
        if ($label !== null && !isset($htmlAttributes['id'])) {
            $itemData['id'] = $name;
        }
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output = $item->renderLabel();
        $output .= '<select';
        $output .= $item->renderAttributes(true);
        $output .= '>';
        foreach ($availableValues as $currentKey => $currentOption) {
            if (is_array($currentOption)) {
                $output .=
                    '<optgroup label="' .
                    htmlentities(
                        (string) $currentKey,
                        ENT_COMPAT,
                        static::$encoding
                    ) .
                    '">';
                foreach ($currentOption as $subKey => $subOption) {
                    if (is_array($value)) {
                        $selected = in_array($subKey, $value)
                            ? ' selected'
                            : '';
                    } else {
                        $selected = $subKey == $value ? ' selected' : '';
                    }
                    $output .=
                        '<option value="' .
                        htmlentities(
                            (string) $subKey,
                            ENT_COMPAT,
                            static::$encoding
                        ) .
                        '"' .
                        $selected .
                        '>' .
                        htmlentities(
                            (string) $subOption,
                            ENT_COMPAT,
                            static::$encoding
                        ) .
                        '</option>';
                }
                $output .= '</optgroup>';
            } else {
                if (is_array($value)) {
                    $selected = in_array($currentKey, $value)
                        ? ' selected'
                        : '';
                } else {
                    $selected = $currentKey == $value ? ' selected' : '';
                }
                $output .=
                    '<option value="' .
                    htmlentities(
                        (string) $currentKey,
                        ENT_COMPAT,
                        static::$encoding
                    ) .
                    '"' .
                    $selected .
                    '>' .
                    htmlentities(
                        (string) $currentOption,
                        ENT_COMPAT,
                        static::$encoding
                    ) .
                    '</option>';
            }
        }

        $output .= '</select>';

        return $output;
    }

    public static function submit(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input('submit', $name, $value, $label, $htmlAttributes);
    }

    public static function textarea(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        $itemData = [];
        $itemData['name'] = $name;

        $itemData['label'] = $label;
        if ($label !== null && !isset($htmlAttributes['id'])) {
            $itemData['id'] = $name;
        }
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output = $item->renderLabel();
        $output .= '<textarea';
        $output .= $item->renderAttributes(true);
        $output .= '>';
        $output .= $value;
        $output .= '</textarea>';

        return $output;
    }

    public static function tel($name, $value, $label = '', $htmlAttributes = [])
    {
        return static::input('tel', $name, $value, $label, $htmlAttributes);
    }

    public static function url($name, $value, $label = '', $htmlAttributes = [])
    {
        return static::input('url', $name, $value, $label, $htmlAttributes);
    }

    public static function email(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input('email', $name, $value, $label, $htmlAttributes);
    }

    public static function search(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input('search', $name, $value, $label, $htmlAttributes);
    }

    public static function date(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input('date', $name, $value, $label, $htmlAttributes);
    }

    public static function dateTime(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input(
            'datetime-local',
            $name,
            $value,
            $label,
            $htmlAttributes
        );
    }

    public static function time(
        $name,
        $value,
        $label = '',
        $htmlAttributes = []
    ) {
        return static::input('time', $name, $value, $label, $htmlAttributes);
    }

    public static function color(
        $name,
        $value = null,
        $label = null,
        $htmlAttributes = []
    ) {
        return static::input('color', $name, $value, $label, $htmlAttributes);
    }

    protected function renderLabel()
    {
        $output = '';
        if ($this->label != '') {
            $output .= '<label';
            if ($this->id != '') {
                $output .=
                    ' for="' .
                    htmlentities($this->id, ENT_COMPAT, static::$encoding) .
                    '"';
            }
            $output .= '>';
            $output .= $this->label;
            $output .= '</label>';
        }

        return $output;
    }

    protected function renderAttributes($skipValue = false)
    {
        $output = '';
        foreach ($this->objectHtmlProperties as $currentAttribute) {
            if ($currentAttribute === 'data' && is_array($this->$currentAttribute)) {
                foreach ($this->$currentAttribute as $dataKey => $dataValue) {
                    $output .=
                    ' data-' .
                    $dataKey .
                    '="' .
                    htmlentities(
                        (string) $dataValue,
                        ENT_COMPAT,
                        static::$encoding
                    ) .
                    '"';
                }
                continue;
            }
            if (!($currentAttribute == 'value' && $skipValue)) {
                if ($this->$currentAttribute !== null) {
                    $output .=
                        ' ' .
                        $currentAttribute .
                        '="' .
                        htmlentities(
                            (string) $this->$currentAttribute,
                            ENT_COMPAT,
                            static::$encoding
                        ) .
                        '"';
                }
            }
        }

        return $output;
    }
}
