<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Events\StockMovementRecorded;
use App\Jobs\LogStockMovementJob;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $req): JsonResponse
    {
    	//\Log::info(auth()->user());
        $movement = DB::transaction(function () use ($req) {
        	//\Log::info($req->validated());
            $m = StockMovement::create($req->validated());

            // flush report cache *after* changes to the stock movements
            StockMovementRecorded::dispatch($m);

            // async log
            LogStockMovementJob::dispatch($m->id);

            return $m;
        });

        return response()->json($movement,201);
    }
}
