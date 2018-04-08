<?php
/**
 * Created by PhpStorm.
 * User: gitzj
 * Date: 3/29/2018
 * Time: 9:29 PM
 */

namespace App\Services;

interface ProductService
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
    );
    public function read($id);
    public function readAll($limit = 0, $productName = 0);
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
    );
    public function delete($id);
}