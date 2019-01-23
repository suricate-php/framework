<?php
namespace Suricate\Traits;

trait DBObjectExport
{
    /**
     * Export DBObject to array
     *
     * @return array
     */
    public function toArray()
    {
        $this->setExportedVariables();
        $result = [];
        foreach ($this->exportedVariables as $sourceName => $destinationName) {
            $omitEmpty  = false;
            $castType   = null;
            if (strpos($destinationName, ',') !== false) {
                $splitted   = explode(',', $destinationName);
                array_map(function ($item) use (&$castType, &$omitEmpty) {
                    if ($item === 'omitempty') {
                        $omitEmpty = true;
                        return;
                    }
                    if (substr($item, 0, 5) === 'type:') {
                        $castType = substr($item, 5);
                    }
                }, $splitted);

                $destinationName = $splitted[0];
            }

            if ($destinationName === '-') {
                continue;
            }

            if ($omitEmpty && empty($this->$sourceName)) {
                continue;
            }
            $value = $this->$sourceName;
            if ($castType !== null) {
                settype($value, $castType);
            }
            $result[$destinationName] = $value;
        }

        return $result;
    }

    /**
     * Export DBObject to JSON format
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}