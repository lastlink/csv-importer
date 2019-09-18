<?php

declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Class Belfius.
 * Sander Kleykens <sander@kleykens.com>
 *
 * Fixes Belfius CSV files to:
 *  - Correct descriptions for recurring transactions so doubles can be detected when the equivalent incoming
 *    transaction is imported.
 *
 */
class Belfius implements SpecificInterface
{
    /**
     * Description of this specific fix.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getDescription(): string
    {
        return 'import.specific_belfius_descr';
    }

    /**
     * Name of specific fix.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getName(): string
    {
        return 'import.specific_belfius_name';
    }

    /**
     * Run the fix.
     *
     * @param array $row
     *
     * @return array
     *
     */
    public function run(array $row): array
    {
        return Belfius::processRecurringTransactionDescription($row);
    }

    /**
     * Fixes the description for outgoing recurring transactions so doubles can be detected when the equivalent incoming
     * transaction is imported for another bank account.
     *
     * @return array the row containing the new description
     */
    protected static function processRecurringTransactionDescription(array $row): array
    {
        if (!isset($row[5]) || !isset($row[14])) {
            return $row;
        }

        $opposingAccountName = $row[5];
        $description = $row[14];

        preg_match('/DOORLOPENDE OPDRACHT.*\s+' . preg_quote($opposingAccountName, '/') . '\s+(.+)\s+REF.\s*:/', $description, $matches);

        if (isset($matches[1])) {
            $row[14] = $matches[1];
        }

        return $row;
    }
}
