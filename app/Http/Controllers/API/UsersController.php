<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;

use App\User;

use Validator;

class UsersController extends Controller
{
    public function createUser(Request $request){
    	$input = $request->all();
    	$rules = [
    		'name'		=> 'required|max:190',
    		'email'		=> 'required|email|unique:users,email',
    		'password'	=> 'required|min:8|max:190'
    	];
    	$validation = Validator::make($input, $rules);
    	if($validation->fails()){
    		$error = $validation->errors()->first();
    		return response()->json(['message'=> $error], 400);
    	}

    	$token = str_random(60);
        while(User::where('api_token',$token)->count() > 0){
            $token = str_random(60);
        }
    	$data = [
    		'name'		=> $input['name'],
    		'email'		=> $input['email'],
    		'password'	=> bcrypt($input['password']),
    		'api_token'	=> $token
    	];
    	$user = User::create($data);
    	if($user){
    		return new UsersResource($user);
    	}
    }

    public function getUsers(){
    	return UsersResource::collection(User::all());
    }

    public function getUser($id){
    	$user = User::find($id);
    	if(empty($user)){
    		return response()->json(['message'=> 'user not found'], 404);
    	}
    	return new UsersResource($user);
    }

    public function updateUser(Request $request,$id){
    	$user = User::find($id);
    	if(empty($user)){
    		return response()->json(['message'=> 'user not found'], 404);
    	}

    	$input = $request->all();
    	$rules = [
    		'name'	=> 'required|max:190',
    		'email'	=> 'required|email|unique:users,email,'.$id,
    	];
    	if($request->has('password')){
    		$rules['password'] = 'required|min:8|max:190';
    	}
    	$validation = Validator::make($input, $rules);
    	if($validation->fails()){
    		$error = $validation->errors()->first();
    		return response()->json(['message'=> $error], 400);
    	}
    	$data = [
    		'name'		=> $input['name'],
    		'email'		=> $input['email'],
    	];
    	if($request->has('password')){
    		$date['password'] = $input['password'];
    	}
    	$user = $user->update($data);
    	if($user){
    		return new UsersResource(User::find($id));
    	}

    }
}
