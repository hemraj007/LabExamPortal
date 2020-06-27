<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

class UserController extends Controller 
{
public $successStatus = 200;
/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request){ 

        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required',
            'isAdmin' => 'required'
        ]);

        if(!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid Credentials'],401);
        }
 
        //update status to login
        DB::table('users')
            ->where('email',$loginData['email'])
            ->update([
                'isLogin' => 1
            ]);

        $success['token'] =  auth()->user()->createToken('authToken')-> accessToken;
        $success['user'] = auth()->user();
        return response()->json(['message' => 'Logged In!', 'success' => $success],$this-> successStatus);

        
    }

    /**
     * Logout api
     *  @return \Illuminate\Http\Response 
     */

     public function logout()
     {
         if(Auth::check()) {
             $accessToken = Auth::user()->token(); 
             //update isLogin to 0
             DB::table('users')
                ->where('id',$accessToken->user_id)
                ->update([
                    'isLogin' => 0
                ]);

            //Delete token
             DB::table('oauth_refresh_tokens')
                ->where('access_token_id',$accessToken->id)
                ->update([
                    'revoked' => true
                ]);

             $accessToken->revoke();
             return response()->json(['message'=>'Successfully logged out!!']);
         }
        
     }
/** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        $validatedData = $request->validate([
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'isAdmin' => 'required',
            'username' => 'required',
            'password' => 'required|confirmed',
            // 'isLogin' => 'required'
        ]);
        
        $validatedData['password'] = bcrypt($request->password);

        $user = User::create($validatedData);

        $success['token'] =  $user->createToken('authToken')-> accessToken;
        $success['user'] = $user;
        return response()->json(['message' => 'Registered Successfully', 'success' => $success],$this-> successStatus);
        } 

/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        $accessToken = Auth::user()->token();
            
        $remoteUser = json_decode($accessToken);
    
        $user = Auth::user(); 
        return response()->json(['user' => $user,'token' => $remoteUser]);
    }   
}