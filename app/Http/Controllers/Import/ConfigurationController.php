<?php
declare(strict_types=1);
/**
 * ConfigurationController.php
 * Copyright (c) 2020 james@firefly-iii.org
 *
 * This file is part of the Firefly III CSV importer
 * (https://github.com/firefly-iii/csv-importer).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;
use App\Http\Middleware\ConfigComplete;
use App\Http\Request\ConfigurationPostRequest;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\Specifics\SpecificService;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;
use Carbon\Carbon;
use GrumpyDictator\FFIIIApiSupport\Model\Account;
use GrumpyDictator\FFIIIApiSupport\Request\GetAccountsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

/**
 * Class ConfigurationController
 */
class ConfigurationController extends Controller
{
    /**
     * StartController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        app('view')->share('pageTitle', 'Import configuration');
        $this->middleware(ConfigComplete::class);
    }

    /**
     * @throws \GrumpyDictator\FFIIIApiSupport\Exceptions\ApiHttpException
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $mainTitle = 'Import routine';
        $subTitle  = 'Configure your CSV file import';
        $accounts  = [];

        $configuration = null;
        if (session()->has(Constants::CONFIGURATION)) {
            $configuration = Configuration::fromArray(session()->get(Constants::CONFIGURATION));
        }
        // if config says to skip it, skip it:
        if (null !== $configuration && true === $configuration->isSkipForm()) {
            // skipForm
            return redirect()->route('import.roles.index');
        }

        // get list of asset accounts:
        $uri   = (string)config('csv_importer.uri');
        $token = (string)config('csv_importer.access_token');
        $request = new GetAccountsRequest($uri, $token);
        $request->setType(GetAccountsRequest::ASSET);
        $response = $request->get();

        // get list of specifics:
        $specifics = SpecificService::getSpecifics();

        /** @var Account $account */
        foreach ($response as $account) {
            $accounts[$account->id] = $account;
        }


        // send other values through the form. A bit of a hack but OK.
        $roles     = '{}';
        $doMapping = '{}';
        $mapping   = '{}';
        if (null !== $configuration) {
            $roles     = base64_encode(json_encode($configuration->getRoles(), JSON_THROW_ON_ERROR, 512));
            $doMapping = base64_encode(json_encode($configuration->getDoMapping(), JSON_THROW_ON_ERROR, 512));
            $mapping   = base64_encode(json_encode($configuration->getMapping(), JSON_THROW_ON_ERROR, 512));
        }

        // update configuration with old values if present? TODO

        return view(
            'import.configuration.index',
            compact('mainTitle', 'subTitle', 'accounts', 'specifics', 'configuration', 'roles', 'mapping', 'doMapping')
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function phpDate(Request $request): JsonResponse
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $format = $request->get('format');
        $date   = Carbon::make('1984-09-17');

        return response()->json(['result' => $date->format($format)]);
    }

    /**
     * @param ConfigurationPostRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(ConfigurationPostRequest $request)
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        // store config on drive.
        $fromRequest   = $request->getAll();
        $configuration = Configuration::fromRequest($fromRequest);
        $config = StorageService::storeContent(json_encode($configuration));

        session()->put(Constants::CONFIGURATION, $configuration->toArray());

        // set config as complete.
        session()->put(Constants::CONFIG_COMPLETE_INDICATOR, true);

        // redirect to import things?
        return redirect()->route('import.roles.index');
    }

}
