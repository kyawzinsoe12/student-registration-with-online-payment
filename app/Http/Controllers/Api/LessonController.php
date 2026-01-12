<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ResponseSequence;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view',Lesson::class);
        $lessons = Lesson::with('course:id,name,created_by')
                            ->select('id','course_id','title','slug')
                            ->get();

        return response()->json([
                'status' => 'success',
                'message' => 'Show all lessons',
                'data' => $lessons, 
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
    public function store(Request $request,Course $course)
    {
        try{
            $this->authorize('create',Lesson::class);

            $validated = $request->validate([
                'course_id'     =>  'required|exists:courses,id',
                'title'         =>  'required|string|max:255',
                'content'       =>  'nullable|string|max:255',
                'order'         =>  'nullable|integer|min:1',
            ]);

            $lesson = Lesson::create([
                'course_id' =>  $course->id,
                'title'     =>  $validated['title'],
                'slug'      =>  Str::slug($validated['title']),
                'content'   =>  $validated['content'],
                'order'     =>  $validated['order'] ?? 1,
                'is_free'   =>  $request->is_free ?? false,
                'created_by'=>  auth()->id(),
                'updated_by'=>  auth()->id(),
            ]);

            return response()->json([
                'status'    =>  'success',
                'message'   =>  'Lesson create Successfully',
                'data'      =>  $lesson->load('course:id,name'),
            ],201);

        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'status'    => 'error',
                'message'   =>  $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                'status'    => 'error',
                'message'   =>  $e->getMessage(),
            ],422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $this->authorize('update',$lesson);
        $validated = $request->validate([
            'title'         =>  'sometimes|required|string|max:255',
            'content'       =>  'nullable|string|max:255',
            'order'         =>  'nullable|integer|min:1',
        ]);

        if(isset($validated['title'])){
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_free'] = $request->is_free ?? false;
        $validated['updated_by'] = auth()->id();

        $lesson->update($validated);
        return response()->json([
            'status'    => 'success',
            'message'   => 'Lesson Update Successfully',
            'data'      =>  $lesson,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('delete',$lesson);

        $lesson->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Lesson deleted successfully'
        ]);

    }
}
