<?php
/**
 * Configuration.php
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

namespace App\Services\CSV\Configuration;

use Log;

/**
 * Class Configuration
 */
class Configuration
{
    /** @var string */
    private $date;
    /** @var int */
    private $defaultAccount;
    /** @var string */
    private $delimiter;
    /** @var bool */
    private $headers;
    /** @var bool */
    private $ignoreDuplicates;
    /** @var bool */
    private $ignoreLines;
    /** @var bool */
    private $ignoreTransfers;
    /** @var bool */
    private $rules;
    /** @var bool */
    private $skipForm;
    /** @var array */
    private $specifics;
    /** @var array */
    private $roles;

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }


    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Configuration constructor.
     */
    private function __construct()
    {
        $this->date             = 'Y-m-d';
        $this->defaultAccount   = 1;
        $this->delimiter        = 'comma';
        $this->headers          = false;
        $this->ignoreDuplicates = false;
        $this->ignoreLines      = false;
        $this->ignoreTransfers  = false;
        $this->rules            = true;
        $this->skipForm         = false;
        $this->specifics        = [];
        $this->roles            = [];
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $object                   = new self;
        $object->headers          = $array['headers'];
        $object->date             = $array['date'];
        $object->defaultAccount   = $array['defaultAccount'];
        $object->delimiter        = $array['delimiter'];
        $object->ignoreDuplicates = $array['ignoreDuplicates'];
        $object->ignoreLines      = $array['ignoreLines'];
        $object->ignoreTransfers  = $array['ignoreTransfers'];
        $object->rules            = $array['rules'];
        $object->skipForm         = $array['skipForm'];
        $object->specifics        = $array['specifics'];
        $object->roles            = $array['roles'];

        return $object;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public static function fromRequest(array $array): self
    {
        $object                   = new self;
        $object->headers          = $array['headers'];
        $object->date             = $array['date'];
        $object->defaultAccount   = $array['default_account'];
        $object->delimiter        = $array['delimiter'];
        $object->ignoreDuplicates = $array['ignore_duplicates'];
        $object->ignoreLines      = $array['ignore_lines'];
        $object->ignoreTransfers  = $array['ignore_transfers'];
        $object->rules            = $array['rules'];
        $object->skipForm         = $array['skip_form'];
        $object->specifics        = $array['specifics'];
        $object->roles            = $array['roles'];

        return $object;
    }

    /**
     * TODO: column count and column roles. column mapping / do-mapping.
     *
     * @param array $data
     *
     * @return $this
     */
    public static function fromClassic(array $data): self
    {
        Log::debug('Now in Configuration::fromClassic', $data);
        $validDelimiters = [
            ','   => 'comma',
            ';'   => 'semicolon',
            'tab' => 'tab',
        ];


        $object                 = new self;
        $object->date           = $data['date-format'] ?? $object->date;
        $object->defaultAccount = $data['import-account'] ?? $object->defaultAccount;
        $delimiter              = $data['delimiter'] ?? ',';
        $object->delimiter      = $validDelimiters[$delimiter] ?? 'comma';
        $object->headers        = $data['has-headers'] ?? false;
        $object->rules          = $data['apply-rules'] ?? true;
        $object->specifics      = [];

        Log::debug(sprintf('Has headers: %s', var_export($object->headers, true)));

        $specifics = array_keys($data['specifics'] ?? []);
        foreach ($specifics as $name) {
            $class = sprintf('App\\Services\\CSV\\Specifics\\%s', $name);
            if (class_exists($class)) {
                $object->specifics[] = $name;
            }
        }

        // loop roles:
        $roles = $data['column-roles'] ?? [];
        foreach ($roles as $role) {
            // exists in new system?
            $config = config(sprintf('csv_importer.import_roles.%s', $role));
            if (null !== $config) {
                $object->roles[] = $role;
            }
        }
        return $object;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'date'             => $this->date,
            'defaultAccount'   => $this->defaultAccount,
            'delimiter'        => $this->delimiter,
            'headers'          => $this->headers,
            'ignoreDuplicates' => $this->ignoreDuplicates,
            'ignoreLines'      => $this->ignoreLines,
            'ignoreTransfers'  => $this->ignoreTransfers,
            'rules'            => $this->rules,
            'skipForm'         => $this->skipForm,
            'specifics'        => $this->specifics,
            'roles'            => $this->roles,
        ];
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getDefaultAccount(): int
    {
        return $this->defaultAccount;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasSpecific(string $name): bool
    {
        return in_array($name, $this->specifics, true);
    }

    /**
     * @return bool
     */
    public function isHeaders(): bool
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function isIgnoreDuplicates(): bool
    {
        return $this->ignoreDuplicates;
    }

    /**
     * @return bool
     */
    public function isIgnoreLines(): bool
    {
        return $this->ignoreLines;
    }

    /**
     * @return bool
     */
    public function isIgnoreTransfers(): bool
    {
        return $this->ignoreTransfers;
    }

    /**
     * @return bool
     */
    public function isRules(): bool
    {
        return $this->rules;
    }

    /**
     * @return bool
     */
    public function isSkipForm(): bool
    {
        return $this->skipForm;
    }

    /**
     * @return array
     */
    public function getSpecifics(): array
    {
        return $this->specifics;
    }


}
