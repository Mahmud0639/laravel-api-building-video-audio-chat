<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class CheckUser{

	public function handle($request, Closure $next){
		
		$Authorization = $request->header('Authorization');
		
		// if(empty($Authorization)){
			// return response()->json([
				// "code"=>401,//401 means permission denied
				// "message"=>'Authorization failed.'	
			// ],401);
		// }
		
		//we can also like this
		if(empty($Authorization)){
			return response([
				"code"=>401,//401 means permission denied
				"message"=>'Authorization failed.'	
			],401);
		}
		
		
		//$Authorization = "hello world"
		//ltrim($Authorization,'hello')=>'world'
	
		//ltrim means left trim(trim means remove white spaces)
		
		$access_token = trim(ltrim($Authorization,'Bearer'));
		
		$res_user = DB::table('users')->where('access_token',$access_token)
		->select('id','avatar','name','token','type','access_token','expire_date')
		->first();
		
		if(empty($res_user)){
			return response([
				'code'=>401,//401 means permission denied
				'message'=>'User does not exits'
			],401);
		}
		
		$expire_date = $res_user->expire_date;
		
		if(empty($expire_date)){
			return response([
				'code'=>401,
				'message'=>'You must login again.'
			],401);
		}
		
		//that means user has expired because we set expire date as 30 days
		//if any user try to log in after this 30 days then we can send him
		//this message 
		if($expire_date<Carbon::now()){
			return response([
				'code'=>401,
				'message'=>'Your token has expired. You must login.'
			],401);
		}
		//if the token has not expired yet but close to expiration.Then we can add 
		//some days with the expire_date so that user not to log in again and again
		$addTime = Carbon::now()->addDays(5);
		if($expire_date<$addTime){
			$add_expire_date = Carbon::now()->addDays(30);
			//now we need to update the expire date field
			DB::table('users')->where('access_token',$access_token)->update('expire_date',$add_expire_date);
		}
		
		$request->user_id = $res_user->id;
		$request->user_type = $res_user->type;
		$request->user_name = $res_user->name;
		$request->user_avatar = $res_user->avatar;
		$request->user_token = $res_user->token;
		
		//if the if block is not working then we will return $next($request);
		return $next($request);
	}

}