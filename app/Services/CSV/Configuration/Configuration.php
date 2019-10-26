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
    /** @var bool When set to true, the importer will ignore existing duplicate transactions found in Firefly III. */
    private $ignoreDuplicateTransactions;
    /** @var bool When set to true, the importer will ignore duplicate lines in the CSV file. */
    private $ignoreDuplicateLines;
    /** @var bool */
    private $rules;
    /** @var bool */
    private $skipForm;
    /** @var array */
    private $specifics;
    /** @var array */
    private $roles;
    /** @var int */
    private $version;

    /** @var array */
    private $doMapping;

    /** @var array */
    private $mapping;
    /** @var int */
    public const VERSION = 2;


    /**
     * Configuration constructor.
     */
    private function __construct()
    {
        $this->date                        = 'Y-m-d';
        $this->defaultAccount              = 1;
        $this->delimiter                   = 'comma';
        $this->headers                     = false;
        $this->ignoreDuplicateTransactions = true;
        $this->ignoreDuplicateLines        = true;
        $this->rules                       = true;
        $this->skipForm                    = false;
        $this->specifics                   = [];
        $this->roles                       = [];
        $this->mapping                     = [];
        $this->doMapping                   = [];
        $this->version                     = self::VERSION;
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $version = $array['version'] ?? 1;

        // TODO now have room to do version based array parsing.

        $object                              = new self;
        $object->headers                     = $array['headers'];
        $object->date                        = $array['date'];
        $object->defaultAccount              = $array['default_account'];
        $object->delimiter                   = $array['delimiter'];
        $object->ignoreDuplicateLines        = $array['ignore_duplicate_lines'];
        $object->ignoreDuplicateTransactions = $array['ignore_duplicate_transactions'];
        $object->rules                       = $array['rules'];
        $object->skipForm                    = $array['skip_form'];
        $object->specifics                   = $array['specifics'];
        $object->roles                       = $array['roles'];
        $object->mapping                     = $array['mapping'];
        $object->doMapping                   = $array['do_mapping'];
        $object->version                     = $version;

        return $object;
    }


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
     * @param array $array
     *
     * @return $this
     */
    public static function fromRequest(array $array): self
    {
        $object                              = new self;
        $object->version                     = self::VERSION;
        $object->headers                     = $array['headers'];
        $object->date                        = $array['date'];
        $object->defaultAccount              = $array['default_account'];
        $object->delimiter                   = $array['delimiter'];
        $object->ignoreDuplicateLines        = $array['ignore_duplicate_lines'];
        $object->ignoreDuplicateTransactions = $array['ignore_duplicate_transactions'];
        $object->rules                       = $array['rules'];
        $object->skipForm                    = $array['skip_form'];
        $object->specifics                   = $array['specifics'];
        $object->roles                       = $array['roles'];
        $object->mapping                     = $array['mapping'];
        $object->doMapping                   = $array['do_mapping'];

        return $object;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public static function fromFile(array $data): self
    {
        Log::debug('Now in Configuration::fromClassic', $data);
        $version = $data['version'] ?? 1;

        // TODO now have room to handle different versions.

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
        $object->version        = $version;

        // some

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
            $index                     = (int)$index;
            $object->doMapping[$index] = $map;
        }

        // loop mapping
        $mapping = $data['column-mapping-config'] ?? [];
        foreach ($mapping as $index => $map) {
            $index                   = (int)$index;
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
            'date'                          => $this->date,
            'default_account'               => $this->defaultAccount,
            'delimiter'                     => $this->delimiter,
            'headers'                       => $this->headers,
            'ignore_duplicate_lines'        => $this->ignoreDuplicateLines,
            'ignore_duplicate_transactions' => $this->ignoreDuplicateTransactions,
            'rules'                         => $this->rules,
            'skip_form'                      => $this->skipForm,
            'specifics'                     => $this->specifics,
            'roles'                         => $this->roles,
            'do_mapping'                    => $this->doMapping,
            'mapping'                       => $this->mapping,
            'version' => $this->version,
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
    public function getDefaultAccount(): ?int
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
     * @return array
     */
    public function getSpecifics(): array
    {
        return $this->specifics;
    }


}
