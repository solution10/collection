<?php

namespace Solution10\Collection\Exception;

/**
 * Thrown when searching has gone awry
 *
 * @package 	Solution10\Collection
 * @category  	Exceptions
 * @author 		Alex Gisby <alex@solution10.com>
 */
class Search extends Exception
{
    const BAD_CALLBACK = 0;
    const BAD_CALLBACK_RETURN = 1;
}
