<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view',Course::class);
        $courses = Course::all();
        return response()->json([
            'status'    =>  'success',
            'message'   =>  'show all courses',
            'data'  => [
                'name'  =>  $courses->pluck('name'),
            ],
        ],200);
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
        try{
            $this->authorize('create',Course::class);
            $validated = $request->validate([
                'major_id'      =>  'required|exists:majors,id',
                'name'          =>  'required|string|max:255',
                'description'   =>  'nullable|string|max:255',
                'price'         =>  'nullable|numeric|min:0',
                'image'         =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $imagePath = null;
            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('courses','public');
            }

            $course = Course::create([
                'major_id'      =>  $validated['major_id'],
                'name'          =>  $validated['name'],
                'description'   =>  $validated['description'] ?? null,
                'price'         =>  $validated['price'] ?? 0,
                'image'         =>  $imagePath,
                'slug'          =>  Str::slug($validated['name']),
                'is_active'     => true,
                'created_by'    => auth()->id(),
            ]);

            return response()->json([
                'status'    =>  'success',
                'message'   =>  'course created successfully!',
                'data'      =>  $course->load('creator:id,name','major:id,name')
            ],201);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $e->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $this->authorize('view',$course);
        
        return response()->json([
            'status'    =>  'success',
            'message'   =>  'show '. $course->name . 'details',
            'data'      => $course,
        ],200);
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
    public function update(Request $request, Course $course)
    {
        $this->authorize('update',$course);

        $validated = $request->validate([
                'major_id'      =>  'required|exists:majors,id',
                'name'          =>  'sometimes|string|max:255|unique:courses,name,'.$course->id,
                'description'   =>  'nullable|string|max:255',
                'price'         =>  'nullable|numeric|min:0',
                'image'         =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if($request->hasFile('image')){
            if($course->image){
                Storage::disk('public')->delete($course->image);
            }
            $validated['image'] = $request->file('image')->store('courses','public');
        }

        if(isset($validated['name'])){
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['updated_by'] = auth()->id();
        $course->update($validated);
        return response()->json([
            'status'  => 'success',
            'message' => 'Course updated successfully',
            'data'    => $course
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete',$course);

        if($course->image){
            Storage::disk('public')->delete($course->image);
        }
        $course->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'course delete successfully',
        ],200);
    }
}
