<?php

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use ChrisLorando\LaravelAccurate\OAuth\CallbackController;
use Illuminate\Http\Request;
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

Route::get('/accurate/item-search', function (Request $request) {
    $query = Accurate::connection('default')
        ->openDatabase('2759883')
        ->items()
        ->query()
        ->select('id', 'no', 'name', 'unit1Name', 'unitPrice', 'unit1', 'itemTypeName');

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

Route::prefix('accurate/demo/warehouses')->group(function () {

    // GET /accurate/demo/warehouses — List all warehouses
    Route::get('/', function () {
        return Accurate::warehouses()->list([
            // 'fields' => 'id,warehouseName,warehouseCode,suspended',
            'sp.pageSize' => '20',
        ]);
    });

    // GET /accurate/demo/warehouses/1 — Get warehouse detail
    Route::get('/{id}', function ($id) {
        return Accurate::warehouses()->detail($id);
    });

    // POST /accurate/demo/warehouses — Save (create/update) a warehouse
    Route::post('/', function () {
        return Accurate::warehouses()->save(request()->all());
    });

    // DELETE /accurate/demo/warehouses/{id} — Delete a warehouse
    // Route::delete('/{id}', function (string $id) {
    //     return Accurate::warehouses()->delete($id);
    // })->where('id', '[0-9]+');

    // GET /accurate/demo/warehouses/search/{keyword} — Query builder demo
    Route::get('/search/{keyword}', function (string $keyword) {
        return Accurate::warehouses()->query()
            ->select('id', 'warehouseName', 'warehouseCode')
            ->where('keywords', 'like', $keyword)
            ->limit(20)
            ->get();
    });
});
