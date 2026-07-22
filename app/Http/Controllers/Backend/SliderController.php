<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SliderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Requests\Slider\SliderUpdateRequest;
use App\Models\Slider;
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    use ImageUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(SliderDataTable $dataTable)
    {
        return $dataTable->render('backend.slider.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextSerial = (int) Slider::max('serial') + 1;
        if ($nextSerial < 1) {
            $nextSerial = 1;
        }

        return view('backend.slider.create', compact('nextSerial'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SliderCreateRequest $request)
    {
        $bannerPath = $this->upload_image($request, 'banner', 'uploads/sliders');

        Slider::create([
            'title' => $request->title,
            'description' => $request->description,
            'starting_price' => max(0, (float) $request->starting_price),
            'button_url' => $request->button_url,
            'serial' => max(1, (int) $request->serial),
            'status' => $request->status,
            'banner' => $bannerPath,
        ]);

        Toastr::success('Slider Created Successfully!');
        return redirect()->route('admin.slider.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('backend.slider.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SliderUpdateRequest $request, string $id)
    {
        $slider = Slider::findOrFail($id);
        $bannerPath = $this->update_image($request, 'banner', 'uploads/sliders', $slider->banner);

        $slider->title = $request->title;
        $slider->description = $request->description;
        $slider->starting_price = max(0, (float) $request->starting_price);
        $slider->button_url = $request->button_url;
        $slider->serial = max(1, (int) $request->serial);
        $slider->status = $request->status;

        if ($request->hasFile('banner')) {
            $slider->banner = $bannerPath;
        }

        $slider->save();

        Toastr::success('Slider Updated Successfully!');
        return redirect()->route('admin.slider.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
        $this->delete_image($slider->banner);
        $slider->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change slider status.
     */
    public function changeStatus(Request $request)
    {
        $slider = Slider::findOrFail($request->id);
        $slider->status = $request->status == 'true' ? 1 : 0;
        $slider->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
