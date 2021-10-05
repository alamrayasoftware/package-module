<?php

namespace __defaultNamespace__Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $listItems = Item::orderBy('sku')
            ->get();

        Log::info('get-list-items', ['user' => $request->user() ?? null]);
        return response()->json([
            'status' => 'success',
            'data' => $listItems
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $newData = new Item();
            $newData->name = $request->name ?? '-';
            $newData->sku = $request->sku ?? '-';
            $newData->save();

            DB::commit();
            Log::info('store-new-item', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $newData
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store-new-item', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $item = Item::find($id);

            Log::info('show-item', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $item
            ]);
        } catch (\Throwable $th) {
            Log::error('show-item', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Item::where('id', $id)
                ->firstOrFail();
            $data->sku = $request->sku ?? $data->sku;
            $data->name = $request->name ?? $data->name;
            $data->save();

            DB::commit();
            Log::info('update-item', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('update-item', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Item::where('id', $id)->delete();

            DB::commit();
            Log::info('delete-item', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            Log::error('delete-item', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }
}