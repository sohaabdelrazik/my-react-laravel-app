<?php

namespace App\Http\Controllers;


use Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
class UsersController extends Controller
{
    public function register(Request $request){

        $validator=Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|max:12|min:8',
            'gender'=>'required|in:Female,Male',
            'age'=>'required|integer',
           'rate'=>'numeric',
           'mobile_number'=>'required|string|min:11|max:11'

        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);}
         
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'age'=>$request->age,
            'gender'=>$request->gender,
            'rate'=>$request->rate,
            'password'=>Hash::make($request->password),
            'mobile_number'=>$request->mobile_number

        ]);
        $token=JWTAuth::fromUser($user);
        return response()->json(['message'=>'User Registered','user'=>$user,'token'=>$token],201);
    
        }  
    public function login(Request $request){

            $request->validate([
                'email'=>'required|email',
                'password'=>'required|max:12|min:8',
            ]);
            $user=User::where('email',$request->email)->first();
            if(!$user){
                return response()->json(['errors'=>'invalid email'],401);}
             elseif(!Hash::check($request->password,$user->password)){
                return response()->json(['errors'=>'incorrect password'],401);
             }
            $token=JWTAuth::fromUser($user);
            return response()->json(['message'=>'User logged in ',
            'user'=>$user->makeHidden(['password']),'token'=>$token],200);
        
            }  
     public function dashboard(Request $request){

                try{
                    $token=$request->bearerToken();
                    if(!$token){
                        return response()->json(['error'=>'No token provided'],401);
                    }

                    $user = auth('user')->user();
                    if (!$user) {
                        return response()->json(['errors' => 'Invalid token or Expired'], 401);
                    }
                }
                catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                    // Token is invalid
                    return response()->json(['errors' => 'Invalid token'], 401);
            
                } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    // Token is expired
                    return response()->json(['errors' => 'Expired token'], 401);
            
                } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                    // Catch other JWT-related exceptions
                    return response()->json(['errors' => 'Token error: ' . $e->getMessage()], 500);
                }
                return response()->json(['message'=>'welcome to your dashboard',
                'user'=>$user]);
            
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
        public function profile( $name){
            $userAuth=auth('user')->user();
            $charity=auth('charity')->user();
             if (!$userAuth&&!$charity){
                return response()->json(['error'=>'unauthorized'],401);
             }
             $user=User::where('name',$name)->first();
             if(!$user){
                return response()->json(['error'=>'user not found'],404);
             }
             return response()->json(['message'=>'User Profile',
             'user'=>$user->makeHidden(['password','id'])]);
        }
        public function topRated(){
            $user=auth('user')->user();
            $charity=auth('charity')->user();
            if(!$charity&&!$user){
                return response()->json(['error'=>'unauthorized'],403);
            }
            $topRaters=User::orderByDesc('rate')
            ->take(5)->select('name','rate')->get();
            if(!$topRaters){
                return response()->json(['message'=>'no users yet'],404);
            }
            return response()->json(['top users'=>$topRaters],201);
        }
}
