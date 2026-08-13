<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DocumentSequenceDataTable;
use App\Http\Controllers\Controller;
use App\Models\DocumentSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentSequenceController extends Controller
{
    public function index(DocumentSequenceDataTable $dataTable)
    {
        $sequences = DocumentSequence::orderBy('model_type')->get();

        return $dataTable->render('backend.document_sequences.index', compact('sequences'));
    }

    public function update(Request $request, DocumentSequence $documentSequence): RedirectResponse
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'padding' => 'required|integer|min:1|max:10',
            'next_number' => 'required|integer|min:1',
            'reset_policy' => 'required|in:yearly,monthly,never',
            'include_date' => 'nullable|boolean',
            'date_format' => 'required|string|max:10',
        ]);

        $validated['include_date'] = $request->has('include_date');

        $documentSequence->update($validated);

        toastr()->success('Document Sequence updated successfully for ' . $documentSequence->model_type);

        return redirect()->route('admin.document-sequences.index');
    }
}
