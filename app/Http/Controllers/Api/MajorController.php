<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Major;
use Nette\Utils\Image;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\MajorResource;

class MajorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;
    public function index()
    {
        // $this->authorize('view', Major::class);
        $majors = Major::select('id', 'name', 'description')->get();

        $data = MajorResource::collection($majors);

        return $this->success('Show all Major', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Major::class);
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:majors,name',
                'description' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('majors', 'public');
            }

            $majors = Major::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            return $this->success('Major created successfully', new MajorResource($majors), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Major $major)
    {
        $this->authorize('update', $major);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:majors,name,' . $major->id,
            'description' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($major->image) {
                Storage::disk('public')->delete($major->image);
            }
            $validated['image'] = $request->file('image')->store('majors', 'public');
        }
        $validated['updated_by'] = auth()->id();

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $major->update($validated);

        return $this->success('Major Updated Successfully', new MajorResource($major));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major)
    {
        $this->authorize('delete', $major);
        if ($major->image) {
            Storage::disk('public')->delete($major->image);
        }
        $major->delete();

        return $this->success('Major deleted successfully');
    }
}
