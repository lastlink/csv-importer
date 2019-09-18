<?php


namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;
use App\Http\Request\ConfigurationPostRequest;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Request\GetAccountsRequest;
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

        /** @var Account $account */
        foreach ($response as $account) {
            $accounts[$account->id] = $account;
        }

        return view('import.configuration.index', compact('mainTitle', 'subTitle','accounts'));
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
        die('OK!');
    }

}
