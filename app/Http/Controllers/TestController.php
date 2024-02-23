<?php

	namespace App\Http\Controllers;
	
	class TestController extends Controller{
		public function index(){
			return [
				"code"=>0,
				"data"=>"We have a lot of data",
				"message"=>"It is great message to read."
			];
		}
	}