<?php

namespace App\Http\Requests\Catalog;

/** Same rules as create; the unique rule already ignores the {category} route id. */
class UpdateCategoryRequest extends StoreCategoryRequest {}
