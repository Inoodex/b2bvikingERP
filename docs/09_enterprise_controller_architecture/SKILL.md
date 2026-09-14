---
name: enterprise-controller-sdd
description: "Step-by-step Standard Operating Procedure (SOP) and architectural standards for refactoring Laravel controllers into clean SDD pattern (Thin Controller, FormRequest, Domain Service, Queueable Job) in b2bvikingERP."
---

# 🛠️ Enterprise Controller SDD Refactoring Skill Guide
**Architecture Level:** 25-Year Senior Principal Enterprise Software Architect  
**Project:** B2B Viking ERP ([b2bviking.com](https://b2bviking.com))  
**Target Standard:** Thin HTTP Orchestrators, Isolated FormRequests, Domain Services, Async Queueable Jobs

---

## 📌 1. Purpose & When to Use

Use this skill whenever auditing, refactoring, or authoring controllers across the 12 Navbar modules of B2B Viking ERP:
1. Category
2. Products
3. Inventory
4. Orders & Sales
5. Procurement
6. Reports
7. Accounts
8. Payment Operations
9. Brands
10. Vendors
11. Enterprise Setup
12. System Settings

---

## 🏗️ 2. The 2-Tier Architectural Decision Matrix

As a 25-Year Senior Principal Architect, **never force unnecessary abstraction layers**. Avoid the "Anemic Service Anti-Pattern" where a service merely acts as an empty pass-through for 1-line Eloquent operations.

```
                  ┌──────────────────────────────┐
                  │      Incoming Request        │
                  └──────────────┬───────────────┘
                                 │
                                 ▼
                  ┌──────────────────────────────┐
                  │ 1. FormRequest (Always Used) │
                  │    - Authorization & Rules   │
                  └──────────────┬───────────────┘
                                 │
          Does this action involve multi-table mutations,
        complex domain calculations, external I/O, or jobs?
                                 │
                 ┌───────────────┴───────────────┐
                 │                               │
              YES │                               │ NO (Simple Master CRUD)
                 ▼                               ▼
  ┌─────────────────────────────┐ ┌─────────────────────────────┐
  │ Tier A: Service-Backed SDD  │ │ Tier B: Pragmatic Thin CRUD │
  │ (Orders, Inventory, Invoices│ │ (Color, Size, Unit, Dept)   │
  │  Products Matrix, LC, etc.) │ │                             │
  │ - Domain Service Class      │ │ - No Service file needed    │
  │ - DB::transaction() safety  │ │ - Controller uses Eloquent  │
  │ - Domain Exceptions         │ │   directly (2-3 clean lines)│
  │ - Queueable Jobs (if async) │ │ - Zero pass-through bloat   │
  └──────────────┬──────────────┘ └──────────────┬──────────────┘
                 │                               │
                 └───────────────┬───────────────┘
                                 ▼
                  ┌──────────────────────────────┐
                  │ 2. Thin Controller           │
                  │    - Strict Types (PHP 8.2+) │
                  │    - Route Model Binding     │
                  │    - Uniform HTTP Responses  │
                  └──────────────┬───────────────┘
                                 │
                                 ▼
                  ┌──────────────────────────────┐
                  │ 3. Automated Feature Test    │
                  │    - tests/Feature/Controllers│
                  │    - 100% assertions passed  │
                  └──────────────────────────────┘
```

---

## 🔄 3. The 7-Step Refactoring Process (Step-by-Step SOP)

Follow this exact sequence for every controller:

### Step 1: Controller Inspection & Tier Classification
- Read the entire existing controller.
- Check its route dependencies and business scope.
- **Classify the controller into its appropriate Tier:**
  - **Tier A (Domain Service Required):** Multi-table mutations, inventory/ledger movements, invoice/order calculations, file upload processing, external integrations.
  - **Tier B (Pragmatic Thin CRUD):** Simple master data lookup (e.g. Color, Size, Unit). Do NOT generate a pointless service file!

### Step 2: FormRequest Extraction (Universal)
- Create dedicated FormRequest classes:
  - Create/Store action: `app/Http/Requests/{Module}/{Model}StoreRequest.php`
  - Edit/Update action: `app/Http/Requests/{Module}/{Model}UpdateRequest.php`
- Enforce strict `authorize()` rules and clean `rules()`.

### Step 3: Domain Service Construction (Tier A ONLY)
- If **Tier A**: Create or update the module service in `app/Services/{Module}/{Model}Service.php`.
  - Wrap multi-table operations inside `DB::transaction()`.
  - Throw domain exceptions on invalid business states.
- If **Tier B**: **SKIP THIS STEP.** Do NOT create an anemic service just to call `Model::create()`. Eloquent in controller is already clean and optimal.

### Step 4: Queueable Job Extraction (If Applicable)
- If the method sends emails, generates large PDFs, or broadcasts events, dispatch a Queueable Job in `app/Jobs/` rather than stalling the HTTP thread.

### Step 5: Thin Controller Assembly
- Add `declare(strict_types=1);` at the top of the controller.
- Inject required Services and DataTables via constructor or method injection.
- Add strict parameter types (e.g. `Category $category` via Route Model Binding instead of `string $id`).
- Add explicit return types to every method:
  - `public function index(ModelDataTable $dataTable): JsonResponse|View`
  - `public function create(): View`
  - `public function store(ModelStoreRequest $request): RedirectResponse`
  - `public function edit(Model $model): View`
  - `public function update(ModelUpdateRequest $request, Model $model): RedirectResponse`
  - `public function destroy(Model $model): JsonResponse`
  - `public function changeStatus(Request $request): JsonResponse`

### Step 6: Standardize Response Contracts
- **Redirects:**
  ```php
  toastr()->success(__('Category created successfully'));
  return redirect()->route('admin.category.index');
  ```
- **AJAX / DataTables Responses:**
  ```php
  return response()->json([
      'status' => 'success',
      'message' => __('Status updated successfully'),
      'data' => $result,
  ], 200);
  ```
- **Error Responses:**
  ```php
  return response()->json([
      'status' => 'error',
      'message' => $e->getMessage(),
  ], 422);
  ```

### Step 7: Mandatory Automated Feature Test Creation & 100% Green Verification
- For every controller, create `tests/Feature/Controllers/{Module}/{Controller}Test.php`.
- Test every CRUD and AJAX action:
  - `index` loads 200 OK and renders DataTable.
  - `create` loads form 200 OK.
  - `store` creates record via Service within transaction, checks redirect & toastr.
  - `store validation` catches invalid input.
  - `edit` loads form with correct model 200 OK.
  - `update` persists changes via Service, checks redirect & toastr.
  - `destroy` deletes record and returns JSON `{ status: 'success' }`.
  - `destroy cascade guards` prevents deletion when active child dependencies exist.
  - `changeStatus / AJAX toggles` updates boolean and returns JSON.
- Run the test suite:
  ```bash
  php artisan test tests/Feature/Controllers/{Module}/{Controller}Test.php
  ```
- **Definition of Done requires 100% assertions green before moving to the next controller.**

---

## 💻 4. Standard Boilerplate Templates

### Template A: Tier B — Pragmatic Thin Controller (No Service)
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryCreateRequest;
use App\Http\Requests\Category\CategoryToggleFrontendShowRequest;
use App\Http\Requests\Category\CategoryToggleStatusRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(CategoryDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.category.index');
    }

    public function create(): View
    {
        return view('backend.category.create');
    }

    public function store(CategoryCreateRequest $request): RedirectResponse
    {
        Category::create([
            'name'          => $request->validated('name'),
            'slug'          => Str::slug($request->validated('name')),
            'status'        => (bool) $request->validated('status'),
            'frontend_show' => (bool) $request->validated('frontend_show', false),
        ]);

        Toastr::success(__('Category Created Successfully!'));

        return redirect()->route('admin.category.index');
    }

    public function edit(Category $category): View
    {
        return view('backend.category.edit', compact('category'));
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name'          => $request->validated('name'),
            'slug'          => Str::slug($request->validated('name')),
            'status'        => (bool) $request->validated('status'),
            'frontend_show' => (bool) $request->validated('frontend_show', false),
        ]);

        Toastr::success(__('Category Updated Successfully!'));

        return redirect()->route('admin.category.index');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->subCategories()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => __('This category has subcategories. Please delete them first!'),
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    public function changeStatus(CategoryToggleStatusRequest $request): JsonResponse
    {
        $category = Category::findOrFail((int) $request->validated('id'));
        $category->update(['status' => filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN)]);

        return response()->json([
            'status'  => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }
}
```

### Template A2: Tier A — Service-Backed Controller (Complex Domain)
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug($data['name']);
            return Category::create($data);
        });
    }

    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $data['slug'] = Str::slug($data['name']);
            $category->update($data);
            return $category->fresh();
        });
    }

    public function deleteCategory(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            if ($category->subCategories()->exists()) {
                throw new DomainException(__('Cannot delete category because it contains active subcategories.'));
            }
            return (bool) $category->delete();
        });
    }

    public function updateStatus(Category $category, bool $status): bool
    {
        return $category->update(['status' => $status ? 1 : 0]);
    }
}
```

### Template C: FormRequest
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200|unique:categories,name',
            'status' => 'required|boolean',
            'frontend_show' => 'nullable|boolean',
        ];
    }
}
```

### Template D: Feature Test Boilerplate
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\Category;
use App\Models\SubCategory;
use Tests\Feature\Controllers\ControllerTestCase;

class CategoryControllerTest extends ControllerTestCase
{
    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.category.index'));
        $response->assertOk();
        $response->assertViewIs('backend.category.index');
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.category.create'));
        $response->assertOk();
        $response->assertViewIs('backend.category.create');
    }

    public function test_store_creates_category_and_redirects(): void
    {
        $payload = [
            'name' => 'Men Fashion ' . uniqid(),
            'status' => 1,
            'frontend_show' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.category.store'), $payload);

        $response->assertRedirect(route('admin.category.index'));
        $this->assertDatabaseHas('categories', ['name' => $payload['name']]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.category.store'), []);
        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_edit_displays_form_with_category(): void
    {
        $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'status' => 1]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.category.edit', $category->id));
        $response->assertOk();
        $response->assertViewIs('backend.category.edit');
        $response->assertViewHas('category');
    }

    public function test_update_modifies_category_and_redirects(): void
    {
        $category = Category::create(['name' => 'Old Cat', 'slug' => 'old-cat', 'status' => 1]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.category.update', $category->id), [
            'name' => 'Updated Cat',
            'status' => 1,
            'frontend_show' => 0,
        ]);

        $response->assertRedirect(route('admin.category.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Updated Cat', 'slug' => 'updated-cat']);
    }

    public function test_destroy_deletes_category_and_returns_json(): void
    {
        $category = Category::create(['name' => 'To Delete', 'slug' => 'to-delete', 'status' => 1]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.category.destroy', $category->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_prevents_deletion_if_subcategories_exist(): void
    {
        $category = Category::create(['name' => 'Parent Cat', 'slug' => 'parent-cat', 'status' => 1]);
        SubCategory::create(['category_id' => $category->id, 'name' => 'Sub Cat', 'slug' => 'sub-cat', 'status' => 1]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.category.destroy', $category->id));

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $category = Category::create(['name' => 'Status Cat', 'slug' => 'status-cat', 'status' => 0]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.category.change-status'), [
            'id' => $category->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'status' => 1]);
    }
}
```

---

## 🚫 5. Anti-Patterns to Eliminate Immediately

1. **Inline `$request->validate()`:** Never use in controller actions — all validation lives in a dedicated `FormRequest` class.
2. **Commented Code or `dd()` / `dump()`:** Must be deleted before PR/review.
3. **Unused Imports:** Run static check or remove any `use` statement not referenced in the file.
4. **Missing Return Types:** Every single public controller method must declare its return type.
5. **Direct DB mutations without Services (Tier A ONLY):** A Tier A controller must NOT call `Model::create()` directly when multi-table dependencies, ledger movements, or domain calculations exist — this logic belongs in a Domain Service wrapped in `DB::transaction()`. **Tier B controllers** (simple master data) correctly use Eloquent directly — do NOT add a pass-through service just to satisfy this rule.
6. **Inconsistent Array Responses:** `return response(['status' => '...'])` must always be `return response()->json(['status' => '...'])`.
7. **Wrong HTTP Status on Business Rule Errors:** A `destroy` that is blocked by a business rule guard (e.g. subcategories exist) MUST return HTTP `422 Unprocessable`. Never return HTTP `200 OK` with `{status: 'error'}` in the body.
8. **Stripping Established Traits (e.g. `ImageUploadTrait`):** Never delete or bypass established project traits (`upload_image`, `update_image`, `delete_image`). Refactoring must maintain project-wide utility consistency.
9. **Arbitrary Logic Alteration:** Refactoring means improving structure (strict types, FormRequests, Route Model Binding, return types, dead code removal) WITHOUT rewriting or altering working business logic.
