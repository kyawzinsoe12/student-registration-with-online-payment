<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enrollments = Enrollment::with('user:id,name', 'course:id,name,price')
            ->select('id', 'user_id', 'course_id', 'status')
            ->get();
        $data = EnrollmentResource::collection($enrollments);
        return $this->success('Show All Enrollments.', $data);
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
            'major_id' => 'required|exists:majors,id',
            'course_id' => 'required|exists:courses,id',
        ]);
        $user = auth()->id();
        $alreadyEnrolled = Enrollment::where('user_id', $user)
            ->where('course_id', $validated['course_id'])
            ->exists();
        if ($alreadyEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are already enrolled for this courese',
            ], 409);
        }

        $enrollment = Enrollment::create([
            'user_id' => auth()->id(),
            'major_id' => $validated['major_id'],
            'course_id' => $validated['course_id'],
            'status' => 'pending',
        ]);

        return $this->success('Enrolled Created Successfully.', new EnrollmentResource($enrollment), 201);
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
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        if ($validated['status'] === 'paid') {
            $validated['completed_at'] = now();
        } else {
            $validated['completed_at'] = null;
        }

        $enrollment->update($validated);

        return $this->success('Enrolled Updated Successfully.', new EnrollmentResource($enrollment));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return $this->success('Enrolled Deleted Successfully.');
    }

    public function cancel(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'cancelled',
        ]);

        return $this->success('Enrolled Cancel Successfully.', new EnrollmentResource($enrollment));
    }


}
