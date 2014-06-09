<?php

class FormsController extends BaseController
{
    protected $userHasAccess = false;
    protected $userCanManage = false;

    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
        $this->initAccess();
    }

    /*
     * Todo: refactor this so that 1) it makes sense and 2) we get better 
     * "no access" feedback
     */
    protected function initAccess()
    {
        $user = Auth::user();

        if ($user->hasRole('Store' . Session::get('storeContext'))) {
            if ($user->hasRole('Manager'))
            {
                $this->userHasAccess = true;
                $this->userCanManage = true;
            } elseif ($user->hasRole('Associate')) {
                $this->userHasAccess = true;
                $this->userCanManage = false;
            }
        } 
    }

    public function getIndex()
    {
        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        return View::make('pages.forms.index');
    }

	public function getExpenseReport()
	{
        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        return View::make('pages.forms.expense.index');
	}

	public function getExpenseReportNew()
	{

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        return View::make('pages.forms.expense.edit');
	}
}
