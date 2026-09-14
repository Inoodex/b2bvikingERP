<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\SliderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Requests\Slider\SliderToggleStatusRequest;
use App\Http\Requests\Slider\SliderUpdateRequest;
use App\Models\Slider;
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SliderController extends Controller
{
    use ImageUploadTrait;

    /**
     * Display a listing of sliders via DataTable.
     */
    public function index(SliderDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.slider.index');
    }

    /**
     * Show the form for creating a new slider.
     */
    public function create(): View
    {
        $nextSerial = max(1, (int) Slider::max('serial') + 1);

        return view('backend.slider.create', compact('nextSerial'));
    }

    /**
     * Store a newly created slider in storage.
     */
    public function store(SliderCreateRequest $request): RedirectResponse
    {
        $bannerPath = $this->upload_image($request, 'banner', 'uploads/sliders');

        Slider::create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'starting_price' => max(0, (float) ($request->validated('starting_price') ?? 0)),
            'button_url' => $request->validated('button_url'),
            'serial' => max(1, (int) $request->validated('serial')),
            'status' => (bool) $request->validated('status'),
            'banner' => $bannerPath,
        ]);

        Toastr::success(__('Slider Created Successfully!'));

        return redirect()->route('admin.slider.index');
    }

    /**
     * Show the form for editing the specified slider.
     */
    public function edit(Slider $slider): View
    {
        return view('backend.slider.edit', compact('slider'));
    }

    /**
     * Update the specified slider in storage.
     */
    public function update(SliderUpdateRequest $request, Slider $slider): RedirectResponse
    {
        $bannerPath = $this->update_image($request, 'banner', 'uploads/sliders', $slider->banner);

        $slider->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'starting_price' => max(0, (float) ($request->validated('starting_price') ?? 0)),
            'button_url' => $request->validated('button_url'),
            'serial' => max(1, (int) $request->validated('serial')),
            'status' => (bool) $request->validated('status'),
            'banner' => $bannerPath ?? $slider->banner,
        ]);

        Toastr::success(__('Slider Updated Successfully!'));

        return redirect()->route('admin.slider.index');
    }

    /**
     * Remove the specified slider from storage and prune image.
     */
    public function destroy(Slider $slider): JsonResponse
    {
        $this->delete_image($slider->banner);
        $slider->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    /**
     * Toggle publication status of a slider.
     */
    public function changeStatus(SliderToggleStatusRequest $request): JsonResponse
    {
        $slider = Slider::findOrFail((int) $request->validated('id'));
        $status = filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN);

        $slider->update(['status' => $status]);

        return response()->json([
            'status' => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }
}
