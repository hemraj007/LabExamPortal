<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\question;
use App\opted_exam;
use App\admin_detail;
use App\student_detail;
use App\exam_detail;
use App\student_submmission;

class ExamController extends Controller
{
    //
    public $successStatus = 200;

    /** 
     * coursefetch api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    // public function coursefetch()
    // {
    //     $course = DB::table('admin_details')->distinct()->select('course_name')->get();

    //     if(is_null($course))
    //     {
    //         return response()->json(['message' => 'record not found'],404);
    //     }
    //     return response()->json($course,200);
    // }

    /**
     * startExam api
     * @return \Illuminate\Http\Response 
     */

     public function startExam(Request $request)
     {
        $validatedData = $request->validate([
            'course_name' => 'required',
            'exam_code' => 'required',
        ]);

        $course = $validatedData['course_name'];
        $pin = $validatedData['exam_code'];

        
        //retrieve admin_id
        $admin_id = DB::table('admin_details')
            ->select('admin_id')
            ->where('course_name',$course)
            ->first(); // to fetch first row of view
        
        
        // return response()->json(['admin' => $admin_id->admin_id]);

        if(is_null($admin_id))
        {
            return response()->json(['message'=>'Invalid course!!'],406);//not acceptable error
        }
        
        //retrieve exam details
        $exam_detail = DB::table('exam_details')
            ->select('exam_id','exam_name','exam_hours')
            ->where([['exam_code',$pin],['exam_for',$admin_id->admin_id]])
            ->first();
            // 

        if(is_null($exam_detail))
        {
            return response()->json(['message'=>'Invalid Exam Code!!'],400);
        }

        //update opted_exams table
 
        $accessToken = Auth::user()->token();
        $student_id = $accessToken->User_id;
        $duration = ($exam_detail->exam_hours)*60*60;//in seconds

        $student_info = [
            'student_id' => $student_id,
            'exam_id' => $exam_detail->exam_id, 
            'duration_left' => $duration
        ];

        $student = opted_exam::create($student_info);

        // return response()->json($exam_detail);
        
        $question_detail = DB::table('questions')
            ->select('id','title','description','marks')
            ->where('exam_id',$exam_detail->exam_id)
            ->get();

        if(is_null($question_detail))
        {
            return response()->json(['message'=>'questions not found!!'],404);
        }

        return response()->json(['question_details' => $question_detail,'exam_details' => $exam_detail,'subject' => $validatedData['course_name']]);

     }

     /**
     * submitSolution api
     * @return \Illuminate\Http\Response 
     */
    public function submitSolution(Request $request)
    {
        
    }

}
