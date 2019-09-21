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
    'specifics' => [
        AbnAmroDescription::class,
        AppendHash::class,
        Belfius::class,
        IngBelgium::class,
        IngDescription::class,
        PresidentsChoice::class,
        SnsDescription::class,
    ],
];
