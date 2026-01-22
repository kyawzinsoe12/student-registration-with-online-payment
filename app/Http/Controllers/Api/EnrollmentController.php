<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enrollments = Enrollment::with('user:id,name','course:id,name,price')
                                    ->select('id','user_id','course_id','status')
                                    ->get();
        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Show all Enroallment',
            'data'      =>  $enrollments,
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
        $validated = $request->validate([
            'major_id'  =>  'required|exists:majors,id',
            'course_id' =>  'required|exists:courses,id',
        ]);
        $user = auth()->id();
        $alreadyEnrolled = Enrollment::where('user_id',$user)
                        ->where('course_id',$validated['course_id'])
                        ->exists();
        if($alreadyEnrolled){
            return response()->json([
                'status'    =>  'error',
                'message'   =>  'You are already enrolled for this courese',
            ],409);
        }

        $enrollment = Enrollment::create([
            'user_id'   => auth()->id(),
            'major_id'  =>  $validated['major_id'],
            'course_id' =>  $validated['course_id'],
            'status'    =>  'pending',
        ]);

        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Enrolled Successfully!',
            'data'      =>  $enrollment,
            'price'     =>  $enrollment->course->price,
        ],201);
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
    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending', 'paid', 'cancelled',
        ]);

        if($validated['status']){
            $validated['completed_at'] = now();
        }

        $enrollment->update($validated);

        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Update Enroll Successfully',
            'data'      =>  $enrollment,
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Enrollment record deleted permanently.'
        ], 200);
    }

    public function cancel(Enrollment $enrollment)
    {
        $enrollment->update([
            'status'    =>  'cancelled',
        ]);

        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Cancel successfully',
            'data'      =>  [
                'userInfo' => $enrollment->load('user:id,name'),
            ]
        ],203);
    }


}
