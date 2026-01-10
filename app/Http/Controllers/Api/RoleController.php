<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Exception;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        try{
            // $roles = Role::with('permissions')->get();
            // $roles = Role::pluck('name');
            // $permissions = Permission::pluck('name');
            $roles = Role::with('permissions:name')->get()
                ->map(function ($role){
                    return [
                        'role' => $role->name,
                        'permissions' => $role->permissions->pluck('name'),
                    ];
                });
            return response()->json([
                'status' => 'success',
                'message' => 'Show all roles',
                'roles' => $roles, 
            ],200);   
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status" => "error",
                "message" => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                "status" => "error",
                "message" =>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
             $request->validate([
                'name' => 'required|unique:roles,name',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name',
            ]);

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            $role->syncPermissions($request->permissions);

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully',
                // 'role' => $role->load('permissions'),
                'role_name'=>$role->name,
                'permissions'=>$role->permissions->pluck('name'),
            ], 201);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status" => "error",
                "message" => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                "status"=>"error",
                "message"=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name',
            ]);

            $role->update([
                'name' => $validated['name'],
            ]);

            $role->syncPermissions($validated['permissions']);

            return response()->json([
                "status" => "success",
                "message" => "roles updated successfully!",
                "role"  => $role->name,
                'permissions'=>$role->permissions->pluck('name'),
            ],200);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status"    =>  "error",
                "message"   => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                "status"    =>  "error",
                "message"   => $e->getMessage(),
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try{
            $protectedRole = ['admin'];   
            if(in_array($role->name,$protectedRole)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'This default role cannot be deleted.'
                ],403);
            }

            $role->syncPermissions([]);
            $role->delete();

            return response()->json([
                    'status' => 'success',
                    'message' => 'role delete successfully',
                ],200);

        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status" => "error",
                "message" => $e->errors(),
            ]);
        }catch(Exception $e){
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }
}
