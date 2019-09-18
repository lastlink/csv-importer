<?php


namespace App\Services\FireflyIIIApi\Response;

use App\Services\FireflyIIIApi\Model\Account;
use Illuminate\Support\Collection;
use Iterator;

/**
 * Class GetAccountsResponse
 */
class GetAccountsResponse extends Response implements Iterator
{
    /** @var Collection */
    private $collection;
    /** @var int */
    private $position = 0;

    /**
     * Response constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->collection = new Collection;

        /** @var array $row */
        foreach ($data as $row) {
            $model = Account::fromArray($row);
            $this->collection->push($model);
        }
    }

    /**
     * Return the current element
     *
     * @link  https://php.net/manual/en/iterator.current.php
     * @return Account
     * @since 5.0.0
     */
    public function current(): Account
    {
        return $this->collection->get($this->position);
    }

    /**
     * Return the key of the current element
     *
     * @link  https://php.net/manual/en/iterator.key.php
     * @return int
     * @since 5.0.0
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move forward to next element
     *
     * @link  https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Checks if current position is valid
     *
     * @link  https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        return $this->collection->has($this->position);
    }
}
