<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\MainIndexController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\BundleCourseController;
use Illuminate\Support\Facades\Route;

// 🔐 Rutas de autenticación
Route::group(['middleware' => 'guest'], function () {
    Route::get('sign-up', [RegistrationController::class, 'signUp'])->name('sign-up');
    Route::post('store-sign-up', [RegistrationController::class, 'storeSignUp'])->name('store.sign-up');
});

Route::get('forget-password', [LoginController::class, 'forgetPassword'])->name('forget-password');
Route::post('forget-password', [LoginController::class, 'forgetPasswordEmail'])->name('forget-password.email');
Route::get('reset-password', [LoginController::class, 'resetPassword'])->name('reset-password');
Route::post('reset-password', [LoginController::class, 'resetPasswordCheck'])->name('reset-password.check');

Route::get('user/email/verify/{token}', [RegistrationController::class, 'emailVerification'])->name('user.email.verification');

// 📄 Ruta interna necesaria (ver perfiles)
Route::get('users/{user}/profile', [MainIndexController::class, 'userProfile'])->name('userProfile');

// 📚 Rutas públicas de cursos
Route::get('courses', [CourseController::class, 'allCourses'])->name('courses');
Route::get('course-details/{slug}', [CourseController::class, 'courseDetails'])->name('course-details');
Route::get('category/courses/{slug}', [CourseController::class, 'categoryCourses'])->name('category-courses');
Route::get('subcategory/courses/{slug}', [CourseController::class, 'subCategoryCourses'])->name('subcategory-courses');
Route::get('get-sub-category-courses/fetch-data', [CourseController::class, 'paginationFetchData'])->name('course.fetch-data');
Route::get('get-filter-courses', [CourseController::class, 'getFilterCourse'])->name('getFilterCourse');
Route::post('review-paginate/{courseId}', [CourseController::class, 'reviewPaginate'])->name('frontend.reviewPaginate');
Route::get('search-course-list', [CourseController::class, 'searchCourseList'])->name('search-course.list');

// 🎁 Rutas públicas de bundles
Route::get('bundles', [BundleCourseController::class, 'allBundles'])->name('bundles');
Route::get('bundle-details/{slug?}', [BundleCourseController::class, 'bundleDetails'])->name('bundle-details');
Route::get('get-bundle-courses/fetch-data', [BundleCourseController::class, 'paginationFetchData'])->name('bundle-course.fetch-data');
Route::get('get-filter-bundle-courses', [BundleCourseController::class, 'getFilterBundleCourse'])->name('getFilterBundleCourse');

// Rutas visibles sin login
Route::get('terms-conditions', [MainIndexController::class, 'termConditions'])->name('terms-conditions')->withoutMiddleware('private.mode');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
