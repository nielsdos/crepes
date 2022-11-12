<?php

$routeCustomizedNames = config('app.route_customized_names');

Auth::routes(['verify' => true]);
Route::get('email/change/{id}', 'Auth\VerificationController@change')->name('verification.change.email');
Route::get('/', 'CourseController@index')->name('course.index');
Route::resource($routeCustomizedNames['course'], 'CourseController', [
    'names' => [
        'create' => 'course.create',
        'edit' => 'course.edit',
        'update' => 'course.update',
        'destroy' => 'course.destroy',
        'store' => 'course.store',
    ],
    'parameters' => [$routeCustomizedNames['course'] => 'course'],
    'except' => ['index', 'show'],
]);
Route::get($routeCustomizedNames['course'].'/export-excel/{course}', 'CourseController@exportExcel')->name('course.export.xlsx');
Route::get($routeCustomizedNames['course'].'/export-csv/{course}', 'CourseController@exportCSV')->name('course.export.csv');
Route::get($routeCustomizedNames['course'].'/{course}/{slug?}', 'CourseController@show')->name('course.show');
Route::get($routeCustomizedNames['course'], 'CourseIndexController@index');
Route::get($routeCustomizedNames['subscriptions'], 'SubscriptionController@index')->name('subscriptions');
Route::get($routeCustomizedNames['subscriptions'].'/{user}', 'SubscriptionController@show')->name('subscriptions.show');
Route::post($routeCustomizedNames['subscribe'].'/{sessionGroup}', 'SubscriptionController@subscribe')->name('course.subscribe');
Route::post($routeCustomizedNames['unsubscribe'].'/{subscription}', 'SubscriptionController@unsubscribe')->name('course.unsubscribe');
Route::get('privacy', 'PrivacyPolicyController@index')->name('privacy');
Route::get('account/export-excel', 'AccountController@exportExcel')->name('account.export.xlsx');
Route::get('account/export-csv', 'AccountController@exportCSV')->name('account.export.csv');
Route::get('account', 'AccountController@index')->name('account.index');
Route::delete('account/{user}', 'AccountController@destroy')->name('account.destroy')->withTrashed();
Route::get('account/{user}/edit', 'AccountController@edit')->name('account.edit');
Route::put('account/{user}/personal', 'AccountController@updatePersonal')->name('account.update.personal');
Route::put('account/{user}/password', 'AccountController@updatePassword')->name('account.update.password');
Route::put('account/{user}/email', 'AccountController@updateEmail')->name('account.update.email');
Route::put('account/{user}/admin', 'AccountController@updateAdmin')->name('account.update.admin');
Route::post('account/forget', 'AccountController@forget')->name('account.forget');
Route::get('sitemap.xml', 'SitemapIndexController@index');
Route::get('settings', 'SettingsController@edit')->name('settings.edit');
Route::put('settings/view', 'SettingsController@updateView')->name('settings.update.view');
Route::put('settings/privacy', 'SettingsController@updatePrivacyPolicy')->name('settings.update.privacy');
Route::put('settings/options', 'SettingsController@updateOptions')->name('settings.update.options');
