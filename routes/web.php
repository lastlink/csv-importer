<?php
/**
 * web.php
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

Route::get('/', 'IndexController@index')->name('index');

// validate access token:
Route::get('/token', 'TokenController@index')->name('token.index');
Route::get('/token/validate', 'TokenController@doValidate')->name('token.validate');

// clear session
Route::get('/flush','IndexController@flush')->name('flush');

// start import thing.
Route::get('/import/start', ['uses' => 'Import\StartController@index', 'as' => 'import.start']);
Route::post('/import/upload', ['uses' => 'Import\UploadController@upload', 'as' => 'import.upload']);
Route::get('/import/configure', ['uses' => 'Import\ConfigurationController@index', 'as' => 'import.configure.index']);
Route::post('/import/configure', ['uses' => 'Import\ConfigurationController@postIndex', 'as' => 'import.configure.post']);

// import config helper
Route::get('/import/php_date', ['uses' => 'Import\ConfigurationController@phpDate', 'as' => 'import.configure.php_date']);

// roles
Route::get('/import/roles', ['uses' => 'Import\RoleController@index', 'as' => 'import.roles.index']);
Route::post('/import/roles', ['uses' => 'Import\RoleController@postIndex', 'as' => 'import.roles.post']);
