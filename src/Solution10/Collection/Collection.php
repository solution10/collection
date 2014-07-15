<?php

namespace Solution10\Collection;

/**
 * Solution10: Collection
 *
 * The Collection class is a general purpose class that holds a collection or set of data.
 * Think of it as an array on steroids. Useful features of Collections are quick slicing and
 * selecting portions of the data set and basic querying of the dataset.
 *
 * @package 	Solution10
 * @category 	Collection
 * @author 		Alex Gisby <alex@solution10.com>
 * @license 	MIT
 */
class Collection implements \Countable, \ArrayAccess, \Iterator
{
	const SORT_ASC = 1;
	const SORT_ASC_PRESERVE_KEYS = 2;
	const SORT_DESC = 3;
	const SORT_DESC_PRESERVE_KEYS = 4;

	/**
	 * @var 	array 	Data container
	 */
	protected $contents = array();
	
	/**
	 * @var 	array 	Selectors
	 */
	protected $selectors = array();
	
	/**
	 * Constructor
	 * Optionally pass in an array to start your Collection.
	 *
	 * @param 	array 	Collection items
	 * @return 	Collection
	 */
	public function __construct(array $initial_contents = array())
	{
		$this->contents = $initial_contents;
		
		$this->add_base_selectors();
	}
	
	/**
	 * Add a new selector type into the Collection.
	 * This allows you to add a new selector type on-the-fly to the collection.
	 *
	 * For example:
	 *		$collection->add_selector('[a-z0-9]', function(collection, matches){
	 *			// Do stuff here.
	 *		});
	 *
	 * 		$result = $collection['callselectorabove'];
	 *
	 * @param 	string 		Regex to match for selector to be invoked.
	 * @param 	callback 	Function (anonymous or otherwise) to call when the selector is found.
	 * @return 	this
	 */
	public function add_selector($selector, $callback)
	{
		$this->selectors[$selector] = $callback;
		return $this;
	}
	
	/**
	 * Adds the default selectors
	 */
	protected function add_base_selectors()
	{
		$this->add_selector('(?P<start>-?[0-9]+):(?P<end>[0-9]+|END)', array($this, 'splice'));
	}
	
	
	/**
	 * Calls a selector or throws an exception if it doesn't know what it is.
	 *
	 * @param 	string 		Key passed to the collection
	 * @throws	Exception\Index
	 * @return 	mixed 		Generally a new Collection instance, but theoretically anything.
	 */
	protected function call_selector($key)
	{
		foreach($this->selectors as $selector => $callback)
		{
			$regex = '/' . $selector . '/i';
			if(preg_match($regex, $key, $matches))
			{
				return call_user_func_array($callback, array($this, $matches));
			}
		}
		
		throw new Exception\Index('Unknown index: ' . $key);
	}
	
	/**
	 * ------------------------ Countable Implementation ---------------------------
	 */
	
	/**
	 * Returns the number of elements in this collection.
	 * Allows for the count() PHP function to be used.
	 *
	 * @return 	int
	 */
	public function count()
	{
		return count($this->contents);
	}
	
	
	/**
	 * ----------------------- Array Access Implementation -------------------------
	 */
	
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->contents);
	}
	
	
	/**
	 * This function contains a lot of the magic with splicing and such.
	 *
	 * @param 	mixed 	INT for numeric index. String splice selector otherwise.
	 * @return 	array
	 */
	public function offsetGet($offset)
	{
		if(is_int($offset))
		{
			return $this->contents[$offset];
		}
		elseif($offset == ':last')
		{
			// Shortcut for fetching the last item in the CSV:
			return $this->contents[count($this->contents) - 1];
		}
		elseif(preg_match('/(?P<start>-?[0-9]+):(?P<end>[0-9]+|END)/', $offset, $matches))
		{
			$start 	= $matches['start'];
			$end	= $matches['end'];
			
			// You can use the END keyword to select everything up until an end point.
			if($end == 'END')
				$end = count($this->contents) - 1;
			
			// If the start is negative, we count backwards from the end of the CSV. array_slice can handle negative
			// offsets, but bounds checking gets a bit gnarly.
			if($start < 0)
				$start = (count($this->contents)) - abs($start);
			
			// Check the bounds:
			if($start >= count($this->contents))
			{
				throw new Exception\Bounds('Start index (' . $start . ') is beyond the end of the file', Exception\Bounds::ERROR_START_OUT_OF_RANGE);
			}
			elseif($start > $end)
			{
				throw new Exception\Bounds('Start index is greater than end index: ' . $start . ' > ' . $end, Exception\Bounds::ERROR_START_GT_END);
			}
			
			// If the end index is > the total, we set to the total:
			if($end >= count($this->contents))
			{
				$end = count($this->contents) - 1;
			}
			
			// Now work out the params for array_slice
			$offset = $start;
			$length	= ($end - $start) + 1;
			
			return array_slice($this->contents, $offset, $length);
		}
		else
		{
			return $this->call_selector($offset);
		}
		
		// We've got an index we don't know what to do with. Throw an exception:
		throw new Exception\Index('Unknown index: ' . $offset);
	}
	
	public function offsetSet($offset, $value)
	{
		if(is_null($offset))
		{
			$this->contents[] = $value;
		}
		else
		{
			$this->contents[$offset] = $value;
		}
	}
	
	public function offsetUnset($offset)
	{
		unset($this->contents[$offset]);
	}
	
	
	/**
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
		$this->iter_current_pos ++;
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
	 * @return 	array
	 */
	public function keys()
	{
		return array_keys($this->contents);
	}
	
	/**
	 * Our implementation of array_values(). Returns all the values in the array
	 * in the order that they appear, without keys.
	 *
	 * @return 	array
	 */
	public function values()
	{
		return array_values($this->contents);
	}
	
	
	/**
	 * Returns the collection unmolested into an array. If any sorting has taken place, you
	 * are returned the sorted version of the array.
	 *
	 * @return 	array
	 */
	public function to_array()
	{
		return $this->contents;
	}
	
	
	/**
	 * ---------------------------------- Sorting -----------------------------------
	 */
	
	/**
	 * Sorts the contents of the Collection. Uses the same sort flags as sort() and asort().
	 * It
	 *
	 * @param 	int 	Sort direction (use the class constants)
	 * @param 	int 	Sort flags (see http://php.net/sort)
	 * @return 	this
	 */
	public function sort($direction = \Solution10\Collection\Collection::SORT_ASC, $flags = SORT_REGULAR)
	{
		switch($direction)
		{
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
	 * @param 	string 	Key name, member name or function name to sort by.
	 * @param 	int 	Sort direction (use class constants) default ASC.
	 * @param 	int 	Sort flags (see http://php.net/sort)
	 * @return 	this
	 * @throws 	Solution10\Collection\Exception
	 */
	public function sort_by_member($member, $direction = \Solution10\Collection\Collection::SORT_ASC, $flags = SORT_REGULAR)
	{
		// Build up an array to sort using asort or arsort.
		$arr_to_sort = array();
		foreach($this->contents as $key => $item)
		{
			if(is_array($item))
			{
				if(!array_key_exists($member, $item))
					throw new Exception\Index('Unknown Array Index "' . $member . '" in item with key: "' . $key . '"');
				
				$arr_to_sort[$key] = $item[$member];
			}
			elseif(is_object($item))
			{
				if(isset($item->$member) && !($item->$member instanceof \Closure))
				{
					$arr_to_sort[$key] = $item->$member;
				}
				elseif(method_exists($item, $member))
				{
					$arr_to_sort[$key] = $item->$member();
				}
				elseif(property_exists($item, $member) && $item->$member instanceof \Closure)
				{
					$func = $item->$member;
					$arr_to_sort[$key] = $func();
				}
				else
				{
					throw new Exception\Index('Unknown Object Member/Method "' . $member . '" in item with key: "' . $key . '"');
				}
			}
		}
		
		// Perform the sort:
		switch($direction)
		{
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
		foreach($arr_to_sort as $key => $not_used_sorted_value)
		{
			switch($direction)
			{
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
}