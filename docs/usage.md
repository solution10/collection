# Solution10\Collection Usage

Like Arrays. Only better!

## What is a Collection?

Collections, at their core, are implementations of Iterator, ArrayAccess and Countable. But they're also a lot more than that.
Collections allow you to splice your array and return a subset, just by passing a special key.

But more on that later, let's start with the basics

## Creating and Populating

Creating an instance of Collection is simple:

    // Create a new, empty collection:
    $collection = new Solution10\Collection\Collection();

    // Create a collection from an array:
    $collection = new Solution10\Collection\Collection(array('Hello', 'World'));
    To add items into the collection, you simply use the same syntax you would an array:

    $collection = new Solution10\Collection\Collection();
    $collection[] = 'Hello';
    $collection[] = 'Beautiful';
    $collection[20] = 'World';

    /*
        Will result in a collection that looks like:
        (
            0 => 'Hello',
            1 => 'Beautiful',
            20 => 'World'
        )
    */

To keep the docs brief, from now on we'll assume your using the Solution10\Collection namespace in the file like so:

    use \Solution10\Collection;


You can overwrite elements in the array just like you would a normal array:

    $collection = new Collection();
    $collection[] = 'Hello';
    $collection[] = 'World';

    // Change the first item to Goodbye:
    $collection[0] = 'Goodbye';
    And finally, unset() works exactly how you would imagine, removing an item from the collection.

    $collection = new Collection();
    $collection[] = 'Hello';
    $collection[] = 'World';
    unset($collection[1]);

    // Collection now just contains 'Hello'

## Looping and Counting

Collections act like arrays in a most circumstances, and looping and counting is one of them.

To loop (or iterate) over a collection, just use a foreach:

    $collection = new Collection();
    $collection['name'] = 'Alex';
    $collection['role'] = 'Admin';

    foreach($collection as $key => $value)
    {
        echo $key . ': ' . $value . PHP_EOL;
    }

    // The above will output:
    // name: Alex
    // role: Admin

To count how many items are in a collection, we can use count():

    $collection = new Collection(array('Apple', 'Orange', 'Banana', 'Grapes'));
    echo count($collection); // Outputs 4

## Splicing

Splicing allows you to quickly retrieve a subset of the collection based on its numeric indexes. If it's numerically indexed that is.

### Basic Splicing

Splicing involves accessing a key in the Collection of the format start:end where start and end are numeric indexes in the collection.

**Splicing is 1-indexed!** - Don't forget this!

So asking for 1:1 will grab the first element. 1:2 will grab the first two elements. etc

    $collection = new Collection(array('Apple', 'Orange', 'Banana'));
    $subset = $collection['1:2'];

    // $subset is now: array('Apple', 'Orange')

Collections allow for 'over-the-end' splicing, where if the end index is greater than the length, it'll just take everything
up to the end:

    $collection = new Collection(array('Apple', 'Orange', 'Banana'));
    $subset = $collection['1:100'];

    // $subset contains array('Orange', 'Banana')

You can use negative splicing too to grab elements from the end of the Collection. The Collection will count backwards the
number of negative steps and start counting from there.

    $collection = new Collection(array('Apple', 'Orange', 'Banana'));
    $subset = $collection['-2:2'];

    // $subset contains ('Orange', 'Banana')

Note that the end index is still counted from the start of the array! You should instead use the END keyword outlined below to
grab say the last three elements of the collection.

### Splicing Keywords

Sometimes you won't know how many items are in the Collection, or just want to make it explicit that you want everything after a certain point. For this, you should use the END keyword.

    $collection = new Collection(array('Apple', 'Orange', 'Banana', 'Grapes'));
    $subset = $collection['2:END'];

    // $subset contains ('Orange', 'Banana', 'Grapes')

There's also a shortcut to fetching the last item in the collection, the LAST keyword:

    $collection = new Collection(array('Apple', 'Orange', 'Banana', 'Grapes'));
    $subset = $collection[':LAST'];

    // $subset is simply array('Grapes')

### Splicing Exceptions

The following table is the Exceptions which can be thrown by Collection around splicing:

|Exception                              | Reason                                                                                                            |
|---------------------------------------|-------------------------------------------------------------------------------------------------------------------|
|Solution10\Collection\Exception\Bounds | Asking for a start value greater than the count() of the Collection<br>Asking for a start index greater than end  |
|Solution10\Collection\Exception\Index  | Asking for an unknown index                                                                                       |

## Sorting

Collections allow you to sort their contents just like they are real arrays. You can also sort by a property of the items within the collection! Basics first.

### Sorting Basics

Sorting uses the same flags as PHP's [sort()](http://php.net/sort) family of functions. So you can use SORT_NATURAL et al.

To dictate direction, you need to use the following constants:

|Constant                            | Description                                                              |
|------------------------------------|--------------------------------------------------------------------------|
|Collection::SORT_ASC                | Sort the items in Ascending order, rebuilding the keys.                  |
|Collection::SORT_DESC               | Sort the items in Descending order, rebuilding the keys.                 |
|Collection::SORT_ASC_PRESERVE_KEYS	 | Sort the items in Ascending order, maintaining key => value association. |
|Collection::SORT_DESC_PRESERVE_KEYS | Sort the items in Descending order, maintaining key => value association.|

    $collection = new Collection(array(100, 50, 70, 10));
    $collection->sort(Collection::SORT_ASC);

    // $collection's order is now: 10, 50, 70, 100

    // You can pass in sort() flags like so:
    $collection->sort(Collection::SORT_ASC, SORT_NUMERIC);

### Sorting by Member

You can sort the contents of a Collection by some data value within the collections contents! This can be either:

- A keyed item in a multi-d array
- A property on all objects in the Collection
- The result of a function call on all objects within the Collection.

So, for example:

    $collection = new Collection(array(
        array(
            'name' => 'Sarah',
            'job' => 'Manager',
        ),
        array(
            'name' => 'Alex',
            'job' => 'Developer',
        ),
        array(
            'name' => 'Tracy',
            'job' => 'HR'
        ),
    ));

    $collection->sort_by_member('name', Collection::SORT_ASC);

    // The collection now has the array('name' => 'Alex' ...) item first and the array('name' => 'Tracy' ...) item last!

Sorting by a property in contained objects:

    $obj = new stdClass();
    $obj->name = 'Jenny';

    $obj2 = new stdClass();
    $obj2->name = 'Forest';

    $collection = new Collection(array(
        $obj, $obj2
    ));

    $collection->sort_by_member('name', Collection::SORT_ASC);

    // Forest is now first in the collection, with Jenny in second.

You can also sort by the result of a function:

    class Product
    {
        public $price;

        public function price_with_vat()
        {
            return $this->price * 1.20;
        }
    }

    $product1 = new Product();
    $product1->price = 20.00;

    $product2 = new Product();
    $product2->price = 18.99;

    $collection = new Collection(array($product1, $product2));

    $collection->sort_by_member('price_with_vat', Collection::SORT_ASC);

    // $product2 is now first in the array with $product1 second.

### Sorting Exceptions

The following exceptions can be thrown during sorting:

|Exception	                                | Reason                                                                    |
|-------------------------------------------|---------------------------------------------------------------------------|
|Solution10\Collection\Exception\Exception  | Asking for an unknown sort direction.                                     |
|Solution10\Collection\Exception\Index      | Asking for a sort_by_member on an unknown index / property / function.    |

## Getting data back (keys and values)

If you need to get your data out of a collection and into a standard array, you can use:

    $collection->keys(); // Returns the keys of the collection in an array
    $collection->values(); // Returns the values (no keys) of the collection
    $collection->toArray(); // Returns the keys and values in a single array.

