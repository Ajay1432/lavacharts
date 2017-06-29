<?php

namespace Khill\Lavacharts\DataTables\Rows;

use ArrayAccess;
use Carbon\Carbon;
use IteratorAggregate;
use Khill\Lavacharts\DataTables\Cells\Cell;
use Khill\Lavacharts\DataTables\Cells\NullCell;
use Khill\Lavacharts\DataTables\Cells\DateCell;
use Khill\Lavacharts\DataTables\DataTable;
use Khill\Lavacharts\Exceptions\InvalidCellCount;
use Khill\Lavacharts\Exceptions\InvalidColumnIndex;
use Khill\Lavacharts\Exceptions\InvalidDate;
use Khill\Lavacharts\Exceptions\InvalidRowDefinition;
use Khill\Lavacharts\Support\Contracts\Arrayable;
use Khill\Lavacharts\Support\Contracts\Jsonable;
use Khill\Lavacharts\Support\Traits\ArrayToJsonTrait as ArrayToJson;
use Khill\Lavacharts\Values\StringValue;
use Traversable;

/**
 * Row Object
 *
 * The row object contains all the data for a row, stored in an array, indexed by columns.
 *
 *
 * @package   Khill\Lavacharts\DataTables\Rows
 * @since     3.0.0
 * @author    Kevin Hill <kevinkhill@gmail.com>
 * @copyright (c) 2017, KHill Designs
 * @link      http://github.com/kevinkhill/lavacharts GitHub Repository Page
 * @link      http://lavacharts.com                   Official Docs Site
 * @license   http://opensource.org/licenses/MIT      MIT
 */
class Row implements ArrayAccess, Arrayable, Jsonable, IteratorAggregate
{
    use ArrayToJson;

    /**
     * Row values
     *
     * @var Cell[]
     */
    protected $cells;

    /**
     * Creates a new Row object with the given values from an array.
     *
     * While iterating through the array, if the value is a...
     *  - Cell, pass it through
     *  - Scalar value, create a Cell
     *  - Carbon instance, create a DateCell
     *  - null value, create a NullCell
     *
     * @param array $values Array of row values.
     */
    public function __construct($values)
    {
        $this->cells = array_map(function ($cellValue) {
            if ($cellValue instanceof Carbon) {
                return new DateCell($cellValue);
            }

            if ($cellValue instanceof Cell) {
                return $cellValue;
            }

            if (is_null($cellValue)) {
                return new NullCell();
            }

            return new Cell($cellValue);
        }, $values);
    }

    /**
     * Creates a new Row object from an array of values.
     *
     * @param \Khill\Lavacharts\DataTables\DataTable $datatable
     * @param  array                                 $values Array of values to assign to the row.
     * @return \Khill\Lavacharts\DataTables\Rows\Row
     * @throws \Khill\Lavacharts\Exceptions\InvalidCellCount
     * @throws \Khill\Lavacharts\Exceptions\InvalidDate
     * @throws \Khill\Lavacharts\Exceptions\InvalidRowDefinition
     */
    public static function create(DataTable $datatable, $values)
    {
        $columnCount = $datatable->getColumnCount();

        if ($values !== null && is_array($values) === false) {
            throw new InvalidRowDefinition($values);
        }

        if ($values === null || is_array($values) && empty($values)) {
            return new NullRow($columnCount);
        }

        $cellCount = count($values);

        if ($cellCount > $columnCount) {
            throw new InvalidCellCount($cellCount, $columnCount);
        }

        $columnTypes    = $datatable->getColumnTypes();
        $dateTimeFormat = $datatable->getOptions()->get('datetime_format');

        $rowData = [];

        foreach ($values as $index => $cellValue) {
            // Regardless of column type, a null creates a NullRow
            if ($cellValue === null) {
                $rowData[] = new NullCell;
            }

            // Also regardless of column type, if a Cell is explicitly defined by
            // an array, then create a new Cell with the values.
            if (is_array($cellValue) === true) {
                $rowData[] = Cell::create($cellValue);
            }

            if (preg_match('/date|datetime|timeofday/', $columnTypes[$index])) {



                if (StringValue::isNonEmpty($cellValue) === false &&
                    $cellValue instanceof Carbon === false &&
                    $cellValue !== null
                ) {
                    throw new InvalidDate($cellValue);
                }

                 else if ($cellValue instanceof Carbon) {
                    $rowData[] = new DateCell($cellValue);
                } else {
                    if (isset($dateTimeFormat)) {
                        //@TODO: update this for getting datTimeFormat from options
                        $rowData[] = DateCell::parseString($cellValue, $dateTimeFormat);
                    } else {
                        $rowData[] = DateCell::parseString($cellValue);
                    }
                }
            } else {
                if (is_array($cellValue) === true) {

                } else {
                    $rowData[] = $cellValue;
                }
            }
        }

        return new self($rowData);
    }

    /**
     * Returns a column value from the Row.
     *
     * @param  int $index Column value to fetch from the row.
     * @throws \Khill\Lavacharts\Exceptions\InvalidColumnIndex
     * @return \Khill\Lavacharts\DataTables\Cells\Cell
     */
    public function getCell($index)
    {
        if (is_int($index) === false || isset($this->cells[$index]) === false) {
            throw new InvalidColumnIndex($index, count($this->cells));
        }

        return $this->cells[$index];
    }

    /**
     * Returns the Row as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return ['c' => $this->cells];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->cells[] = $value;
        } else {
            $this->cells[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->cells[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->cells[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->cells[$offset]) ? $this->cells[$offset] : null;
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {

    }
}
