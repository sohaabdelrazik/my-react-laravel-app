<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use App\Models\Charity;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
class CharitiesController extends Controller
{  public function register(Request $request){

        $validator=Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'address'=>'required|max:1000',
            'description'=>'required|max:1600',
            'specialty'=>'required|max:100',
            'password'=>'required|string|max:12|min:8',
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);}
         
        $charity=Charity::create([
            'name'=>$request->name,
            'address'=>$request->address,
            'description'=>$request->description,
            'specialty'=>$request->specialty,
            'password'=>Hash::make($request->password),
        ]);
        $token=JWTAuth::fromUser($charity);
        return response()->json(['message'=>'Charity Registered successfully','charity'=>$charity,'token'=>$token],201);
    
        }  
    public function login(Request $request){

            $request->validate([
                'name'=>'required',
                'password'=>'required|max:12|min:8',
            ]);
            $charity=Charity::where('name',$request->name)->first();
            if(!$charity){
                return response()->json(['errors'=>'invalid Charity_name'],401);}
             elseif(!Hash::check($request->password,$charity->password)){
                return response()->json(['errors'=>'incorrect password'],401);
             }
            $token=JWTAuth::fromUser($charity);
            return response()->json(['message'=>'User logged in ',
            'charity'=>$charity->makeHidden(['password']),'token'=>$token],200);
        
            }  
            public function dashboard(Request $request)
            {
                try {
                    // Ensure the token is passed correctly
                    $token = $request->bearerToken();
                    if (!$token) {
                        return response()->json(['errors' => 'No token provided'], 401);
                    }
            
                    // Get the authenticated charity using the 'charity' guard
                    $charity = auth('charity')->user();
            
                    if (!$charity) {
                        return response()->json(['errors' => 'Invalid token or expired token'], 401);
                    }
            
                } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                    // Catch invalid token
                    return response()->json(['errors' => 'Invalid token'], 401);
                } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    // Catch expired token
                    return response()->json(['errors' => 'Expired token'], 401);
                } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                    // Catch general JWT exceptions
                    return response()->json(['errors' => 'Token error: ' . $e->getMessage()], 500);
                }
            
                // If everything is good, return the charity data
                return response()->json([
                    'message' => 'Welcome to your dashboard',
                    'charity' => $charity
                ]);
            }
            
                
        public function logout(Request $request){

                    try{
                        $token=JWTAuth::getToken();
                        if(!$token){
                            return response()->json(['errors'=>'token not provided'],401);
                        }
                       JWTAuth::invalidate($token);
                       return response()->json(['message'=>'logged out successfully'],201) ;
                    }
                    catch(\Tymon\JWTAuth\Exceptions\JWTException $e){
                        return response()->json(['errors'=>'failed to logout'],401);}

                
                    }  
}

