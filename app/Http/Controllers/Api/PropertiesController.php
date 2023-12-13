<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Resources\PropertyCollection;

class PropertiesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/properties",
     *     tags={"Properties"},
     *     summary="Get list of property",
     *     description="Returns list of property",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="properties",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Property",
     *                  ),
     *              )
     *          ),
     *     )
     * )
     */

    public function index()
    {
        return new PropertyCollection(Property::all());
    }
}
