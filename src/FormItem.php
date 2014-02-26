<?php
namespace Fwk;

class FormItem
{
    public $objectHtmlProperties = array(
                                    'type',
                                    'name',
                                    'id',
                                    'class',
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
                                    'required',
                                    'pattern',
                                    'min',
                                    'step',
                                    'max'
                                );
    public $label;
    public $objectHtmlValues            = array();
    public static $encoding             = 'UTF-8';

    public function __construct($itemData = array())
    {
         foreach ($itemData as $itemProperty => $itemValue) {
            $this->$itemProperty = $itemValue;
        }
    }

    public function __get($name)
    {
        if (isset($this->objectHtmlValues[$name])) {
            return $this->objectHtmlValues[$name];
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->objectHtmlProperties)) {
            $this->objectHtmlValues[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return in_array($name, $this->objectHtmlProperties);
    }

    public static function input($type, $name, $value = null, $label = null, $htmlAttributes = array())
    {
        $itemData           = array();
        $itemData['type']   = $type;
        $itemData['name']   = $name;
        $itemData['value']  = $value;
        $itemData['label']  = $label;
        if ($label !== null && !isset($htmlAttributes['id'])) {
            $itemData['id']     = $name;
        }
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output  = $item->renderLabel();
        $output .= '<input';
        $output .= $item->renderAttributes();
        $output .= '/>';

        return $output;
    }

    public static function text($name, $value = null, $label = null, $htmlAttributes = array())
    {
        return static::input('text', $name, $value, $label, $htmlAttributes);
    }

    public static function password($name, $value, $label = null, $htmlAttributes = array())
    {
        return static::input('password', $name, $value, $label, $htmlAttributes);
    }

    public static function number($name, $value, $label = null, $htmlAttributes = array())
    {
        return static::input('number', $name, $value, $label, $htmlAttributes);
    }

    public static function button($name, $value, $label = null, $htmlAttributes = array())
    {
        return static::input('button', $name, $value, $label, $htmlAttributes);
    }

    public static function checkbox($name, $value = 1, $checked = false, $label = null, $htmlAttributes = array()) 
    {
        if (!isset($htmlAttributes['checked']) && $checked) {
            $htmlAttributes['checked'] = 'checked';
        }
        
        return static::input('checkbox', $name, $value, $label, $htmlAttributes);
    }

    public static function file($name, $value, $label = null, $htmlAttributes = array())
    {

    }

    public static function hidden($name, $value, $label = null, $htmlAttributes = array())
    {
        return static::input('hidden', $name, $value, $label, $htmlAttributes);
    }

    public static function image($name, $url, $htmlAttributes = array())
    {
        $htmlAttributes['src'] = $url;

        return static::input('email', $name, $value, $label, $htmlAttributes);
    }

    public static function radio($name, $availableValues = array(), $value = null, $label = null, $htmlAttributes = array(), $errors = array())
    {
        $itemData           = array();
        $itemData['name']   = $name;
        $itemData['value']  = $value;
        $itemData['label']  = $label;
        $itemData['errors'] = $errors;
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output  = $item->renderLabel();
        $output .= '<div class="radio-list">'."\n";
        foreach ($availableValues as $currentValue => $currentLabel) {
            $htmlAttributes = array('id' => $name . '-' . $currentValue);
            if ($currentValue == $value) {
                $htmlAttributes['checked'] = 'checked';
            }

            $output .= '<div class="radio-item">' . FormItem::input('radio', $name, $currentValue, $currentLabel, $htmlAttributes) . '</div>'."\n";
        }
        $output .= '</div>'."\n";

        return $output;
    }
    
    /*public static function radio($name, $value = null, $checked = false, $label = null, $htmlAttributes = array())
    {
        if (!isset($htmlAttributes['checked']) && $checked) {
            $htmlAttributes['checked'] = 'checked';
        }
        return static::input('radio', $name, $value, $label, $htmlAttributes);
    }
*/
    public static function reset($value = null, $htmlAttributes)
    {
        return static::input('reset', null, $value, $label, $htmlAttributes);
    }

    public static function select($name, $availableValues = array(), $value = null, $label = null, $htmlAttributes = array())
    {
        $itemData           = array();
        $itemData['name']   = $name;
        $itemData['value']  = $value;
        $itemData['label']  = $label;
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output  = $item->renderLabel();
        $output .= '<select';
        $output .= $item->renderAttributes();
        $output .= '>' . "\n";
        foreach ($availableValues as $currentKey=>$currentOption) {
            
            if (is_array($currentOption)) {
                $output .= '<optgroup label="' . $currentKey . '">'."\n";
                foreach ($currentOption as $subKey=>$subOption) {
                    $selected = $subKey == $value ? ' selected' : '';
                    $output .= '<option value="' . $subKey . '"' . $selected . '>' . $subOption . '</option>'."\n";
                }
                $output .= '</optgroup>'."\n";
            } else {
                $selected = $currentKey == $value ? ' selected' : '';
                $output .= '<option value="' . $currentKey . '"' . $selected . '>' . $currentOption . '</option>'."\n";
            }
        }

        $output .= '</select>';

        return $output;
    }

    public static function submit($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('submit', $name, $value, $label, $htmlAttributes);
    }

    public static function textarea($name, $value, $label = '', $htmlAttributes = array())
    {
        $itemData           = array();
        $itemData['name']   = $name;
        $itemData['value']  = $value;
        $itemData['label']  = $label;
         if ($label !== null && !isset($htmlAttributes['id'])) {
            $itemData['id']     = $name;
        }
        $itemData = array_merge($itemData, $htmlAttributes);

        $item = new FormItem($itemData);

        $output  = $item->renderLabel();
        $output .= '<textarea';
        $output .= $item->renderAttributes(true);
        $output .= '>';
        $output .= $item->value;
        $output .= '</textarea>'."\n";

        return $output;
    }

    public static function tel($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('tel', $name, $value, $label, $htmlAttributes);
    }

    public static function url($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('url', $name, $value, $label, $htmlAttributes);
    }

    public static function email($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('email', $name, $value, $label, $htmlAttributes);
    }

    public static function search($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('search', $name, $value, $label, $htmlAttributes);
    }

    public static function date($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('date', $name, $value, $label, $htmlAttributes);
    }

    public static function dateTime($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('datetime', $name, $value, $label, $htmlAttributes);
    }

    public static function time($name, $value, $label = '', $htmlAttributes = array())
    {
        return static::input('time', $name, $value, $label, $htmlAttributes);
    }

    protected function renderLabel()
    {
        $output = '';
        if ($this->label != '') {
            $output .= '<label';
            if ($this->id != '') {
                $output .= ' for="' . htmlentities($this->id, ENT_COMPAT, static::$encoding) . '"';
            }
            $output .= '>';
            $output .= $this->label;
            $output .= '</label>'."\n";
        }

        return $output;
    }

    protected function renderAttributes($skipValue = false)
    {
        $output = '';
        foreach ($this->objectHtmlProperties as $currentAttribute) {
            if (!($currentAttribute == 'value' && $skipValue)) {
                if ($this->$currentAttribute !== null) {
                    $output .= ' ' . $currentAttribute . '="' . htmlentities($this->$currentAttribute, ENT_COMPAT, static::$encoding) . '"';
                }
            }
        }

        return $output;
    }
}