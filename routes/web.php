<?php

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use ChrisLorando\LaravelAccurate\OAuth\CallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('accurate/connect', function () {
    return redirect(Accurate::authorizationUrl());
})
->name('accurate.connect');

Route::get('accurate/callback', CallbackController::class)->name('accurate.callback');

Route::get('/accurate/databases', function () {
    return Accurate::connection('default')->databases();
});


Route::get('/accurate/item-search', function (\Illuminate\Http\Request $request) {
    $query = Accurate::connection('default')
        ->openDatabase('2759883')
        ->items()
        ->query()
        ->select('id', 'no', 'name', 'unit1Name', 'unitPrice', "unit1", 'itemTypeName');

    if ($request->has('q')) {
        $query->where('keywords', 'like', $request->query('q'));
    }

    if ($request->has('sort')) {
        $query->orderBy($request->query('sort'), $request->query('direction', 'asc'));
    } else {
        $query->orderBy('name');
    }

    return response()->json(
        $query
            ->limit($request->query('limit', 10))
            ->page($request->query('page', 1))
            ->get()
    );
});

Route::get('/accurate/item-detail/{id}', function ($id) {
    $result = Accurate::connection('default')
        ->openDatabase('2759883')
        ->items()
        ->detail($id);

    return response()->json($result);
});