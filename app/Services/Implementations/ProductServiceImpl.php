<?php
/**
 * Created by PhpStorm.
 * User: gitzj
 * Date: 4/8/2018
 * Time: 12:34 AM
 */

namespace App\Services\Implementations;

use DB;
use Config;
use Intervention\Image\Facades\Image;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductCategory;

use App\Services\ProductService;

class ProductServiceImpl implements ProductService
{

    public function create(
        $company_id,
        $product_type_id,
        $productCategories,
        $name,
        $image_filename,
        $short_code,
        $barcode,
        $productUnits,
        $minimal_in_stock,
        $description,
        $status,
        $remarks
    )
    {
        $imageName = '';

        if (!empty($image_filename)) {
            $imageName = time() . '.' . $image_filename->getClientOriginalExtension();
            $path = public_path('images') . '/' . $imageName;

            Image::make($image_filename->getRealPath())->resize(160, 160)->save($path);
        }

        DB::beginTransaction();
        try {
            $product = new Product;
            $product->company_id = $company_id;
            $product->product_type_id = $product_type_id;
            $product->name = $name;
            $product->image_filename = $imageName;
            $product->short_code = $short_code;
            $product->barcode = $barcode;
            $product->minimal_in_stock = $minimal_in_stock;
            $product->description = $description;
            $product->status = $status;
            $product->remarks = is_null($remarks) ? '':$remarks;

            $product->save();

            for ($i = 0; $i < count($productUnits); $i++) {
                $punit = new ProductUnit();
                $punit->unit_id = $productUnits[$i]['unit_id'];
                $punit->is_base = $productUnits[$i]['is_base'];
                $punit->conversion_value = $productUnits[$i]['conversion_value'];
                $punit->remarks = $productUnits[$i]['remarks'];

                $product->productUnits()->save($punit);
            }

            for ($j = 0; $j < count($productCategories); $j++) {
                $pcat = new ProductCategory();
                $pcat->company_id = $company_id;
                $pcat->code = $productCategories[$j]['cat_code'];
                $pcat->name = $productCategories[$j]['cat_name'];
                $pcat->description = $productCategories[$j]['cat_description'];
                $pcat->level = $productCategories[$j]['cat_level'];

                $product->productCategories()->save($pcat);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function read($productName = '')
    {
        $product = [];
        if ($productName != '') {
            $product = Product::with('productType', 'productCategories', 'productUnits.unit')->where('name', 'like', '%'.$productName.'%')
                ->paginate(Config::get('const.PAGINATION'));
        } else {
            $product = Product::with('productType', 'productCategories', 'productUnits.unit')->paginate(Config::get('const.PAGINATION'));
        }

        return $product;
    }

    public function update(
        $id,
        $company_id,
        $product_type_id,
        $productCategories,
        $name,
        $image_filename,
        $short_code,
        $barcode,
        $productUnits,
        $minimal_in_stock,
        $description,
        $status,
        $remarks
    )
    {
        DB::beginTransaction();

        try {
            $product = Product::find($id);

            if (!empty($product->image_filename)) {
                if (!empty($image_filename)) {
                    $imageName = time() . '.' . $image_filename->getClientOriginalExtension();
                    $path = public_path('images') . '/' . $imageName;

                    Image::make($image_filename->getRealPath())->resize(160, 160)->save($path);
                } else {
                    $imageName = $image_filename;
                }
            } else {
                if (!empty($image_filename)) {
                    $imageName = time() . '.' . $image_filename->getClientOriginalExtension();
                    $path = public_path('images') . '/' . $imageName;

                    Image::make($image_filename->getRealPath())->resize(160, 160)->save($path);
                } else {
                    $imageName = '';
                }
            }

            $product->productUnits->each(function($pu) { $pu->delete(); });

            $pu = array();
            for ($i = 0; $i < count($productUnits); $i++) {
                $punit = new ProductUnit();
                $punit->unit_id = $productUnits[$i]['unit_id'];
                $punit->is_base = $productUnits[$i]['is_base'];
                $punit->conversion_value = $productUnits[$i]['conversion_value'];
                $punit->remarks = $productUnits[$i]['remarks'];

                array_push($pu, $punit);
            }

            $product->productUnits()->saveMany($pu);

            $product->productCategories->each(function($pc) { $pc->delete(); });

            $pclist = array();
            for ($j = 0; $j  < count($productCategories); $j++) {
                $pcat = new ProductCategory();
                $pcat->company_id = $company_id;
                $pcat->code = $productCategories[$j]['cat_code'];
                $pcat->name = $productCategories[$j]['cat_name'];
                $pcat->description = $productCategories[$j]['cat_description'];
                $pcat->level = $productCategories[$j]['cat_level'];

                array_push($pclist, $pcat);
            }

            $product->productCategories()->saveMany($pclist);

            $product->update([
                'product_type_id' => $product_type_id,
                'name' => $name,
                'short_code' => $short_code,
                'description' => $description,
                'image_path' => $imageName,
                'status' => $status,
                'remarks' => $remarks,
                'barcode' => $barcode,
                'minimal_in_stock' => $minimal_in_stock,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        $product = Product::find($id);

        $product->delete();
    }
}