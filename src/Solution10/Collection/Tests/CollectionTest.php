<?php

namespace Solution10\Collection\Tests;

use PHPUnit_Framework_TestCase;
use Solution10\Collection\Collection;
use Solution10\Collection\Tests\Stubs\Person;
use \stdClass;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var     Collection
     */
    public $collection;

    /**
     * Sets up a basic Collection to use in multiple tests
     */
    public function setUp()
    {
        $this->collection = new Collection(array(
            'Item1', 'Item2', 'Item3',
        ));
    }

    /**
     * Tests the constructor of the Collection.
     */
    public function testConstruct()
    {
        $collection = new Collection(array(
            'Item1', 'Item2', 'Item3',
        ));

        $this->assertEquals('Solution10\\Collection\\Collection', get_class($collection));
    }

    /**
     * Tests constructor with no params
     */
    public function testEmptyContructor()
    {
        $collection = new Collection();
        $this->assertEquals('Solution10\\Collection\\Collection', get_class($collection));
    }

    /**
     * Test the member counting function
     */
    public function testMemberCount()
    {
        $this->assertEquals(3, $this->collection->count());
    }

    /**
     * Tests the count interface
     */
    public function testCount()
    {
        $this->assertEquals(3, count($this->collection));
    }

    /*
     * -------------- Selector Tests ----------------
     */

    /**
     * Tests adding an anonymous function as a callback
     */
    public function testAnonFuncSelector()
    {
        $this->collection->addSelector('::test::', function ($collection, $key, $matches) {
            return true;
        });

        $this->assertTrue($this->collection['::test::']);
    }

    /**
     * Tests selecting by an index
     */
    public function testArrayAccessIndex()
    {
        $item 	= $this->collection[0];

        // Check it's an array:
        $this->assertEquals('Item1', $item);
    }

    /**
     * Tests ArrayAccess::offsetExists
     */
    public function testArrayAccessIsset()
    {
        $this->assertEquals(true, isset($this->collection[0]));
    }

    /**
     * Tests inserting records into the array
     */
    public function testArrayAccessSetNull()
    {
        $this->collection[]	= 'NullItem';

        $this->assertEquals(4, count($this->collection));
        $this->assertEquals('NullItem', $this->collection[3]);

        // Re-read the data to restore it:
        $this->setUp();
    }

    /**
     * Tests inserting records into the array
     */
    public function testArrayAccessSetIndex()
    {
        $this->collection[3]	= 'Item4';

        // Check there's five elements:
        $this->assertEquals(4, count($this->collection));

        // Check the last one has a name of Jim
        $this->assertEquals('Item4', $this->collection[3]);

        // Re-read the data to restore it:
        $this->setUp();
    }

    /**
     * Tests unsetting records from the rows
     */
    public function testArrayAccessUnset()
    {
        unset($this->collection[2]);

        $this->assertEquals(2, count($this->collection));

        // Re-read the data to restore it:
        $this->setUp();
    }

    /**
     * Tests Iterator.
     */
    public function testIterator()
    {
        $iterations = 0;

        foreach ($this->collection as $key => $item) {
            $this->assertEquals('Item' . ($key + 1), $item);
            $iterations ++;
        }

        $this->assertEquals(count($this->collection), $iterations);
    }


    /*
     * -------------------- Splicing tests, of which there are many -----------------
     */

    /**
     * Tests out of bounds
     *
     * @expectedException 		\Solution10\Collection\Exception\Bounds
     * @expectedExceptionCode 	1
     */
    public function testOOBStart()
    {
        $this->collection['10:20'];
    }

    /**
     * Tests start > end
     *
     * @expectedException 		\Solution10\Collection\Exception\Bounds
     * @expectedExceptionCode 	2
     */
    public function testOOBStartGTEnd()
    {
        $this->collection['2:0'];
    }

    /**
     * Tests a bad index
     *
     * @expectedException 		\Solution10\Collection\Exception\Index
     */
    public function testBadIndex()
    {
        $this->collection['thisisabadindex'];
    }

    /**
     * Testing basic splicing
     */
    public function testBasicSplicing()
    {
        $splice = $this->collection['1:2'];

        $this->assertEquals(2, count($splice));
        $this->assertEquals('Item2', $splice[0]);
        $this->assertEquals('Item3', $splice[1]);
    }

    /**
     * Tests the 'over-the-end' splice, where the end is greater than the length
     */
    public function testOverTheEndSplicing()
    {
        $splice = $this->collection['1:10'];

        $this->assertEquals(2, count($splice));
        $this->assertEquals('Item2', $splice[0]);
        $this->assertEquals('Item3', $splice[1]);
    }

    /**
     * Tests negative splicing
     */
    public function testNegativeSplicing()
    {
        $splice = $this->collection['-1:3'];
        $this->assertEquals(1, count($splice));
        $this->assertEquals('Item3', $splice[0]);
    }


    /**
     * Tests the END keyword
     */
    public function testEndSplicing()
    {
        $splice = $this->collection['1:END'];
        $this->assertEquals(2, count($splice));
        $this->assertEquals('Item2', $splice[0]);
        $this->assertEquals('Item3', $splice[1]);
    }

    /**
     * Test the :LAST selector
     */
    public function testLastSelector()
    {
        $splice = $this->collection[':last'];
        $this->assertEquals('Item3', $splice);
    }

    /*
     * ----------------- Keys and Values Tests ------------------------
     */

    /**
     * Testing keys()
     */
    public function testKeys()
    {
        $collection = new Collection(array(
            'name' => 'Alex',
            'job' => 'Web Dev',
            'fave_food' => 'Chinese',
        ));

        $keys = $collection->keys();
        $this->assertEquals('name', $keys[0]);
        $this->assertEquals('job', $keys[1]);
        $this->assertEquals('fave_food', $keys[2]);
    }

    /**
     * Testing values()
     */
    public function testValues()
    {
        $collection = new Collection(array(
            'name' => 'Alex',
            'job' => 'Web Dev',
            'fave_food' => 'Chinese',
        ));

        $values = $collection->values();
        $this->assertEquals('Alex', $values[0]);
        $this->assertEquals('Web Dev', $values[1]);
        $this->assertEquals('Chinese', $values[2]);
    }

    /**
     * Testing toArray()
     */
    public function testToArray()
    {
        $collection = new Collection(array(
            'Apple', 'Orange', 'Banana',
        ));

        $arr = $collection->toArray();

        $this->assertEquals('Apple', $arr[0]);
        $this->assertEquals('Orange', $arr[1]);
        $this->assertEquals('Banana', $arr[2]);
    }


    /*
     * ----------------- Sorting Tests ------------------------
     */

    /**
     * Testing basic ascending sorting
     */
    public function testSort()
    {
        $collection = new Collection(array(
            'Apple', 'Orange', 'Banana', 'Cucumber',
        ));

        $collection->sort(Collection::SORT_ASC);
        $this->assertEquals('Apple', $collection[0]);
        $this->assertEquals('Banana', $collection[1]);
        $this->assertEquals('Cucumber', $collection[2]);
        $this->assertEquals('Orange', $collection[3]);
    }

    /**
     * Testing basic descending sorting
     */
    public function testRSort()
    {
        $collection = new Collection(array(
            'Apple', 'Orange', 'Banana', 'Cucumber',
        ));

        $collection->sort(Collection::SORT_DESC);
        $this->assertEquals('Apple', $collection[3]);
        $this->assertEquals('Banana', $collection[2]);
        $this->assertEquals('Cucumber', $collection[1]);
        $this->assertEquals('Orange', $collection[0]);
    }

    /**
     * Testing the preserved key asc sorting
     */
    public function testASort()
    {
        $collection = new Collection(array(
            'name' => 'Alex',
            'job' => 'Web Dev',
            'fave_food' => 'Chinese',
        ));

        $collection->sort(Collection::SORT_ASC_PRESERVE_KEYS);

        $keys = $collection->keys();
        $this->assertEquals('name', $keys[0]);
        $this->assertEquals('job', $keys[2]);

        $values = $collection->values();
        $this->assertEquals('Alex', $values[0]);
        $this->assertEquals('Web Dev', $values[2]);
    }

    /**
     * Testing the preserved key desc sorting
     */
    public function testARSort()
    {
        $collection = new Collection(array(
            'name' => 'Alex',
            'job' => 'Web Dev',
            'fave_food' => 'Chinese',
        ));

        $collection->sort(Collection::SORT_DESC_PRESERVE_KEYS);

        $keys = $collection->keys();
        $this->assertEquals('name', $keys[2]);
        $this->assertEquals('job', $keys[0]);

        $values = $collection->values();
        $this->assertEquals('Alex', $values[2]);
        $this->assertEquals('Web Dev', $values[0]);
    }


    /**
     * Sorting by an array member asc test
     */
    public function testArrayMemberAscSort()
    {
        $collection = new Collection(array(
            array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('name', Collection::SORT_ASC);
        $this->assertEquals('Alex', $collection[0]['name']);
        $this->assertEquals('Developer', $collection[0]['job']);
        $this->assertEquals('Kelly', $collection[2]['name']);
        $this->assertEquals('Manager', $collection[2]['job']);
    }

    /**
     * Sorting by an array member desc test
     */
    public function testArrayMemberDescSort()
    {
        $collection = new Collection(array(
            array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('name', Collection::SORT_DESC);
        $this->assertEquals('Alex', $collection[2]['name']);
        $this->assertEquals('Developer', $collection[2]['job']);
        $this->assertEquals('Kelly', $collection[0]['name']);
        $this->assertEquals('Manager', $collection[0]['job']);
    }


    /**
     * Sorting by an object member asc test
     */
    public function testObjMemberAscSort()
    {
        $obj1 = new stdClass();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';

        $obj2 = new stdClass();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';

        $obj3 = new stdClass();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('name', Collection::SORT_ASC);
        $this->assertEquals('Alex', $collection[0]->name);
        $this->assertEquals('Developer', $collection[0]->job);
        $this->assertEquals('Kelly', $collection[2]->name);
        $this->assertEquals('Manager', $collection[2]->job);
    }

    /**
     * Sorting by an object member desc test
     */
    public function testObjMemberDescSort()
    {
        $obj1 = new stdClass();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';

        $obj2 = new stdClass();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';

        $obj3 = new stdClass();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('name', Collection::SORT_DESC);
        $this->assertEquals('Alex', $collection[2]->name);
        $this->assertEquals('Developer', $collection[2]->job);
        $this->assertEquals('Kelly', $collection[0]->name);
        $this->assertEquals('Manager', $collection[0]->job);
    }

    /**
     * Sorting by an object closure asc test
     */
    public function testObjClosureAscSort()
    {
        $obj1 = new stdClass();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';
        $obj1->sort = function () {
            return 'Orange';
        };

        $obj2 = new stdClass();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';
        $obj2->sort = function () {
            return 'Apple';
        };

        $obj3 = new stdClass();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';
        $obj3->sort = function () {
            return 'Banana';
        };

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('sort', Collection::SORT_ASC);
        $this->assertEquals('Alex', $collection[0]->name);
        $this->assertEquals('Developer', $collection[0]->job);
        $this->assertEquals('Kelly', $collection[2]->name);
        $this->assertEquals('Manager', $collection[2]->job);
    }

    /**
     * Sorting by an object closure desc test
     */
    public function testObjClosureDescSort()
    {
        $obj1 = new stdClass();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';
        $obj1->sort = function () {
            return 'Orange';
        };

        $obj2 = new stdClass();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';
        $obj2->sort = function () {
            return 'Apple';
        };

        $obj3 = new stdClass();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';
        $obj3->sort = function () {
            return 'Banana';
        };

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('sort', Collection::SORT_DESC);
        $this->assertEquals('Alex', $collection[2]->name);
        $this->assertEquals('Developer', $collection[2]->job);
        $this->assertEquals('Kelly', $collection[0]->name);
        $this->assertEquals('Manager', $collection[0]->job);
    }

    /**
     * Sorting by an object method asc test
     */
    public function testObjMethodAscSort()
    {
        $obj1 = new Person();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';

        $obj2 = new Person();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';

        $obj3 = new Person();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('sort', Collection::SORT_ASC);
        $this->assertEquals('Alex', $collection[0]->name);
        $this->assertEquals('Developer', $collection[0]->job);
        $this->assertEquals('Kelly', $collection[2]->name);
        $this->assertEquals('Manager', $collection[2]->job);
    }

    /**
     * Sorting by an object method desc test
     */
    public function testObjMethodDescSort()
    {
        $obj1 = new Person();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';

        $obj2 = new Person();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';

        $obj3 = new Person();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('sort', Collection::SORT_DESC);
        $this->assertEquals('Alex', $collection[2]->name);
        $this->assertEquals('Developer', $collection[2]->job);
        $this->assertEquals('Kelly', $collection[0]->name);
        $this->assertEquals('Manager', $collection[0]->job);
    }

    /**
     * Testing array member preserved keys sorting asc
     */
    public function testArrayMemberAscSortPreserveKeys()
    {
        $collection = new Collection(array(
            'apple' => array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            'orange' => array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            'banana' => array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('name', Collection::SORT_ASC_PRESERVE_KEYS);
        $keys = $collection->keys();
        $this->assertEquals('orange', $keys[0]);
        $this->assertEquals('apple', $keys[2]);
    }

    /**
     * Testing array member preserved keys sorting desc
     */
    public function testArrayMemberDescSortPreserveKeys()
    {
        $collection = new Collection(array(
            'apple' => array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            'orange' => array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            'banana' => array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('name', Collection::SORT_DESC_PRESERVE_KEYS);
        $keys = $collection->keys();
        $this->assertEquals('orange', $keys[2]);
        $this->assertEquals('apple', $keys[0]);
    }

    /**
     * Testing unknown sort exceptions
     *
     * @expectedException \Solution10\Collection\Exception\Exception
     */
    public function testBadSortDirection()
    {
        $collection = new Collection(array(
            'Apple', 'Orange', 'Banana',
        ));

        $collection->sort(999);
    }

    /**
     * Testing unknown sortByMember exceptions
     *
     * @expectedException \Solution10\Collection\Exception\Exception
     */
    public function testBadMemberSortDirection()
    {
        $collection = new Collection(array(
            'apple' => array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            'orange' => array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            'banana' => array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('name', 999);
    }

    /**
     * Testing unknown array member exceptions
     *
     * @expectedException \Solution10\Collection\Exception\Index
     */
    public function testBadArrayMemberSort()
    {
        $collection = new Collection(array(
            'apple' => array(
                'name' => 'Kelly',
                'job' => 'Manager',
            ),
            'orange' => array(
                'name' => 'Alex',
                'job' => 'Developer',
            ),
            'banana' => array(
                'name' => 'Jimmy',
                'job' => 'Tester',
            ),
        ));

        $collection->sortByMember('unknown');
    }

    /**
     * Testing unknown object property sorting
     *
     * @expectedException \Solution10\Collection\Exception\Index
     */
    public function testBadObjectMemberSort()
    {
        $obj1 = new Person();
        $obj1->name = 'Kelly';
        $obj1->job = 'Manager';

        $obj2 = new Person();
        $obj2->name = 'Alex';
        $obj2->job = 'Developer';

        $obj3 = new Person();
        $obj3->name = 'Jimmy';
        $obj3->job = 'Tester';

        $collection = new Collection(array(
            $obj1, $obj2, $obj3,
        ));

        $collection->sortByMember('unknown');
    }

    /*
     * ------------- Testing Cycling Behaviour --------------
     */

    public function testSetGetCyclePosition()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals(0, $c->cyclePosition());
        $c->setCyclePosition(3);
        $this->assertEquals(3, $c->cyclePosition());
    }

    public function testSetGetCyclePositionUnderBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals(0, $c->cyclePosition());
        $c->setCyclePosition(-4);
        $this->assertEquals(0, $c->cyclePosition());
    }

    public function testSetGetCyclePositionOverBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals(0, $c->cyclePosition());
        $c->setCyclePosition(8);
        $this->assertEquals(6, $c->cyclePosition());
    }

    public function testCycleForwardWithinBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals('Tues', $c->cycleForward(1));
        $this->assertEquals('Sat', $c->cycleForward(4));
    }

    public function testCycleForwardOverBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals('Mon', $c->cycleForward(7));
        $this->assertEquals('Thurs', $c->cycleForward(10));
    }

    public function testCycleBackwardWithinBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $c->setCyclePosition(5);
        $this->assertEquals('Fri', $c->cycleBackward(1));
        $this->assertEquals('Mon', $c->cycleBackward(4));
    }

    public function testCycleBackwardOverBounds()
    {
        $c = new Collection(array('Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat', 'Sun'));
        $this->assertEquals('Mon', $c->cycleBackward(7));
        $this->assertEquals('Fri', $c->cycleBackward(10));
    }

    /*
     * ----------------- Testing Searching Functionality -----------------
     */

    public function testSimpleSearch()
    {
        $c = new Collection(array('England', 'Scotland', 'Ireland', 'Wales'));
        $this->assertEquals(array(1 => 'Scotland'), $c->search('Scotland'));
    }

    public function testSimpleSearchNoResults()
    {
        $c = new Collection(array('England', 'Scotland', 'Ireland', 'Wales'));
        $this->assertEquals(array(), $c->search('France'));
    }

    public function testSearchCallback()
    {
        $c = new Collection(array('England', 'Scotland', 'Ireland', 'Wales'));

        // This callback returns anything that *isn't* the term.
        $result = $c->search('Scotland', function ($term, $item) {
            return $term != $item;
        });

        $this->assertEquals(array(0 => 'England', 2 => 'Ireland', 3 => 'Wales'), $result);
    }

    public function testSearchCallbackNoResults()
    {
        $c = new Collection(array(10, 20, 30, 40, 50));

        // Search for any value that's less than 10
        $result = $c->search(10, function ($term, $item) {
            return $item < $term;
        });

        $this->assertEquals(array(), $result);
    }

    /**
     * @expectedException       \Solution10\Collection\Exception\Search
     * @expectedExceptionCode   \Solution10\Collection\Exception\Search::BAD_CALLBACK
     */
    public function testSearchBadCallback()
    {
        $c = new Collection();
        $c->search('monkey', 12);
    }

    /**
     * @expectedException       \Solution10\Collection\Exception\Search
     * @expectedExceptionCode   \Solution10\Collection\Exception\Search::BAD_CALLBACK_RETURN
     */
    public function testSearchBadCallbackReturn()
    {
        $c = new Collection(array('apple', 'orange', 'banana'));
        $c->search('monkey', function ($term, $item) {
            // No return type!
        });
    }

    /*
     * -------------------- Testing Plucking ----------------
     */

    public function testPlucking()
    {
        $collection = new Collection(array('Alex', 'Bob', 'Charlotte', 'Diana', 'Ellie', 'Frank'));
        $plucked = $collection['0,2,4'];
        $this->assertEquals(array(0 => 'Alex', 2 => 'Charlotte', 4 => 'Ellie'), $plucked);
    }

    public function testPluckingNotFound()
    {
        $collection = new Collection(array('Alex', 'Bob', 'Charlotte', 'Diana', 'Ellie', 'Frank'));
        $plucked = $collection['0,12'];
        $this->assertEquals(array(0 => 'Alex'), $plucked);
    }

    public function testPluckingTrailingComma()
    {
        $collection = new Collection(array('Alex', 'Bob', 'Charlotte', 'Diana', 'Ellie', 'Frank'));
        $plucked = $collection['0,2,4,'];
        $this->assertEquals(array(0 => 'Alex', 2 => 'Charlotte', 4 => 'Ellie'), $plucked);
    }

    public function testPluckingLeadingComma()
    {
        $collection = new Collection(array('Alex', 'Bob', 'Charlotte', 'Diana', 'Ellie', 'Frank'));
        $plucked = $collection['0,2,4,'];
        $this->assertEquals(array(0 => 'Alex', 2 => 'Charlotte', 4 => 'Ellie'), $plucked);
    }

    public function testPluckStringKeys()
    {
        $collection = new Collection(array('dev' => 'Alex', 'manager' => 'Sarah', 'design' => 'Ellie'));
        $plucked = $collection['dev,design'];
        $this->assertEquals(array('dev' => 'Alex', 'design' => 'Ellie'), $plucked);
    }

    public function testPluckMixedKeys()
    {
        $source = array('d3v' => 'Alex', 'mNgMt' => 'Sarah', 'design_lead-awesome' => 'Ellie', 27 => 'Phil');
        $collection = new Collection($source);
        $plucked = $collection['d3v,mNgMt,design_lead-awesome,27'];
        $this->assertEquals($source, $plucked);
    }
}
