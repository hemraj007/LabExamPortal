<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\student_detail;
use App\admin_detail;
use App\opted_exam;
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
            'username' => 'required',
            'password' => 'required',
            'isAdmin' => 'required'
        ]);

        if(!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid Credentials'],401);
        }
 
        //update status to login
        DB::table('users')
            ->where('username',$loginData['username'])
            ->update([
                'isLogin' => 1
            ]);


      
//         $success = auth()->user();
//         $token = auth()->user()->createToken('authToken',['test'=>'test'])-> accessToken;
        
                
//         return response()->json(['message' => 'Logged In!', 'status' => $this-> successStatus,'token' => $token],$this-> successStatus);

        // if(isAdmin == 1)
        // {
        //     $token =  auth()->user()->createToken('authToken')-> accessToken;
        //     return response()->json(['message' => 'Logged In!', 'token' => $token],$this-> successStatus);

        // }
        // else if(isAdmin == 0)
        // {
        //     $token = auth()->user()->createToken('authToken')-> accessToken;
        //     $course = DB::table('admin_details')->distinct()->select('course_name')->get();
        //     return response()->json(['message' => 'Logged In!','token' => $token,'course' => $course],$this-> successStatus);
        // }
       
        // $success = auth()->user();
        $token = auth()->user()->createToken('authToken')-> accessToken;
        
        
        
        return response()->json(['message' => 'Logged In!', 'status' => $this-> successStatus,'token' => $token, 'isAdmin' => $loginData['isAdmin']],$this-> successStatus);


        
    }


/**
     * fetch course api
     *  @return \Illuminate\Http\Response 
     */
    public function fetch_course()
    {
        $course = DB::table('admin_details')->distinct()->select('course_code','course_name')->get();
        return response()->json(['course' => $course,'status' => $this-> successStatus],$this-> successStatus);
        
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
     * Student Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function student_register(Request $request) 
    { 
        $validatedData = $request->validate([
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            // 'isAdmin' => 'required',
            'username' => 'required|unique:users',
            'course' => 'required',
            'semester' => 'required',
            'password' => 'required|confirmed',
            // 'isLogin' => 'required'
        ]);

        
        $validatedData['password'] = bcrypt($request->password);

        $user_info = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'isAdmin' => 0,
            'username' => $validatedData['username'],
            'password' => $validatedData['password'],
        ];

        $user = User::create($user_info);
        
        $student_info = [
            'student_id' => $user['id'],
            'name' => $validatedData['name'],
            'course' => $validatedData['course'],
            'semester' => $validatedData['semester'],
        ];

        $student = student_detail::create($student_info);

        $success['token'] =  $user->createToken('authToken')-> accessToken;
        $success['user'] = $user;

        return response()->json(['message' => 'Registered Successfully', 'status' => $this-> successStatus],$this-> successStatus);
        } 


        /** 
     * admin Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function admin_register(Request $request) 
    { 
        $validatedData = $request->validate([
            'course_name' => 'required', 
            'email' => 'required|email|unique:users', 
            'username' => 'required|unique:users',
            'name' => 'required',
            'password' => 'required|confirmed',
        ]);

        
        $validatedData['password'] = bcrypt($request->password);

        $user_info = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'isAdmin' => 1,
            'username' => $validatedData['username'],
            'password' => $validatedData['password'],
        ];

        $user = User::create($user_info);
        
        $admin_info = [
            'admin_id' => $user['id'],
            'course_code' => $validatedData['username'],
            'course_name' => $validatedData['course_name'],
            'instructor_name' => $validatedData['name'],
        ];

        $admin = admin_detail::create($admin_info);

        $success['token'] =  $user->createToken('authToken')-> accessToken;
        // $success['user'] = $user;
        return response()->json(['message' => 'Registered Successfully', 'status' => $this-> successStatus],$this-> successStatus);
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