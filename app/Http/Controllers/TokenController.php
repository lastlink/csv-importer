<?php


namespace App\Http\Controllers;


use App\Exceptions\ApiException;
use App\Services\FireflyIIIApi\Request\SystemInformationRequest;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

/**
 * Class TokenController
 */
class TokenController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function doValidate(): JsonResponse
    {
        $response = ['result' => 'OK', 'message' => null];
        $request  = new SystemInformationRequest();
        try {
            $result = $request->authenticatedGet();
        } catch (ApiException $e) {
        } catch (GuzzleException $e) {
            $response = ['result' => 'NOK', 'message' => $e->getMessage()];
        }

        return response()->json($response);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $request      = new SystemInformationRequest();
        $errorMessage = 'No error message.';
        $isError      = false;
        try {
            $request->authenticatedGet();
        } catch (ApiException $e) {
        } catch (GuzzleException $e) {
            $errorMessage = $e->getMessage();
            $isError      = true;
        }
        if (!$isError) {
            return redirect(route('index'));
        }

        return view('token.index', compact('errorMessage'));
    }

}
