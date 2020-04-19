<?php
declare(strict_types=1);


namespace App\Console;

/**
 * Trait ManageMessages
 */
trait ManageMessages
{

    /**
     * @param string $key
     * @param array  $messages
     */
    protected function listMessages(string $key, array $messages): void
    {
        $functions = [
            'ERROR'   => 'error',
            'Warning' => 'warning',
            'Message' => 'info',
        ];

        $func = $functions[$key] ?? 'line';

        if (count($messages) > 0) {
            /**
             * @var int   $index
             * @var array $error
             */
            foreach ($messages as $index => $list) {
                /** @var string $line */
                foreach ($list as $line) {

                    $this->$func(sprintf('%s in line #%d: %s', $key, $index + 1, $line));
                }
            }
        }
    }
}
