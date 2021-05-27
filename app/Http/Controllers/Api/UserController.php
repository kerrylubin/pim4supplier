<?php
/**
 * File UserController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Http\Resources\PermissionResource;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Http\Resources\SupplierUserResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\Permission;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Laravue\Models\SupplierProfileUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if ( !$currentUser->isAdmin() ) {

            $searchParams = $request->all();
            $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
            $role = Arr::get($searchParams, 'role', '');
            $keyword = Arr::get($searchParams, 'keyword', '' );

            $userQuery = DB::table('supplier_profile_users')
            ->select('supplier_profile_users.user_id','supplier_profile_users.name',
            'supplier_profile_users.email', 'supplier_profile_users.role')
            ->where('supplier_profile_users.id', '=', $currentUser->id)->get();

            if (!empty($role)) {
                $userQuery->whereHas('roles', function($q) use ($role) { $q->where('name', $role); });
            }

            if (!empty($keyword)) {
                $userQuery->where('name', 'LIKE', '%' . $keyword . '%');
                $userQuery->orWhere('email', 'LIKE', '%' . $keyword . '%');
            }

            // echo'user query: '.var_dump($userQuery);
            // return json_encode( $userQuery );
            return SupplierUserResource::collection($userQuery);
        }
        else{

            $searchParams = $request->all();
            $userQuery = User::query();
            $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
            $role = Arr::get($searchParams, 'role', '');
            $keyword = Arr::get($searchParams, 'keyword', '');

            if (!empty($role)) {
                $userQuery->whereHas('roles', function($q) use ($role) { $q->where('name', $role); });
            }

            if (!empty($keyword)) {
                $userQuery->where('name', 'LIKE', '%' . $keyword . '%');
                $userQuery->orWhere('email', 'LIKE', '%' . $keyword . '%');
            }

            return UserResource::collection($userQuery->paginate($limit));
        }

    }

    public function getSupUsers()
    {
        $currentUser = Auth::user();

        $userQuery = DB::table('supplier_profile_users')
        ->select('supplier_profile_users.user_id','supplier_profile_users.name',
        'supplier_profile_users.email', 'supplier_profile_users.role')
        ->where('supplier_profile_users.id', '=', $currentUser->id)->get();
        return SupplierUserResource::collection($userQuery);
    }


    /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user_id)
    {
        $currentUser = Auth::user();//gets current user
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {


            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
            ]);

            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            $supplier_user = array(
                'user_id'    => $user->id,
                'id' => $currentUser->id,
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
                'role' => $params['role']
            );

            if (!$currentUser->isAdmin()) {// if not admin ad to this table
                DB::table('supplier_profile_users')
                ->insert([$supplier_user]);
            }

            if($params['role'] == 'supplier'){

                $suppliers = array(
                    'id'    => $user->id,
                    'supplier_name' => $params['name'],
                );

                DB::table('suppliers')
                ->insert([$suppliers]);
            }


            return new UserResource($user);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $currentUser = Auth::user();
        if (!$currentUser->isAdmin()
            && $currentUser->id !== $user->id
            && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $email = $request->get('email');
            $found = User::where('email', $email)->first();
            if ($found && $found->id !== $user->id) {
                return response()->json(['error' => 'Email has been taken'], 403);
            }

            $user->name = $request->get('name');
            $user->email = $email;
            $user->save();
            return new UserResource($user);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function updatePermissions(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $permissionIds = $request->get('permissions', []);
        $rolePermissionIds = array_map(
            function($permission) {
                return $permission['id'];
            },

            $user->getPermissionsViaRoles()->toArray()
        );

        $newPermissionIds = array_diff($permissionIds, $rolePermissionIds);
        $permissions = Permission::allowed()->whereIn('id', $newPermissionIds)->get();
        $user->syncPermissions($permissions);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $user->delete();
            if (!$currentUser->isAdmin()) {// if not admin delete from this table
                DB::table('supplier_profile_users')
                ->where('id', $user->id)->delete();
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    /**
     * Get permissions from role
     *
     * @param User $user
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function permissions(User $user)
    {
        try {
            return new JsonResponse([
                'user' => PermissionResource::collection($user->getDirectPermissions()),
                'role' => PermissionResource::collection($user->getPermissionsViaRoles()),
            ]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
    }

    public function getCSVData()
    {
        $csvData = DB::table('csv_mapping')->get();
        echo'CSV DATA!!';
        return response()->json( $csvData );
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|email|unique:users' : 'required|email',
            'roles' => [
                'required',
                'array'
            ],
        ];
    }
}
