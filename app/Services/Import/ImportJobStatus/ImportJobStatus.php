<?php
/**
 * ImportJobStatus.php
 * Copyright (c) 2019 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III CSV Importer.
 *
 * Firefly III CSV Importer is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III CSV Importer.If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace App\Services\Import\ImportJobStatus;

/**
 * Class ImportJobStatus
 */
class ImportJobStatus
{
    /** @var string */
    public const JOB_WAITING = 'waiting_to_start';
    /** @var string */
    public const JOB_RUNNING = 'job_running';
    /** @var string */
    public const JOB_ERRORED = 'job_errored';
    /** @var string */
    public const JOB_DONE = 'job_done';

    /** @var string */
    public $status;
    /** @var array */
    public $errors;
    /** @var array */
    public $warnings;
    /** @var array */
    public $messages;

    /**
     * ImportJobStatus constructor.
     */
    public function __construct()
    {
        $this->status   = self::JOB_WAITING;
        $this->errors   = [];
        $this->warnings = [];
        $this->messages = [];
    }

    public static function fromArray(array $array): self
    {
        $config           = new self;
        $config->status   = $array['status'];
        $config->errors   = $array['errors'] ?? [];
        $config->warnings = $array['warnings'] ?? [];
        $config->messages = $array['messages'] ?? [];

        return $config;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status'   => $this->status,
            'errors'   => $this->errors,
            'warnings' => $this->warnings,
            'messages' => $this->messages,
        ];
    }
}
