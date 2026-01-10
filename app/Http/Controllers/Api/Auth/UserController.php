<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class UserController extends Controller
{
    public function assignRoleAndPermission(Request $request,User $user)
    {
        try{
            $request->validate([
                'roles'         =>  'required',
                'roles.*'       =>  'exists:roles,name',
                'permissions'   =>  'required|array',
                'permissions.*' =>   'exists:permissions,name',
            ]);

            if($request->has('roles')){
                $user->syncRoles($request->roles);
            }

            if($request->has('permissions')){
                $user->syncPermissions($request->permissions);
            }

            return response()->json([
                'status'    =>  'success',
                'message'   => 'assign role and permission for' . $user->name,
                'data'      =>  [
                    'id'            =>  $user->id,
                    'name'          =>  $user->name,
                    'roles'         =>  $user->getRoleNames(),
                    'permissions'   =>  $user->getAllPermissions()->pluck('name'),
                ]
                ],200);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ],500);
        }
    }
}
