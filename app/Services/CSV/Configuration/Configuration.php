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

use App\Services\CSV\Specifics\SpecificService;
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

    /** @var array */
    private $doMapping;

    /** @var array */
    private $mapping;

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param array $doMapping
     */
    public function setDoMapping(array $doMapping): void
    {
        $this->doMapping = $doMapping;
    }

    /**
     * @param array $mapping
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
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
        $this->mapping          = [];
        $this->doMapping        = [];
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
        $object->mapping          = $array['mapping'];
        $object->doMapping        = $array['do_mapping'];

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
        $object->mapping          = $array['mapping'];
        $object->doMapping        = $array['do_mapping'];

        return $object;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public static function fromClassic(array $data): self
    {
        Log::debug('Now in Configuration::fromClassic', $data);

        // todo move to config file
        $validDelimiters = [
            ','   => 'comma',
            ';'   => 'semicolon',
            'tab' => 'tab',
        ];
        // todo move to config file
        $replaceOldRoles = [
            'original-source'    => 'original_source',
            'sepa-cc'            => 'sepa_cc',
            'sepa-ct-op'         => 'sepa_ct_op',
            'sepa-ct-id'         => 'sepa_ct_id',
            'sepa-db'            => 'sepa_db',
            'sepa-country'       => 'sepa_country',
            'sepa-ep'            => 'sepa_ep',
            'sepa-ci'            => 'sepa_ci',
            'sepa-batch-id'      => 'sepa_batch_id',
            'internal-reference' => 'internal_reference',
            'date-interest'      => 'date_interest',
            'date-invoice'       => 'date_invoice',
            'date-book'          => 'date_book',
            'date-payment'       => 'date_payment',
            'date-process'       => 'date_process',
            'date-due'           => 'date_due',
            'date-transaction'   => 'date_transaction',
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
            $class = SpecificService::fullClass($name);
            if (class_exists($class)) {
                $object->specifics[] = $name;
            }
        }

        // loop roles:
        $roles = $data['column-roles'] ?? [];
        foreach ($roles as $role) {

            // some roles have been given a new name some time in the past.
            $role = $replaceOldRoles[$role] ?? $role;

            $config = config(sprintf('csv_importer.import_roles.%s', $role));
            if (null !== $config) {
                $object->roles[] = $role;
            }
        }

        // loop do mapping
        $doMapping = $data['column-do-mapping'] ?? [];
        foreach ($doMapping as $index => $map) {
            $object->doMapping[$index] = $map;
        }

        // loop mapping
        $mapping = $data['column-mapping-config'] ?? [];
        foreach ($mapping as $index => $map) {
            $object->mapping[$index] = $map;
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
            'do_mapping'       => $this->doMapping,
            'mapping'          => $this->mapping,
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
     * @return array
     */
    public function getDoMapping(): array
    {
        return $this->doMapping;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->mapping;
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
