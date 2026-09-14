<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\Slider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Controllers\ControllerTestCase;

class SliderControllerTest extends ControllerTestCase
{
    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.slider.index'));

        $response->assertOk();
        $response->assertViewIs('backend.slider.index');
    }

    public function test_create_displays_form_with_next_serial(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.slider.create'));

        $response->assertOk();
        $response->assertViewIs('backend.slider.create');
        $response->assertViewHas('nextSerial');
    }

    public function test_store_creates_slider_with_banner_and_redirects(): void
    {
        Storage::fake('public');

        $payload = [
            'title' => 'Summer Sale ' . uniqid(),
            'description' => 'Up to 50% off on Viking collections',
            'starting_price' => 199.99,
            'button_url' => 'https://b2bviking.com/summer-sale',
            'serial' => 1,
            'status' => 1,
            'banner' => UploadedFile::fake()->image('slider_banner.jpg', 1200, 400),
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.slider.store'), $payload);

        $response->assertRedirect(route('admin.slider.index'));
        $this->assertDatabaseHas('sliders', [
            'title' => $payload['title'],
            'serial' => 1,
            'status' => 1,
        ]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.slider.store'), []);

        $response->assertSessionHasErrors(['title', 'serial', 'status', 'banner']);
    }

    public function test_edit_displays_form_with_slider(): void
    {
        $slider = Slider::create([
            'title' => 'Promo ' . uniqid(),
            'serial' => 2,
            'status' => 1,
            'banner' => 'uploads/sliders/sample.jpg',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.slider.edit', $slider->id));

        $response->assertOk();
        $response->assertViewIs('backend.slider.edit');
        $response->assertViewHas('slider');
    }

    public function test_update_modifies_slider_and_redirects(): void
    {
        $slider = Slider::create([
            'title' => 'Old Title ' . uniqid(),
            'starting_price' => 50.00,
            'serial' => 3,
            'status' => 1,
            'banner' => 'uploads/sliders/old_banner.jpg',
        ]);

        $updatedTitle = 'New Title ' . uniqid();

        $response = $this->actingAs($this->adminUser)->put(route('admin.slider.update', $slider->id), [
            'title' => $updatedTitle,
            'starting_price' => 99.00,
            'serial' => 3,
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.slider.index'));
        $this->assertDatabaseHas('sliders', [
            'id' => $slider->id,
            'title' => $updatedTitle,
        ]);
    }

    public function test_destroy_deletes_slider_and_returns_json(): void
    {
        $slider = Slider::create([
            'title' => 'To Delete ' . uniqid(),
            'serial' => 4,
            'status' => 1,
            'banner' => 'uploads/sliders/del_banner.jpg',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.slider.destroy', $slider->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('sliders', ['id' => $slider->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $slider = Slider::create([
            'title' => 'Status Slider ' . uniqid(),
            'serial' => 5,
            'status' => 0,
            'banner' => 'uploads/sliders/status_banner.jpg',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.slider.change-status'), [
            'id' => $slider->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('sliders', [
            'id' => $slider->id,
            'status' => 1,
        ]);
    }
}
