<?php

namespace Solution10\Collection\Exception;

/**
 * Bounds exception. Thrown when splicing arrays.
 *
 * @package 	Solution10\Collection
 * @category  	Exceptions
 * @author 		Alex Gisby <alex@solution10.com>
 */
class Bounds extends Exception
{
	/**
	 * @var 	int 	Start bound is out of range
	 */
	const ERROR_START_OUT_OF_RANGE = 1;
	
	/**
	 * @var 	int 	Start bound greater than end
	 */
	const ERROR_START_GT_END = 2;
	
	/**
	 * Constructor
	 *
	 * @param 	string 	Message
	 * @param 	int 	Type
	 */
	public function __construct($message, $type)
	{
		return parent::__construct($message, $type);
	}
}