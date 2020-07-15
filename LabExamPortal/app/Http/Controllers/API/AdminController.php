<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User; 
use App\admin_detail;
use App\exam_detail;
use App\question; 
use App\student_submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    //
    /**
     * admin data api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */
    public function admin_data()
    {
        $accessToken = Auth::user()->token();
        $admin_id = json_decode($accessToken)->user_id;

        $result = DB::table('admin_details')
                    ->select('instructor_name','course_code','course_name')
                    ->where('admin_id',$admin_id)
                    ->first();

        if(is_null($result))
        {
            return response()->json(['message'=>'Record notfound'],404);
        }

        return response()->json(['admin_data'=>$result],200);
    }


    /**
     * update marks api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */
    public function update_marks(Request $request)
    {
        $isUser = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];

        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }

        $validatedData = $request->validate([
            'student_id' => 'required',
            'question_id' => 'required',
            'marks' => 'required',
        ]);

        if(DB::table('student_submissions')->where([['student_id',$validatedData['student_id']],['qid',$validatedData['question_id']]])->exists())
        {
            $result = student_submission::where([['student_id',$validatedData['student_id']],['qid',$validatedData['question_id']]])
            ->update([
                'marks' => $validatedData['marks'],
            ]);

            return response()->json(['message'=>'marks updated successfully'],200);
        }
        else
        {
            return response()->json(["message" => "record not found"],404);
        }
       
    }

    /**
     * number_of_online_users api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function number_of_online_users()
    {
        $isUser = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];

        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $users = DB::table('users')->where([['isLogin','=','1'],['isAdmin','=','0'],])->count();
       
        if(is_null($users))
        {
            return response()->json(["message" => "record not found"],404);
        }
        return response()->json($users,200);
    }

    /**
     * list_online_users api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function list_online_users()
    {
       // $userIsAdmin = "";
        $isUser = "";
        $listname = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];
        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $users = DB::table('users')->select('name')->where([['isLogin','=','1'],['isAdmin','=','0'],])->get();
        if(is_null($users))
        {
            return response()->json(["message" => "record not found"],404);
        }
        // foreach ($users as $value) {
        //     //echo "$value <br>";
        //     $listname[] = $value->name;
        //   }
        return response()->json($users,200);
    }

    /**
     * create_exam api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function create_exam(Request $request)
    {
        $isUser = "";
        //$exam_sub = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];

        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $exam_unique_code = rand(1000,9999);
       // $accessToken = Auth::user()->token();
        //$remoteUser = json_decode($accessToken);
        
        //$exam_sub = DB::table('admin_details')->select('course_code')->where('admin_id',$remoteUser->user_id)->get()[0];
        $validatedData = $request->validate([
            'exam_name' => 'required', 
            'exam_hours' => 'required', 
            'exam_date' => 'required',
            'exam_time' => 'required',
            
        ]);
        $exam_info = [
                 'exam_name' => $validatedData['exam_name'],
                 'exam_hours' => $validatedData['exam_hours'],
                 'exam_for' => $remoteUser->user_id,
                 'exam_code' => $exam_unique_code,
                 'exam_date' => $validatedData['exam_date'],
                 'exam_time' => $validatedData['exam_time'],
                  //$exam_sub->course_code,
                 
                ];
        $exam = exam_detail::create($exam_info);
        
        return response()->json(['exam_code'=>$exam_unique_code,'message'=>'created Successfully'],201);
    }

    /**
     * list_exam api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function list_exam()
    {
        $isUser = "";
        $name = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];
        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        // $name = DB::table('users')->select('username')->where('id',$remoteUser->user_id)->get()[0];
         $exams = DB::table('exam_details')->select('exam_id','exam_name','exam_date','exam_time','exam_hours','exam_code')->where('exam_for',$remoteUser->user_id)->latest()->get();//get();
         if(is_null($exams))
           {
                return response()->json(["message" => "record not found"],404);
           }
         return response()->json($exams,200);
        
        
    }

    /**
     * add_question api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function add_question(Request $request)
    {
        $isUser = "";
        $examfor = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];
        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $validatedData = $request->validate([
            'exam_id' => 'required',
            'title' => 'required', 
            'description' => 'required', 
            'marks' => 'required',
            
        ]);
         if(DB::table('exam_details')->where('exam_id',$validatedData['exam_id'])->exists())
        {
            $question_info = [
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'marks' => $validatedData['marks'],
                'admin_id' =>$remoteUser->user_id,
                'exam_id' => $validatedData['exam_id'],
                
               ];

            $ques = question::create($question_info);

            return response()->json(['message' => 'question Successfully added'],200);
        }
        else 
        {
            return response()->json(['message'=>'Exam id does not exists'],404);
        }
       
   
   
    }

    /**
     * view_question api
     * @return \Illuminate\Http\Response 
     */

    public function view_question()
    {
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];
        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $question_info = DB::table('questions')->select('id','title','marks')->where([['admin_id',$remoteUser->user_id],['exam_id',]])->get();
        return response()->json($question_info,200);
    }


    /**
     * update_instructor api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function update_instructor(Request $request)
    {
        $isUser = "";
       // $code = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];

        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }

        $validatedData = $request->validate([
            'instructor_name' => 'required', 
            'email' => 'required|email|unique:users',    
        ]);
        
        $affectedRows = admin_detail::where('admin_id', '=',$remoteUser->user_id )->update(array('instructor_name' => $validatedData['instructor_name']));

        $affectedRows = user::where('id', '=',$remoteUser->user_id )->update(array('name' =>  $validatedData['instructor_name'] ,'email' => $validatedData['email']));

       
        return response()->json(['message' => 'Successfully updated'],200);

    }

    /**
     * next exam api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function nextExam()
    {
        $isUser = "";
       // $exams = "";
        // $code = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];

        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }

        $dt = Carbon::now()->toDateString();
        $exams = DB::table('exam_details')->select('exam_date','exam_time')->where('exam_date', '>', $dt)->orderBy('exam_date')->orderBy('exam_time')->first();

        if(is_null($exams))
        {
            return response()->json(["message" => "record not found"],404);
        }

        return response()->json(['nextExam'=>$exams],200);

    }
    /**
     * edit question api
     * @return \Illuminate\Http\Response 
     * status : up-to-date
     */

    public function edit_question(Request $request)
    {
        $isUser = "";
        // $code = "";
        $accessToken = Auth::user()->token();
        $remoteUser = json_decode($accessToken);
        $isUser = DB::table('users')->select('isAdmin')->where('id',$remoteUser->user_id)->get()[0];
        if(!$isUser->isAdmin)
        {
            return response()->json(["message" => "access denied"],403);
        }
        $validatedData = $request->validate([
            'id' => 'required',
            'title' => 'required', 
            'description' => 'required',
            'marks' => 'required',    
        ]);

        if(DB::table('questions')->where('id', '=',$validatedData['id'] )->exists())
        {
            
            $affectedRows = question::where('id', '=',$validatedData['id'] )->update(array('title' => $validatedData['title'] ,'description' => $validatedData['description'],'marks'  => $validatedData['marks']));

            return response()->json(['message' => 'Question Successfully edited'],200);
        }
        else
        {
            return response()->json(['message'=>'Question not Found'],404);
        }
        
    
    }
}
