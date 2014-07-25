<?php

namespace Solution10\Collection;

/**
 * Solution10: Collection
 *
 * The Collection class is a general purpose class that holds a collection or set of data.
 * Think of it as an array on steroids. Useful features of Collections are quick slicing and
 * selecting portions of the data set and basic querying of the dataset.
 *
 * @package       Solution10
 * @category      Collection
 * @author        Alex Gisby <alex@solution10.com>
 * @license       MIT
 */
class Collection implements \Countable, \ArrayAccess, \Iterator
{
    const SORT_ASC = 1;
    const SORT_ASC_PRESERVE_KEYS = 2;
    const SORT_DESC = 3;
    const SORT_DESC_PRESERVE_KEYS = 4;

    /**
     * @var    array    Data container
     */
    protected $contents = array();

    /**
     * @var    array    Selectors
     */
    protected $selectors = array();

    /**
     * @var     int     Current position of the cycle
     * @see self::cycleForward() and self::cycleBackward())
     */
    protected $cyclePosition = 0;

    /**
     * Constructor
     * Optionally pass in an array to start your Collection.
     *
     * @param   array       Collection items
     * @return  Collection
     */
    public function __construct(array $initial_contents = array())
    {
        $this->contents = $initial_contents;

        $this->addBaseSelectors();
    }

    /**
     * Add a new selector type into the Collection.
     * This allows you to add a new selector type on-the-fly to the collection.
     *
     * For example:
     *        $collection->addSelector('[a-z0-9]', function(collection, matches){
     *            // Do stuff here.
     *        });
     *
     *        $result = $collection['callselectorabove'];
     *
     * @param   string      $selector   Regex to match for selector to be invoked.
     * @param   callable    $callback   Function (anonymous or otherwise) to call when the selector is found.
     * @return  $this
     */
    public function addSelector($selector, $callback)
    {
        $this->selectors[$selector] = $callback;

        return $this;
    }

    /**
     * Adds the default selectors
     */
    protected function addBaseSelectors()
    {
        $this->addSelector('(?P<start>-?[0-9]+):(?P<end>[0-9]+|END)', array($this, 'spliceCallback'));
        $this->addSelector('[0-9a-zA-Z\-_]+,', array($this, 'pluckCallback'));
    }

    /**
     * Calls a selector or throws an exception if it doesn't know what it is.
     *
     * @param   string  $key    Key passed to the collection
     * @throws  Exception\Index
     * @return  mixed   Generally a new Collection instance, but theoretically anything.
     */
    protected function callSelector($key)
    {
        foreach ($this->selectors as $selector => $callback) {
            $regex = '/' . $selector . '/i';
            if (preg_match($regex, $key, $matches)) {
                return call_user_func_array($callback, array($this, $key, $matches));
            }
        }

        throw new Exception\Index('Unknown index: ' . $key);
    }

    /*
     * ------------------------ Base Selectors --------------------
     */

    /**
     * Splices a collection given by a range. You can also use START and END as
     * keywords.
     *
     * Splicing is 1 indexed!!!
     *
     *  $result = $collection['1:10']; // Get first 10 items.
     *
     * @param   Collection  $collection     Collection we're operating on.
     * @param   string      $key            The unadultarated key the user passed.
     * @param   array       $matches        regex matches array
     * @return  array
     * @throws  Exception\Bounds
     */
    public function spliceCallback(Collection $collection, $key, array $matches)
    {
        // $collection isn't used as this is an internal function.
        $start = $matches['start'];
        $end = $matches['end'];

        // You can use the END keyword to select everything up until an end point.
        if ($end == 'END') {
            $end = count($this->contents) - 1;
        }

        // If the start is negative, we count backwards from the end of the CSV. array_slice can handle negative
        // offsets, but bounds checking gets a bit gnarly.
        if ($start < 0) {
            $start = (count($this->contents)) - abs($start);
        }

        // Check the bounds:
        if ($start >= count($this->contents)) {
            throw new Exception\Bounds(
                'Start index (' . $start . ') is beyond the end of the file',
                Exception\Bounds::ERROR_START_OUT_OF_RANGE
            );
        } elseif ($start > $end) {
            throw new Exception\Bounds(
                'Start index is greater than end index: ' . $start . ' > ' . $end,
                Exception\Bounds::ERROR_START_GT_END
            );
        }

        // If the end index is > the total, we set to the total:
        if ($end >= count($this->contents)) {
            $end = count($this->contents) - 1;
        }

        // Now work out the params for array_slice
        $offset = $start;
        $length = ($end - $start) + 1;

        return array_slice($this->contents, $offset, $length);
    }

    /**
     * Implements the plucking behaviour.
     *
     * @param   Collection  $collection     Collection we're operating on.
     * @param   string      $key            The unadultarated key the user passed.
     * @param   array       $matches        regex matches array
     * @return  array
     */
    public function pluckCallback(Collection $collection, $key, array $matches)
    {
        $indexes = explode(',', $key);
        $result = array();
        foreach ($indexes as $idx) {
            $idx = trim($idx);
            if ($idx != '') {
                if (array_key_exists($idx, $this->contents)) {
                    $result[$idx] = $this->contents[$idx];
                }
            }
        }
        return $result;
    }

    /*
     * ------------------------ Countable Implementation ---------------------------
     */

    /**
     * Returns the number of elements in this collection.
     * Allows for the count() PHP function to be used.
     *
     * @return    int
     */
    public function count()
    {
        return count($this->contents);
    }


    /*
     * ----------------------- Array Access Implementation -------------------------
     */

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->contents);
    }


    /**
     * This function is the gateway to the short syntax magic.
     *
     * @param   mixed   $offset     INT for numeric index. String splice selector otherwise.
     * @throws  Exception\Index
     * @throws  Exception\Bounds
     * @return  array
     */
    public function offsetGet($offset)
    {
        if (is_int($offset)) {
            return $this->contents[$offset];
        } elseif ($offset == ':last') {
            // Shortcut for fetching the last item in the Collection:
            return $this->contents[count($this->contents) - 1];
        } else {
            return $this->callSelector($offset);
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->contents[] = $value;
        } else {
            $this->contents[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->contents[$offset]);
    }


    /*
     * ---------------------- Array Access Implementation ----------------------------
     */

    protected $iter_current_pos = 0;

    public function current()
    {
        return $this->contents[$this->iter_current_pos];
    }

    public function key()
    {
        return $this->iter_current_pos;
    }

    public function next()
    {
        $this->iter_current_pos++;
    }

    public function rewind()
    {
        $this->iter_current_pos = 0;
    }

    public function valid()
    {
        return isset($this->contents[$this->iter_current_pos]);
    }


    /**
     * Our implementation of array_keys(). Returns all the keys of the collection
     * in an array in the order that they appear.
     *
     * @return    array
     */
    public function keys()
    {
        return array_keys($this->contents);
    }

    /**
     * Our implementation of array_values(). Returns all the values in the array
     * in the order that they appear, without keys.
     *
     * @return    array
     */
    public function values()
    {
        return array_values($this->contents);
    }


    /**
     * Returns the collection unmolested into an array. If any sorting has taken place, you
     * are returned the sorted version of the array.
     *
     * @return    array
     */
    public function toArray()
    {
        return $this->contents;
    }


    /*
     * ---------------------------------- Sorting -----------------------------------
     */

    /**
     * Sorts the contents of the Collection. Uses the same sort flags as sort() and asort().
     * It
     *
     * @param   int    $direction  Sort direction (use the class constants)
     * @param   int    $flags      Sort flags (see http://php.net/sort)
     * @return  $this
     * @throws  Exception\Exception
     */
    public function sort($direction = Collection::SORT_ASC, $flags = SORT_REGULAR)
    {
        switch ($direction) {
            case self::SORT_ASC:
                sort($this->contents, $flags);
                break;

            case self::SORT_ASC_PRESERVE_KEYS:
                asort($this->contents, $flags);
                break;

            case self::SORT_DESC:
                rsort($this->contents, $flags);
                break;

            case self::SORT_DESC_PRESERVE_KEYS:
                arsort($this->contents, $flags);
                break;

            default:
                throw new Exception\Exception('Unknown sort direction: ' . $direction);
                break;
        }

        return $this;
    }

    /**
     * Sorting by a member of the collection contents. Works on keyed arrays, object members, and the
     * result of functions on an object.
     *
     * If the member cannot be found on each and every item in the collection, an Exception will be thrown.
     *
     * @param    string    $member      Key name, member name or function name to sort by.
     * @param    int       $direction   Sort direction (use class constants) default ASC.
     * @param    int       $flags       Sort flags (see http://php.net/sort)
     * @return    $this
     * @throws    Exception\Index
     * @throws    Exception\Exception
     */
    public function sortByMember(
        $member,
        $direction = Collection::SORT_ASC,
        $flags = SORT_REGULAR
    ) {
        // Build up an array to sort using asort or arsort.
        $arr_to_sort = array();
        foreach ($this->contents as $key => $item) {
            if (is_array($item)) {
                if (!array_key_exists($member, $item)) {
                    throw new Exception\Index('Unknown Array Index "' . $member . '" in item with key: "' . $key . '"');
                }

                $arr_to_sort[$key] = $item[$member];
            } elseif (is_object($item)) {
                if (isset($item->$member) && !($item->$member instanceof \Closure)) {
                    $arr_to_sort[$key] = $item->$member;
                } elseif (method_exists($item, $member)) {
                    $arr_to_sort[$key] = $item->$member();
                } elseif (property_exists($item, $member) && $item->$member instanceof \Closure) {
                    $func = $item->$member;
                    $arr_to_sort[$key] = $func();
                } else {
                    throw new Exception\Index(
                        'Unknown Object Member/Method "' . $member . '" in item with key: "' . $key . '"'
                    );
                }
            }
        }

        // Perform the sort:
        switch ($direction) {
            case self::SORT_ASC:
            case self::SORT_ASC_PRESERVE_KEYS:
                asort($arr_to_sort, $flags);
                break;

            case self::SORT_DESC:
            case self::SORT_DESC_PRESERVE_KEYS:
                arsort($arr_to_sort, $flags);
                break;

            default:
                throw new Exception\Exception('Unknown sort direction: ' . $direction);
                break;
        }

        // Rebuild the collection in the new order:
        $new_contents = array();
        foreach ($arr_to_sort as $key => $not_used_sorted_value) {
            switch ($direction) {
                case self::SORT_ASC_PRESERVE_KEYS:
                case self::SORT_DESC_PRESERVE_KEYS:
                    $new_contents[$key] = $this->contents[$key];
                    break;

                default:
                    $new_contents[] = $this->contents[$key];
                    break;
            }
        }

        $this->contents = $new_contents;

        return $this;
    }

    /*
     * ------------------ Providing Cycling Functionality ------------------
     */

    /**
     * Sets the cycle position. NOTE: this is 0-indexed. If you set OVER the bounds of the
     * collection, it'll set to the last item. Set under the bounds (ie a negative number)
     * and it'll set to 0.
     *
     * @param   int     $position   Cycle position
     * @return  $this
     */
    public function setCyclePosition($position)
    {
        if ($position >= $this->count()) {
            $this->cyclePosition = $this->count() - 1;
        } elseif ($position < 0) {
            $this->cyclePosition = 0;
        } else {
            $this->cyclePosition = (int)$position;
        }
        return $this;
    }

    /**
     * Gets the current cycle index. This doesn't count loops round or anything,
     * just the index in the contents of the collection.
     *
     * @return  int
     */
    public function cyclePosition()
    {
        return $this->cyclePosition;
    }

    /**
     * "Cycles" the collection forward. What this means is that the Collection
     * will move forward a given number of positions. If it reaches the end
     * of the collection it will wrap back to the beginning.
     *
     * NOTE: This *does not* affect the main array pointer OR iterators OR arrayaccess.
     *
     * @param   int     $advance    Number of positions forward to advance
     * @return  mixed   The value of the Collection at this point.
     */
    public function cycleForward($advance = 1)
    {
        for ($i = 0; $i < $advance; $i ++) {
            if ($this->cyclePosition == $this->count()-1) {
                $this->cyclePosition = 0;
            } else {
                $this->cyclePosition ++;
            }
        }
        return $this->cycleValue();
    }

    /**
     * Cycles the collection backward by the $retreat amount. If the collection
     * hits the beginning, it wraps around to the end.
     *
     * NOTE: This *does not* affect the main array pointer OR iterators OR arrayaccess.
     *
     * @param   int     $retreat    Number of positions to go backwards
     * @return  mixed   The value of the Collection at this point.
     */
    public function cycleBackward($retreat = 1)
    {
        for ($i = 0; $i < $retreat; $i ++) {
            if ($this->cyclePosition == 0) {
                $this->cyclePosition = $this->count() - 1;
            } else {
                $this->cyclePosition --;
            }
        }
        return $this->cycleValue();
    }

    /**
     * Gets the value of the collection at the current cycle position.
     *
     * @return  mixed
     */
    public function cycleValue()
    {
        return $this[$this->cyclePosition];
    }

    /*
     * --------------- Searching Functionality ----------------
     */

    /**
     * Searching is dead simple. If you don't provide a callback, then the search
     * will go through and check $term === $item. If you need something more powerful,
     * you can provide a function to filter by:
     *
     *  function($term, $item) {
     *      // Returning TRUE means that yes this matches the search. FALSE means no.
     *  }
     *
     * The original keys from the collection are preserved! So if you match items in
     * index 0 and 3, your array will be array(0 => value, 3 => value)
     *
     * @param   mixed           $term       Search term
     * @param   null|callable   $callback   Search function to use as a filter.
     * @return  array
     * @throws  Exception\Search    If the callback is invalid.
     */
    public function search($term, $callback = null)
    {
        if ($callback !== null && !is_callable($callback)) {
            throw new Exception\Search('Bad callback passed to search', Exception\Search::BAD_CALLBACK);
        }

        $result = array();
        foreach ($this->contents as $key => $value) {
            if ($callback === null) {
                $include = ($value === $term);
            } else {
                $include = call_user_func_array($callback, array($term, $value));

                // Check that the callback came back with a boolean:
                if (!is_bool($include)) {
                    throw new Exception\Search(
                        'Bad return value from callback: '.$include,
                        Exception\Search::BAD_CALLBACK_RETURN
                    );
                }
            }

            if ($include) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
