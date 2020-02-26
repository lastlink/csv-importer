<?php
/**
 * TokenController.php
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

namespace App\Http\Controllers;


use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Request\SystemInformationRequest;
use App\Services\FireflyIIIApi\Response\SystemInformationResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class TokenController
 */
class TokenController extends Controller
{
    /**
     * Check if the Firefly III API responds properly.
     *
     * @return JsonResponse
     */
    public function doValidate(): JsonResponse
    {
        $response = ['result' => 'OK', 'message' => null];
        $request  = new SystemInformationRequest();
        try {
            $result = $request->get();
        } catch (ApiHttpException $e) {
            $response = ['result' => 'NOK', 'message' => $e->getMessage()];
        }
        // -1 = OK (minimum is smaller)
        // 0 = OK (same version)
        // 1 = NOK (too low a version)

        $minimum = config('csv_importer.minimum_version');
        $compare = version_compare($minimum, $result->version);
        if (1 === $compare) {
            $errorMessage = sprintf('Your Firefly III version %s is below the minimum required version %s', $result->version, $minimum);
            $response = ['result' => 'NOK', 'message' => $errorMessage];
        }

        return response()->json($response);
    }

    /**
     * Same thing but not over JSON.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index()
    {
        $request      = new SystemInformationRequest();
        $errorMessage = 'No error message.';
        $isError      = false;
        $result       = null;
        $compare      = 1;
        try {
            /** @var SystemInformationResponse $result */
            $result = $request->get();
        } catch (ApiHttpException $e) {
            $errorMessage = $e->getMessage();
            $isError      = true;
        }
        // -1 = OK (minimum is smaller)
        // 0 = OK (same version)
        // 1 = NOK (too low a version)
        if (false === $isError) {
            $minimum = config('csv_importer.minimum_version');
            $compare = version_compare($minimum, $result->version);
        }
        if (false === $isError && 1 === $compare) {
            $errorMessage = sprintf('Your Firefly III version %s is below the minimum required version %s', $result->version, $minimum);
            $isError      = true;
        }

        if (false === $isError) {
            return redirect(route('index'));
        }
        $pageTitle = 'Token error';

        return view('token.index', compact('errorMessage', 'pageTitle'));
    }

}
