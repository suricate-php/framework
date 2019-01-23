<?php
namespace Suricate;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess, Interfaces\ICollection
{
    protected $items   = [];
    public $pagination = [
        'nbPages'   => 0,
        'page'      => 1,
        'nbItems'   => 0,
    ];

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function paginate($nbItemPerPage, $currentPage = 1)
    {
        $this->pagination['page']       = $currentPage;
        $this->pagination['nbItems']    = count($this->items);
        $this->pagination['nbPages']    = ceil($this->pagination['nbItems'] / $nbItemPerPage);

        $this->items = array_slice($this->items, ($currentPage - 1) * $nbItemPerPage, $nbItemPerPage);

        return $this;
    }

    public function getPossibleValuesFor($args, $key = null)
    {
        if (!is_array($args)) {
            $args = [
                'format' => '%s',
                'data' => [$args]
            ];
        }

        $values = [];
        foreach ($this->items as $item) {
            $itemValues = [];
            foreach ($args['data'] as $arg) {
                $itemValues[] = dataGet($item, $arg);
            }
            $arrayKey = ($key !== null) ? dataGet($item, $key) : null;
            $values[$arrayKey] = vsprintf($args['format'], $itemValues);
        }

        return $values;
    }

    public function getValuesFor($name)
    {
        $values = [];
        foreach ($this->items as $item) {
            $values[] = dataGet($item, $name);
        }

        return $values;
    }

    public function getItems()
    {
        return $this->items;
    }

    /*

    

    public function getItemFromKey($key)
    {
        $invertedMapping = array_flip($this->mapping);
        if (isset($invertedMapping[$key])) {
            return $this->items[$invertedMapping[$key]];
        }
    }
*/
    /**
     * Implementation of countable interface
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Implementation of IteratorAggregate Interface
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Implementation of ArrayAccess interface
     *
     * @param  mixed $offset Offset to verify
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->items);
    }

    /**
     * Implementation of ArrayAccess Interface
     *
     * @param  mixed $offset Offset to get
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->items)) {
            return $this->items[$offset];
        }
        return null;
        /*
        $item = isset($this->items[$offset]) ? $this->items[$offset] : null;
        if (gettype($item) == 'object' || $item == null) {
            return $item;
        }
       // Lazy load
        $itemType = $this::ITEM_TYPE;
        $itemToLoad = new $itemType;
        $itemToLoad->load($this->items[$offset]);

        $this->items[$offset] = $itemToLoad;

        return $this->items[$offset];*/
    }

    /**
     * Implementation of ArrayAccess Interface
     *
     * @param mixed $offset Offset to set
     * @param mixed $value  Value to set
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Implementation of ArrayAccess Interface
     *
     * @param mixed $offset Offset to unset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    // Helpers

    /**
     * Get first item of the collection
     *
     * @return mixed
     */
    public function first()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    /**
     * Get last item of the collection
     *
     * @return mixed
     */
    public function last()
    {
        if (count($this->items)) {
            return end($this->items);
        }
        
        return null;
    }

    /**
     * Check if collection is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Return the sum of the collection
     *
     * @param mixed $field Field to use for sum
     * @return double|integer
     */
    public function sum($field = null)
    {
        if ($field === null) {
            return array_sum($this->items);
        }
        $result = 0;
        foreach ($this->items as $item) {
            $result += dataGet($item, $field);
        }
        return $result;
    }

    public function random($nbItems = 1)
    {
        if ($this->isEmpty()) {
            return null;
        }

        $keys = array_rand($this->items, $nbItems);

        if (is_array($keys)) {
            return array_intersect_key($this->items, array_flip($keys));
        }
        
        return $this->items[$keys];
    }

    public function shuffle()
    {
        shuffle($this->items);

        return $this;
    }

    public function unique()
    {
        return new static(array_unique($this->items));
    }

    /**
     * Apply a closure to each element of the collection
     *
     * @param \Closure $callback Closure to apply
     * @return Collection
     */
    public function each(\Closure $callback): Collection
    {
        array_map($callback, $this->items);
        return $this;
    }

    /**
     * Sort a collection using a closure
     *
     * @param \Closure $closure Closure to apply for sorting, similar to uasort() closure
     * @return Collection
     */
    public function sort(\Closure $closure): Collection
    {
        uasort($this->items, $closure);

        return $this;
    }

    public function sortBy($field, $reverse = false)
    {
        if ($reverse) {
            $sortFunction = function ($a, $b) use ($field) {
                $first = dataGet($a, $field);
                $second = dataGet($b, $field);
                if ($first == $second) {
                    return 0;
                }
                return ($first > $second) ? -1 : 1;
            };
        } else {
            $sortFunction = function ($a, $b) use ($field) {
                $first = dataGet($a, $field);
                $second = dataGet($b, $field);
                if ($first == $second) {
                    return 0;
                }
                return ($first < $second) ? -1 : 1;
            };
        }


        usort($this->items, $sortFunction);

        return $this;
    }

    public function filter(\Closure $closure)
    {
        return new static(array_filter($this->items, $closure));
    }

    public function search($value, $strict = false)
    {
        return array_search($value, $this->items, $strict);
    }

    public function has($key)
    {
        return $this->offsetExists($key);
    }

    public function keys()
    {
        return array_keys($this->items);
    }

    public function prepend($item)
    {
        array_unshift($this->items, $item);

        return $this;
    }

    public function push($item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function put($key, $val)
    {
        $this->items[$key] = $val;

        return $this;
    }
    public function shift()
    {
        return array_shift($this->items);
    }
    
    public function pop()
    {
        return array_pop($this->items);
    }

    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    public function take($limit = null)
    {
        if ($limit < 0) {
            return $this->slice(abs($limit), $limit);
        }

        return $this->slice(0, $limit);
    }

    public function splice($offset, $length = null, $replacement = [])
    {
        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    public function chunk($size, $preserveKeys = false)
    {
        $result = new static;
        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $result->push(new static($chunk));
        }
        return $result;
    }
}
