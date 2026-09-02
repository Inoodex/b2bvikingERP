<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function index()
    {
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        return view('backend.accounts.fiscal_years.index', compact('fiscalYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        FiscalYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_closed'  => false,
        ]);

        Toastr::success('Fiscal Year created successfully.', 'Success');
        return redirect()->route('admin.fiscal-years.index');
    }

    public function update(Request $request, FiscalYear $fiscalYear)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $fiscalYear->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        Toastr::success('Fiscal Year updated successfully.', 'Success');
        return redirect()->route('admin.fiscal-years.index');
    }

    public function toggleClose(FiscalYear $fiscalYear)
    {
        $fiscalYear->is_closed = !$fiscalYear->is_closed;
        $fiscalYear->closed_at = $fiscalYear->is_closed ? now() : null;
        $fiscalYear->closed_by = $fiscalYear->is_closed ? auth()->id() : null;
        $fiscalYear->save();

        $status = $fiscalYear->is_closed ? 'Closed & Locked' : 'Reopened';
        Toastr::success("Fiscal Year has been {$status}.", 'Success');
        return redirect()->route('admin.fiscal-years.index');
    }
}
