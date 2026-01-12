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

class MajorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('view', Major::class);
        $majors = Major::select('id','name','description')->get();
        return response()->json([
            'status'    => 'success',
            'message'   => 'Show all Majors',
            'data'      =>  $majors,
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
            $this->authorize('create', Major::class);
            $validated = $request->validate([
                'name'          =>  'required|string|max:255|unique:majors,name',
                'description'   =>  'nullable|string|max:255',
                'image'         =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $imagePath = null;

            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('majors','public');
            }

            $majors = Major::create([
                'name'  =>  $validated['name'],
                'slug'  =>  Str::slug($validated['name']),
                'description'   =>  $validated['description'] ?? null,
                'image'         =>  $imagePath,
                'is_active'     =>  true,
                'created_by'    => auth()->id(),
            ]);

            return response()->json([
                'status'    =>  'success',
                'message'   =>  'Major Creted Successfully',
                'data'      => [
                    'name'          =>  $majors->name,
                    'description'   =>  $majors->description,
                ]
            ],203);

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
        $this->authorize('update',$major);
        $validated = $request->validate([
            'name'          =>  'sometimes|string|max:255|unique:majors,name,'.$major->id,
            'description'   =>  'nullable|string|max:255',
            'image'         =>  'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if($request->hasFile('image')){
            if($major->image){
                Storage::disk('public')->delete($major->image);
            }
            $validated['image'] = $request->file('image')->store('majors','public');
        }
        $validated['updated_by'] = auth()->id();

        if(isset($validated['name'])){
            $validated['slug']  = Str::slug($validated['name']);
        }

        $major->update($validated);

        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Major Updated Successfully!',
            'data'      => $major
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major)
    {
        $this->authorize('delete',$major);
        if($major->image){
            Storage::disk('public')->delete($major->image);
        }
        $major->delete();
            
        return response()->json([
            'status'    => 'success',
            'message'   =>  'Major deleted Successfully!',
        ],200);
    }
}
