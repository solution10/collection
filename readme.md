# Solution10\Collection

Like Arrays. Only better!

[![Build Status](https://travis-ci.org/Solution10/collection.svg?branch=master)](https://travis-ci.org/Solution10/collection)
[![Coverage Status](https://coveralls.io/repos/Solution10/collection/badge.png)](https://coveralls.io/r/Solution10/collection)

[![Latest Stable Version](https://poser.pugx.org/solution10/collection/v/stable.svg)](https://packagist.org/packages/solution10/collection)
[![Total Downloads](https://poser.pugx.org/solution10/collection/downloads.svg)](https://packagist.org/packages/solution10/collection)
[![Latest Unstable Version](https://poser.pugx.org/solution10/collection/v/unstable.svg)](https://packagist.org/packages/solution10/collection)
[![License](https://poser.pugx.org/solution10/collection/license.svg)](https://packagist.org/packages/solution10/collection)

## What does this do?

Collections, at their core, are implementations of Iterator, ArrayAccess and Countable. But they're also a lot more than that.

Splice subsections of arrays simply by passing keys:

    $collection = new Collection(array('Apple', 'Orange', 'Banana'));
    $subset = $collection['1:2'];
    // $subset is now: array('Apple', 'Orange')

    $collection = new Collection(array('Apple', 'Orange', 'Banana'));
    $subset = $collection['-2:2'];
    // $subset contains ('Orange', 'Banana')

    $collection = new Collection(array('Apple', 'Orange', 'Banana', 'Grapes'));
    $subset = $collection[':LAST'];
    // $subset is simply array('Grapes')

Quickly and easily Sort

    $collection = new Collection(array(100, 50, 70, 10));
    $collection->sort(Collection::SORT_ASC);
    // $collection's order is now: 10, 50, 70, 100

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
    $collection->sortByMember('name', Collection::SORT_ASC);

## Installation

Install via composer:

    "require": {
        "solution10/collection": "1.*"
    }

## Requirements

- PHP >= 5.3

That's it!

## Documentation

See the [docs/ folder in the repo](http://github.com/solution10/collection/tree/master/docs).

## License

[MIT](http://github.com/solution10/collection/tree/master/LICENSE.md)

## Contributing

[Contributors Notes](http://github.com/solution10/collection/tree/master/CONTRIBUTING.md)
