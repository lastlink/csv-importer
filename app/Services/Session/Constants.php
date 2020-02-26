<?php
/**
 * Constants.php
 * Copyright (c) 2019 james@firefly-iii.org
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

namespace App\Services\Session;

/**
 * Class Constants
 */
class Constants
{
    /** @var string  */
    public const UPLOAD_CSV_FILE = 'csv_file_path';
    /** @var string  */
    public const UPLOAD_CONFIG_FILE = 'config_file_path';
    /** @var string  */
    public const CONFIGURATION = 'configuration';
    /** @var string  */
    public const CONFIG_COMPLETE_INDICATOR = 'config_complete';
    /** @var string string */
    public const ROLES_COMPLETE_INDICATOR = 'role_config_complete';

    /** @var string */
    public const MAPPING_COMPLETE_INDICATOR = 'mapping_config_complete';

    /** @var string */
    public const JOB_STATUS = 'import_job_status';

    public const JOB_IDENTIFIER = 'import_job_id';

}
