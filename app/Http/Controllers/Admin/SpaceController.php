<?php

namespace App\Http\Controllers\Admin;

use App\Models\Space;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SpaceResource;
use App\Http\Requests\Admin\Space\StoreRequest;
use App\Http\Requests\Admin\Space\UpdateRequest;
use App\Traits\FileUploads;

class SpaceController extends Controller
{
    use FileUploads;

    /**
     * List all Space
     */
    public function index()
    {
        $spaces = Space::latest()->paginate();
        return SpaceResource::collection($spaces);
    }

    /**
     * Store Space
     */
    public function store(StoreRequest $request)
    {
        $directory = 'spaces/images';

        $request->validated($request->all());

        $imagePath = $this->handleImageUpload($request, $directory);

        $space = Space::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'rate_per_unit' => $request->rate_per_unit,
            'capacity' => $request->capacity,
            'measurement' => $request->measurement,
            'availability' => true,
            'image' => $imagePath,
            'status' => 'active'
        ]);

        return (new SpaceResource($space))->additional([
            'message' => "Space Created Successfully",
        ]);
    }

    /**
     * show Space logic
     */
    public function show(Space $space)
    {
        return new SpaceResource($space);
    }

    /**
     * Update Space logic
     */
    public function update(UpdateRequest $request, Space $space)
    {
        $directory = 'spaces/images';

        //Check for new file
        if($request->hasFile('image')) {

            $this->deleteImage($space->image);

            $imagePath = $this->handleImageUpload($request, $directory);
            $space->image = $imagePath;
        }

        $space->update($request->validated());
        return (new SpaceResource($space))->additional([
            'message' => 'Space Updated Successfully'
        ]);

    }

    /**
     * Delete Space logic
     */
    public function destroy(Space $space)
    {
        $this->deleteImage($space->image);
        $space->delete();
        return response()->json(['message' => 'Space deleted successfully']);
    }

}
