<?php


namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;

/**
 * Class StartController
 */
class StartController extends Controller
{
    /**
     * StartController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        app('view')->share('pageTitle', 'Import');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $mainTitle = 'Import routine';
        $subTitle  = 'Start page and instructions';

        return view('import.index', compact('mainTitle', 'subTitle'));
    }
}
