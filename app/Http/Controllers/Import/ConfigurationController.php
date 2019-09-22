<?php
/**
 * ConfigurationController.php
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

namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;
use App\Http\Middleware\ConfigComplete;
use App\Http\Middleware\UploadedFiles;
use App\Http\Request\ConfigurationPostRequest;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\Specifics\SpecificService;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Request\GetAccountsRequest;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $mainTitle = 'Import routine';
        $subTitle  = 'Configure your CSV file import';
        $accounts  = [];

        // get list of asset accounts:
        $request = new GetAccountsRequest;
        $request->setType(GetAccountsRequest::ASSET);
        $response = $request->get();

        // get list of specifics:
        $specifics = SpecificService::getSpecifics();

        /** @var Account $account */
        foreach ($response as $account) {
            $accounts[$account->id] = $account;
        }

        $configuration = null;
        if (session()->has(Constants::CONFIGURATION)) {
            $configuration = Configuration::fromArray(session()->get(Constants::CONFIGURATION));
        }

        // update configuration with old values if present? TODO

        return view('import.configuration.index', compact('mainTitle', 'subTitle', 'accounts', 'specifics', 'configuration'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function phpDate(Request $request): JsonResponse
    {
        $format = $request->get('format');
        $date   = Carbon::make('1984-09-17');

        return response()->json(['result' => $date->format($format)]);
    }

    /**
     * @param ConfigurationPostRequest $request
     */
    public function postIndex(ConfigurationPostRequest $request)
    {
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
