<?php
declare(strict_types=1);
/**
 * TokenController.php
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

namespace App\Http\Controllers;

use GrumpyDictator\FFIIIApiSupport\Exceptions\ApiHttpException;
use GrumpyDictator\FFIIIApiSupport\Request\SystemInformationRequest;
use GrumpyDictator\FFIIIApiSupport\Response\SystemInformationResponse;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use InvalidArgumentException;
use Str;

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
    public function doValidate(Request $request): JsonResponse
    {
        $response = ['result' => 'OK', 'message' => null];
        $uri      = (string) config('csv_importer.uri');
        $token    = $request->cookie('access_token');
        $request  = new SystemInformationRequest($uri, $token);

        $request->setVerify(config('csv_importer.connection.verify'));
        $request->setTimeOut(config('csv_importer.connection.timeout'));

        try {
            $result = $request->get();
        } catch (ApiHttpException $e) {
            return ['result' => 'NOK', 'message' => $e->getMessage()];
        }
        // -1 = OK (minimum is smaller)
        // 0 = OK (same version)
        // 1 = NOK (too low a version)

        $minimum = (string) config('csv_importer.minimum_version');
        $compare = version_compare($minimum, $result->version);
        if (1 === $compare) {
            $errorMessage = sprintf(
                'Your Firefly III version %s is below the minimum required version %s',
                $result->version, $minimum
            );
            $response     = ['result' => 'NOK', 'message' => $errorMessage];
        }

        return response()->json($response);
    }

    /**
     * @param Request $request
     */
    public function callback(Request $request)
    {
        $state        = (string) $request->session()->pull('state');
        $codeVerifier = (string) $request->session()->pull('code_verifier');
        $clientId     = (int) $request->session()->pull('form_client_id');
        $code         = $request->get('code');
        $baseURL      = config('csv_importer.uri');
        if ('' !== (string) config('csv_importer.vanity_uri')) {
            $baseURL = config('csv_importer.vanity_uri');
        }

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $response = (new Client)->post(sprintf('%s/oauth/token', $baseURL), [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'redirect_uri'  => route('token.callback'),
                'code_verifier' => $codeVerifier,
                'code'          => $code,
            ],
        ]);
        $data     = json_decode((string) $response->getBody(), true);
        return redirect(route('index'))->cookie('access_token', $data['access_token']);
    }

    /**
     * @param Request $request
     */
    public function submitClientId(Request $request)
    {
        $clientId = (int) $request->get('client_id');
        if (0 === $clientId) {
            return redirect(route('token.index'));
        }
        $baseURL = config('csv_importer.uri');
        if ('' !== (string) config('csv_importer.vanity_uri')) {
            $baseURL = config('csv_importer.vanity_uri');
        }

        $state        = Str::random(40);
        $codeVerifier = Str::random(128);
        $request->session()->put('state', $state);
        $request->session()->put('code_verifier', $codeVerifier);
        $request->session()->put('form_client_id', $clientId);

        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
        $query         = http_build_query([
                                              'client_id'             => $clientId,
                                              'redirect_uri'          => route('token.callback'),
                                              'response_type'         => 'code',
                                              'scope'                 => '',
                                              'state'                 => $state,
                                              'code_challenge'        => $codeChallenge,
                                              'code_challenge_method' => 'S256',
                                          ]);

        return redirect(sprintf('%s/oauth/authorize?', $baseURL) . $query);
    }

    /**
     * Same thing but not over JSON.
     *
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function index(Request $request)
    {
        $configToken = (string) config('csv_importer.access_token');
        $clientId    = (int) config('csv_importer.client_id');
        if ('' !== $configToken) {
            // get access token, go back.
            return redirect(route('index'))->cookie('access_token', $configToken);
        }

        if (0 !== $clientId) {
            // base URL
            $baseURL = config('csv_importer.uri');
            if ('' !== (string) config('csv_importer.vanity_uri')) {
                $baseURL = config('csv_importer.vanity_uri');
            }

            $state        = Str::random(40);
            $codeVerifier = Str::random(128);
            $request->session()->put('state', $state);
            $request->session()->put('code_verifier', $codeVerifier);
            $request->session()->put('form_client_id', $clientId);

            $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
            $query         = http_build_query([
                                                  'client_id'             => $clientId,
                                                  'redirect_uri'          => route('token.callback'),
                                                  'response_type'         => 'code',
                                                  'scope'                 => '',
                                                  'state'                 => $state,
                                                  'code_challenge'        => $codeChallenge,
                                                  'code_challenge_method' => 'S256',
                                              ]);

            return redirect(sprintf('%s/oauth/authorize?', $baseURL) . $query);
        }

        // show view, ask for client ID.
        $baseURL = config('csv_importer.uri');
        if ('' !== (string) config('csv_importer.vanity_uri')) {
            $baseURL = config('csv_importer.vanity_uri');
        }
        return view('token.client_id', compact('baseURL'));


        die('hello');
        $uri = (string) config('csv_importer.uri');

        $sysInfoRequest = new SystemInformationRequest($uri, $token);
        $errorMessage   = 'No error message.';
        $isError        = false;
        $result         = null;
        $compare        = 1;
        $minimum        = '';

        $sysInfoRequest->setVerify(config('csv_importer.connection.verify'));
        $sysInfoRequest->setTimeOut(config('csv_importer.connection.timeout'));

        // new code
        if (0 !== $clientId) {

        }

        // if the user has set a client ID, try to get a temporary access token.
        // aparte functie die access token ophaalt bij Firefly III.
        // eventueel user interface om dit te regelen.
        // dan die ergens in een cookie. en done.
        die('here we are');


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
