<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Ecommerce',
    description: 'UTP API'
)]
class ItemController extends Controller
{
    private $file = 'items.json';

    private function getItems()
    {
        if (!Storage::exists($this->file)) {
            Storage::put($this->file, json_encode([]));
        }

        return json_decode(Storage::get($this->file), true);
    }

    private function saveItems($data)
    {
        Storage::put($this->file, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }

    #[OA\Get(
        path: '/api/items',
        summary: 'Get list of items',
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
        ]
    )]
    public function index()
    {
        return response()->json($this->getItems(), 200);
    }

    #[OA\Post(
        path: '/api/items',
        summary: 'Store a new item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'harga'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Laptop Acer'),
                    new OA\Property(property: 'harga', type: 'integer', example: 15000000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Item created'),
        ]
    )]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $items = $this->getItems();

        $newItem = [
            'id' => uniqid(),
            'nama' => $request->nama,
            'harga' => $request->harga,
        ];

        $items[] = $newItem;
        $this->saveItems($items);

        return response()->json($newItem, 201);
    }

    #[OA\Get(
        path: '/api/items/{id}',
        summary: 'Get item information',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show($id)
    {
        $items = $this->getItems();
        $key = array_search($id, array_column($items, 'id'));

        if ($key === false) {
            return response()->json(['message' => "Item dengan ID {$id} tidak Ditemukan"], 404);
        }

        return response()->json($items[$key], 200);
    }

    #[OA\Put(
        path: '/api/items/{id}',
        summary: 'Update an existing item completely',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'harga'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Updated Laptop Acer'),
                    new OA\Property(property: 'harga', type: 'integer', example: 16000000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(Request $request, $id)
    {
        $items = $this->getItems();
        $key = array_search($id, array_column($items, 'id'));

        if ($key === false) {
            return response()->json(['message' => "Item dengan ID {$id} tidak Ditemukan"], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $items[$key]['nama'] = $request->nama;
        $items[$key]['harga'] = $request->harga;

        $this->saveItems($items);

        return response()->json($items[$key], 200);
    }

    #[OA\Patch(
        path: '/api/items/{id}',
        summary: 'Modify partial data of an item',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Patched Laptop Name'),
                    new OA\Property(property: 'harga', type: 'integer', example: 14000000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function modify(Request $request, $id)
    {
        $items = $this->getItems();
        $key = array_search($id, array_column($items, 'id'));

        if ($key === false) {
            return response()->json(['message' => "Item dengan ID {$id} tidak Ditemukan"], 404);
        }

        if ($request->has('nama')) {
            $items[$key]['nama'] = $request->nama;
        }
        if ($request->has('harga')) {
            $items[$key]['harga'] = $request->harga;
        }

        $this->saveItems($items);

        return response()->json($items[$key], 200);
    }

    #[OA\Delete(
        path: '/api/items/{id}',
        summary: 'Delete an item',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy($id)
    {
        $items = $this->getItems();
        $key = array_search($id, array_column($items, 'id'));

        if ($key === false) {
            return response()->json(['message' => "Item dengan ID {$id} tidak Ditemukan"], 404);
        }

        unset($items[$key]);
        $this->saveItems($items);

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }
}
