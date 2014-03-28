<?php

class FormsController extends BaseController
{
    public function getIndex()
    {
        return View::make('pages.forms.index');
    }

	public function getExpenseReport()
	{
        return View::make('pages.forms.expense.index');
	}

	public function getExpenseReportNew()
	{
        return View::make('pages.forms.expense.edit');
	}
}
