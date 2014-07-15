<?php

namespace Solution10\Collection\Tests\Stubs;

/**
 * This class is used for some of the sorting tests:
 */
class Person
{
    public $name;
    public $job;
    public function sort()
    {
        return $this->name;
    }
}
