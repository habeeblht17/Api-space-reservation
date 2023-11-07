<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PlanResource;
use App\Http\Requests\Admin\Plan\StoreRequest;
use App\Http\Requests\Admin\Plan\UpdateRequest;

class PlanController extends Controller
{
    /**
     * List all Plan
     */
    public function index()
    {
        $plans = Plan::latest()->paginate();
        return PlanResource::collection($plans);
    }

    /**
     * Store Plan
     */
    public function store(StoreRequest $request)
    {
        $request->validated($request->all());

        $plan = Plan::create([
            'title' => $request->title,
            'price' => $request->price,
            'interval' => $request->interval,
        ]);

        return (new PlanResource($plan))->additional([
            'message' => "Plan Created Successfully",
        ]);
    }

    /**
     * show Plan logic
     */
    public function show(Plan $plan)
    {
        return new PlanResource($plan);
    }

    /**
     * Update Plan logic
     */
    public function update(UpdateRequest $request, Plan $plan)
    {
        $plan->update($request->validated());
        return (new PlanResource($plan))->additional([
            'message' => 'Plan Updated Successfully'
        ]);

    }

    /**
     * Delete Plan logic
     */
    public function destroy(Plan $plan)
    {

        $plan->delete();
        return response()->json(['message' => 'Plan deleted successfully']);
    }
}
