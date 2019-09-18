<?php

declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Class PresidentsChoice.
 */
class PresidentsChoice implements SpecificInterface
{
    /**
     * Description of specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getDescription(): string
    {
        return 'import.specific_pres_descr';
    }

    /**
     * Name of specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getName(): string
    {
        return 'import.specific_pres_name';
    }

    /**
     * Run this specific.
     *
     * @param array $row
     *
     * @return array
     */
    public function run(array $row): array
    {
        $row = array_values($row);
        // first, if column 2 is empty and 3 is not, do nothing.
        // if column 3 is empty and column 2 is not, move amount to column 3, *-1
        if (isset($row[3]) && '' === $row[3]) {
            $row[3] = bcmul($row[2], '-1');
        }
        if (isset($row[1])) {
            // copy description into column 2, which is now usable.
            $row[2] = $row[1];
        }

        return $row;
    }
}
