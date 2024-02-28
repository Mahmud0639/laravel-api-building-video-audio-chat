<?php

namespace App\Http\Controllers\Api;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller{
	public function login(Request $request){
		$validator = Validator::make($request->all(),[
			'avatar'=>'required',
			'name'=>'required',
			'type'=>'required',
			'open_id'=>'required',//it the firebase user id
			'email'=>'max:50',
			'phone'=>'max:30'
		
		]);
		
		if($validator->fails()){
			return [
				'code'=>-1,
				'data'=>"No valid data",
				'message'=>$validator->errors()->first()//first object of the error
			];
			
		}
		
		try{
			
			//to do commenting we use ctrl+k and uncommenting use ctrl+shift+k 
		// else{
			// return [
				// 'code'=>1,
				// 'data'=>"valid data",
				// 'message'=>"Success"
			// ];
		// }
		
		//if not fails then it means we are now in the else block so we need to put all the 
		//validated data in the database using map
		
		//with the help of the validated() method we will be able to get
		//all the info from the app
		$validated = $validator->validated();
		//now we need to do a query based on the type and open_id to get all the 
		//field data from the users table
		$map = [];
		$map['type'] = $validated['type'];
		$map['open_id'] = $validated['open_id'];
		
		$results = DB::table('users')->select('avatar','name','description','type','token','access_token')->where($map)->first();
		
		if(empty($results)){
			//it will create a random token and that would be used 
			//to communicate server to app and app to server 
			$validated['token'] = md5(uniqid().rand(10000,99999));
			$validated['created_at'] = Carbon::now();
			//access_token is only for server side not with the app
			$validated['access_token'] = md5(uniqid().rand(1000000,9999999));
			$validated['expire_date'] = Carbon::now()->addDays(30);
			
			//and now just put all the $validated data into the users table
			$user_id = DB::table('users')->insertGetId($validated);
			
			//after putting all the data we can now get all the data from the users table
			$user_result = DB::table('users')->select('avatar','name','description',
			'type','token','access_token','online')->where('id','=',$user_id)->first();
			return [
				"code"=>0,
				"data"=>$user_result,
				'message'=>'User has been created successfully.'
			];
		}else{
			//when the same user with the same user info try to log in then everytime we need to
			//update the access_token and expire_data
			$access_token = md5(uniqid().rand(1000000,9999999));
			$expire_date = Carbon::now()->addDays(30);
			DB::table('users')->where($map)->update(
				[
					"access_token"=>$access_token,
					"expire_date"=>$expire_date
				]
			);
			
			$results->access_token = $access_token;
			return [
				'code'=>0,
				'data'=>$results,
				'message'=>'User info updated.'
			];
		}
			
		}catch(Exception $e){
			return ['code'=>-1,'data'=>'No data available','message'=>(string)$e];
		}
		
		
	}
	public function contact(Request $request){
		
		$token = $request->user_token;
		$res = DB::table('users')->select(
			'avatar',
			'description',
			'online',
			'token',
			'name'
		)->where('token','!=',$token)->get();//here we have used '!=' because we want to show the chat list everyone without the login user(me)
		
		// $res = DB::table('users')->select(
			// 'avatar',
			// 'description',
			// 'online',
			// 'token'
		// )->get();
		
		return [
			'code'=>0,
			'data'=>$res,
			'message'=>'got all the user info.'
		];
		
	}
}