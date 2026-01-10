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

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->id() !== $course->created_by){
                return response()->json([
                    'status'    =>  'error',
                    'message'   =>  'Unauthorized. You do not own this course.'
                ],403);
        } 

        $lessons = Lesson::with('course:id,name,description')
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

            if(auth()->id() !== $course->created_by){
                return response()->json([
                    'status'    =>  'error',
                    'message'   =>  'Unauthorized. You do not own this course.'
                ],403);
            } 

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
            'statur'    => 'success',
            'message'   => 'Lesson Update Successfully',
            'data'      =>  $lesson,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        if(auth()->id() !== $lesson->course->created_by){
            return response()->json([
                'status'    =>  'error',
                'message'   =>  'Unauthorized. You do not own this course.'
            ],403);
        } 

        $lesson->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Lesson deleted successfully'
        ]);

    }
}
