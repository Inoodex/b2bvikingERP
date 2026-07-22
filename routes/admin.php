<?php

use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\UserController;
use Illuminate\Support\Facades\Route;

// Route::group(['middleware' => ['auth', 'verified'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
//     Route::resource('users', UserController::class);
//     Route::put('users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
//     Route::resource('role', RolesController::class);
//     Route::resource('permission', PermissionController::class);

//     /** category routes */
//     Route::put('category/change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
//     Route::resource('category', CategoryController::class);

//     /** subcategory routes */
//     Route::put('subcategory/change-status', [SubCategoryController::class, 'changeStatus'])->name('subcategory.change-status');
//     Route::resource('sub-category', SubCategoryController::class);

//     /** child category routes */
//     Route::controller(ChildCategoryController::class)->group(function () {
//         Route::put('child-category/change-status', 'changeStatus')->name('child-category.change-status');
//         Route::get('get-subcategories', 'getSubCategories')->name('get-subCategories');
//     });
//     Route::resource('child-category', ChildCategoryController::class);
// });
