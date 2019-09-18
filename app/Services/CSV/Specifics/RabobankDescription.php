<?php

declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Class RabobankDescription.
 *
 * @codeCoverageIgnore
 * @deprecated
 */
class RabobankDescription implements SpecificInterface
{
    /**
     * Description of this specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getDescription(): string
    {
        return 'import.specific_rabo_descr';
    }

    /**
     * Name of this specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getName(): string
    {
        return 'import.specific_rabo_name';
    }

    /**
     * Run the specific.
     *
     * @param array $row
     *
     * @return array
     *
     */
    public function run(array $row): array
    {
        $row = array_values($row);

        return $row;
    }
}
