<?php
/**
 * Created by PhpStorm.
 * User: gitzj
 * Date: 4/8/2018
 * Time: 3:33 AM
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Services\ProductTypeService;

class ProductTypeController extends Controller
{
    private $productTypeService;

    public function __construct(ProductTypeService $productTypeService)
    {
        $this->middleware('auth');
        $this->productTypeService = $productTypeService;
    }

    public function index()
    {
        return view('product_type.index');
    }

    public function read()
    {
        return $this->productTypeService->read();
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required',
        ])->validate();

        $this->productTypeService->create(
            Auth::user()->company->id,
            $request['name'],
            $request['short_code'],
            $request['description'],
            $request['status']
        );

        return response()->json();
    }

    public function update($id, Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required',
        ])->validate();

        $this->productTypeService->update(
            $id,
            Auth::user()->company->id,
            $request['name'],
            $request['short_code'],
            $request['description'],
            $request['status']
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->productTypeService->delete($id);

        return response()->json();
    }
}