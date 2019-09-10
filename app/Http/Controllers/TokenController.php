<?php


namespace App\Http\Controllers;


use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Request\SystemInformationRequest;
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
            $request->get();
        } catch (ApiHttpException $e) {
            $response = ['result' => 'NOK', 'message' => $e->getMessage()];
        }

        return response()->json($response);
    }

    /**
     * Same thing but not over JSON.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $request      = new SystemInformationRequest();
        $errorMessage = 'No error message.';
        $isError      = false;
        try {
            $request->get();
        } catch (ApiHttpException $e) {
            $errorMessage = $e->getMessage();
            $isError      = true;
        }
        if (!$isError) {
            return redirect(route('index'));
        }

        return view('token.index', compact('errorMessage'));
    }

}
