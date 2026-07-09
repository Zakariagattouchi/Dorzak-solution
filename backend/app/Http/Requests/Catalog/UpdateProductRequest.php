<?php

namespace App\Http\Requests\Catalog;

/** Same rules as create; the sku unique rule already ignores the {product} route id. */
class UpdateProductRequest extends StoreProductRequest {}
