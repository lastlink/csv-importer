<?php


/**
 * csv_importer.php
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
declare(strict_types=1);

use App\Services\CSV\Specifics\AbnAmroDescription;
use App\Services\CSV\Specifics\AppendHash;
use App\Services\CSV\Specifics\Belfius;
use App\Services\CSV\Specifics\IngBelgium;
use App\Services\CSV\Specifics\IngDescription;
use App\Services\CSV\Specifics\PresidentsChoice;
use App\Services\CSV\Specifics\SnsDescription;


return [
    'version'      => '0.1',
    'access_token' => env('FIREFLY_III_ACCESS_TOKEN'),
    'uri'          => env('FIREFLY_III_URI'),
    'upload_path'  => storage_path('uploads'),
    'specifics'    => [
        AbnAmroDescription::class,
        AppendHash::class,
        Belfius::class,
        IngBelgium::class,
        IngDescription::class,
        PresidentsChoice::class,
        SnsDescription::class,
    ],

    /*
     * Configuration for possible column roles.
     *
     * The key is the short name for the column role. There are five values, which mean this:
     *
     * 'mappable'
     * Whether or not the value in the CSV column can be linked to an existing value in your
     * Firefly database. For example: account names can be linked to existing account names you have already
     * so double entries cannot occur. This process is called "mapping". You have to make each unique value in your
     * CSV file to an existing entry in your database. For example, map all account names in your CSV file to existing
     * accounts. If you have an entry that does not exist in your database, you can set Firefly to ignore it, and it will
     * create it.
     *
     * 'pre-process-map'
     * In the case of tags, there are multiple values in one csv column (for example: "expense groceries snack" in one column).
     * This means the content of the column must be "pre processed" aka split in parts so the importer can work with the data.
     *
     * 'pre-process-mapper'
     * This is the class that will actually do the pre-processing.
     *
     * 'field'
     * I don't believe this value is used any more, but I am not sure.
     *
     * 'converter'
     * The converter is a class in app/Import/Converter that converts the given value into an object Firefly understands.
     * The CategoryName converter can convert a category name into an actual category. This converter will take a mapping
     * into account: if you mapped "Groceries" to category "Groceries" the converter will simply return "Groceries" instead of
     * trying to make a new category also named Groceries.
     *
     * 'mapper'
     * When you map data (see "mappable") you need a list of stuff you can map to. If you say a certain column is mappable
     * and the column contains "category names", the mapper will be "Category" and it will give you a list of possible categories.
     * This way the importer always presents you with a valid list of things to map to.
     *
     *
     *
     */
    'import_roles'        => [
        '_ignore'               => [
            'mappable'        => false,
            'pre-process-map' => false,
            'field'           => 'ignored',
            'converter'       => 'Ignore',
            'mapper'          => null,
        ],
        'bill-id'               => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'bill',
            'converter'       => 'CleanId',
            'mapper'          => 'Bills',
        ],
        'note'                  => [
            'mappable'        => false,
            'pre-process-map' => false,
            'field'           => 'note',
            'converter'       => 'CleanNlString',
        ],
        'bill-name'             => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'bill',
            'converter'       => 'CleanString',
            'mapper'          => 'Bills',
        ],
        'currency-id'           => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'currency',
            'converter'       => 'CleanId',
            'mapper'          => 'TransactionCurrencies',
        ],
        'currency-name'         => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'currency',
            'mapper'          => 'TransactionCurrencies',
        ],
        'currency-code'         => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'currency',
            'mapper'          => 'TransactionCurrencies',
        ],
        'foreign-currency-code' => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'foreign_currency',
            'mapper'          => 'TransactionCurrencies',
        ],
        'external-id'           => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'external-id',
        ],
        'currency-symbol'      => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'currency',
            'mapper'          => 'TransactionCurrencies',
        ],
        'description'          => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'description',
        ],
        'date_transaction'     => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date',
        ],
        'date_interest'        => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-interest',
        ],
        'date_book'            => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-book',
        ],
        'date_process'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-process',
        ],
        'date_due'             => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-due',
        ],
        'date_payment'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-payment',
        ],
        'date_invoice'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Date',
            'field'           => 'date-invoice',
        ],
        'budget-id'            => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanId',
            'field'           => 'budget',
            'mapper'          => 'Budgets',
        ],
        'budget-name'          => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'budget',
            'mapper'          => 'Budgets',
        ],
        'rabo-debit-credit'    => [
            'mappable   '     => false,
            'pre-process-map' => false,
            'converter'       => 'BankDebitCredit',
            'field'           => 'amount-modifier',
        ],
        'ing-debit-credit'     => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'BankDebitCredit',
            'field'           => 'amount-modifier',
        ],
        'generic-debit-credit' => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'BankDebitCredit',
            'field'           => 'amount-modifier',
        ],
        'category-id'          => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanId',
            'field'           => 'category',
            'mapper'          => 'Categories',
        ],
        'category-name'        => [
            'mappable'        => true,
            'pre-process-map' => false,
            'converter'       => 'CleanString',
            'field'           => 'category',
            'mapper'          => 'Categories',
        ],
        'tags-comma'           => [
            'mappable'           => false,
            'pre-process-map'    => true,
            'pre-process-mapper' => 'TagsComma',
            'field'              => 'tags',
            'converter'          => 'TagsComma',
            'mapper'             => 'Tags',
        ],
        'tags-space'           => [
            'mappable'           => false,
            'pre-process-map'    => true,
            'pre-process-mapper' => 'TagsSpace',
            'field'              => 'tags',
            'converter'          => 'TagsSpace',
            'mapper'             => 'Tags',
        ],
        'account-id'           => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'asset-account-id',
            'converter'       => 'CleanId',
            'mapper'          => 'AssetAccounts',
        ],
        'account-name'         => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'asset-account-name',
            'converter'       => 'CleanString',
            'mapper'          => 'AssetAccounts',
        ],
        'account-iban'         => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'asset-account-iban',
            'converter'       => 'Iban',
            'mapper'          => 'AssetAccountIbans',

        ],
        'account-number'       => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'asset-account-number',
            'converter'       => 'CleanString',
            'mapper'          => 'AssetAccounts',
        ],
        'account-bic'          => [
            'mappable'        => false,
            'pre-process-map' => false,
            'field'           => 'asset-account-bic',
            'converter'       => 'CleanString',
        ],
        'opposing-id'          => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'opposing-account-id',
            'converter'       => 'CleanId',
            'mapper'          => 'OpposingAccounts',
        ],
        'opposing-bic'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'field'           => 'opposing-account-bic',
            'converter'       => 'CleanString',
        ],
        'opposing-name'        => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'opposing-account-name',
            'converter'       => 'CleanString',
            'mapper'          => 'OpposingAccounts',
        ],
        'opposing-iban'        => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'opposing-account-iban',
            'converter'       => 'Iban',
            'mapper'          => 'OpposingAccountIbans',
        ],
        'opposing-number'      => [
            'mappable'        => true,
            'pre-process-map' => false,
            'field'           => 'opposing-account-number',
            'converter'       => 'CleanString',
            'mapper'          => 'OpposingAccounts',
        ],
        'amount'               => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Amount',
            'field'           => 'amount',
        ],
        'amount_debit'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'AmountDebit',
            'field'           => 'amount_debit',
        ],
        'amount_credit'        => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'AmountCredit',
            'field'           => 'amount_credit',
        ],
        'amount_negated'       => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'AmountNegated',
            'field'           => 'amount_negated',
        ],
        'amount_foreign'       => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Amount',
            'field'           => 'amount_foreign',
        ],
        'sepa_ct_id'           => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_ct_id',
        ],
        'sepa_ct_op'           => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_ct_op',
        ],
        'sepa_db'              => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_db',
        ],
        'sepa_cc'              => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_cc',
        ],
        'sepa_country'         => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_country',
        ],
        'sepa_ep'              => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_ep',
        ],
        'sepa_ci'              => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_ci',
        ],
        'sepa_batch_id'        => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'sepa_batch',
        ],
        'internal_reference'   => [
            'mappable'        => false,
            'pre-process-map' => false,
            'converter'       => 'Description',
            'field'           => 'internal_reference',
        ],
    ],
    'role_to_transaction' => [
        'account-id'            => 'source_id',
        'account-iban'          => 'source_iban',
        'account-name'          => 'source_name',
        'account-number'        => 'source_number',
        'account-bic'           => 'source_bic', // TODO not present in API.
        'opposing-id'           => 'destination_id',
        'opposing-iban'         => 'destination_iban',
        'opposing-name'         => 'destination_name',
        'opposing-number'       => 'destination_number',
        'opposing-bic'          => 'destination_bic', // TODO not present in API.
        'sepa_cc'               => 'sepa_cc',
        'sepa_ct_op'            => 'sepa_ct_op',
        'sepa_ct_id'            => 'sepa_ct_id',
        'sepa_db'               => 'sepa_db',
        'sepa_country'          => 'sepa_country',
        'sepa_ep'               => 'sepa_ep',
        'sepa_ci'               => 'sepa_ci',
        'sepa_batch_id'         => 'sepa_batch_id',
        'amount'                => 'amount', // TODO needs work
        'amount_debit'          => 'amount_debit', // TODO needs work
        'amount_credit'         => 'amount_credit', // TODO needs work
        'amount_negated'        => 'amount_negated', // TODO needs work
        'amount_foreign'        => 'foreign_amount',
        'foreign-currency-id'   => 'foreign_currency_id',
        'foreign-currency-code' => 'foreign_currency_code',
        'bill-id'               => 'bill_id',
        'bill-name'             => 'bill_name',
        'budget-id'             => 'budget_id',
        'budget-name'           => 'budget_name',
        'category-id'           => 'category_id',
        'category-name'         => 'category_name',
        'currency-id'           => 'currency_id',
        'currency-name'         => 'currency_name',
        'currency-symbol'       => 'currency_symbol',
        'description'           => 'description',
        'note'                  => 'notes',
        'ing-debit-credit'      => 'amount_modifier',
        'rabo-debit-credit'     => 'amount_modifier',
        'generic-debit-credit'  => 'amount_modifier',
        'external-id'           => 'external_id',
        'internal_reference'    => 'internal_reference',
        'original-source'       => 'original_source',
        'tags-comma'            => 'tags_comma', // TODO needs extra conversion
        'tags-space'            => 'tags_space', // TODO needs extra conversion
        'date_transaction'      => 'date',
        'date_interest'         => 'interest_date',
        'date_book'             => 'book_date',
        'date_process'          => 'process_date',
        'date_due'              => 'due_date',
        'date_payment'          => 'payment_date',
        'date_invoice'          => 'invoice_date',
        'currency-code'         => 'code_currency',

    ],
];
