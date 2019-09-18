<?php

declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Class SnsDescription.
 */
class SnsDescription implements SpecificInterface
{
    /**
     * Get description of specific.
     * hugovanduijn@gmail.com.
     * @return string
     * @codeCoverageIgnore
     */
    public static function getDescription(): string
    {
        return 'import.specific_sns_descr';
    }

    /**
     * Get name of specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getName(): string
    {
        return 'import.specific_sns_name';
    }

    /**
     * Run specific.
     *
     * @param array $row
     *
     * @return array
     */
    public function run(array $row): array
    {
        $row = array_values($row);
        if (!isset($row[17])) {
            return $row;
        }
        $row[17] = ltrim($row[17], "'");
        $row[17] = rtrim($row[17], "'");

        return $row;
    }
}
