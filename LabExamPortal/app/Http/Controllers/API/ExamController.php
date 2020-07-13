<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\question;
use App\opted_exam;
use App\admin_detail;
use App\student_detail;
use App\exam_detail;
use App\student_submission;
use GuzzleHttp\Client;
use Carbon\Carbon;

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
            'course_code' => 'required',
            'exam_code' => 'required',
        ]);

        $course = $validatedData['course_code'];
        $pin = $validatedData['exam_code'];
        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

        $ip = \Request::ip();

        //retrieve user details
        $student_details = DB::table('users')
                            ->select('name','username')
                            ->where('id',$student_id)
                            ->first();

        
        //retrieve admin_id
        $admin_id = DB::table('admin_details')
            ->select('admin_id','course_name')
            ->where('course_code',$course)
            ->first(); // to fetch first row of view
        
        
        // return response()->json(['admin' => $admin_id->admin_id]);

        if(is_null($admin_id))
        {
            return response()->json(['message'=>'Invalid course!!'],406);//not acceptable error
        }
        
        //retrieve exam details
        $exam_detail = DB::table('exam_details')
            ->select('exam_id','exam_name','exam_hours','exam_date','exam_time')
            ->where([['exam_code',$pin],['exam_for',$admin_id->admin_id]])
            ->first();
            // 

        if(is_null($exam_detail))
        {
            return response()->json(['message'=>'Invalid Exam Code!!'],400);
        }

        //check if user already started exam
        if(DB::table('opted_exams')->where([['student_id',$student_id],['exam_id',$exam_detail->exam_id]])->exists())
        {
            $exam_data = DB::table('questions')
                            ->join('student_submissions','questions.id','=','student_submissions.qid')
                            ->select('questions.id','questions.title','questions.description','questions.marks','student_submissions.is_attempted','student_submissions.no_of_submissions','student_submissions.created_at','student_submissions.updated_at')
                            ->get();
            
            $duration = DB::table('opted_exams')
                            ->select('duration_left')
                            ->where([['student_id',$student_id],['exam_id',$exam_detail->exam_id]])
                            ->first();
            
            // return response()->json(['question_details' => $exam_data,'exam_details' => $exam_detail,'student_name'=>$student_details->name,'username'=>$student_details->username,'client_ip'=>$ip,'course_code' => $course,'course' => $admin_id->course_name,'duration_left' => $student->duration_left]);
            return response()->json(['question_details' => $exam_data,'exam_details' => $exam_detail,'student_name'=>$student_details->name,'username'=>$student_details->username,'client_ip'=>$ip,'course_code' => $course,'course' => $admin_id->course_name,'duration_left' => $duration->duration_left]);
        }
        
        // if user has not already started the exam
        //update opted_exams table
 
        
        $duration = ($exam_detail->exam_hours)*60*60;//in seconds

        $student_info = [
            'student_id' => $student_id,
            'exam_id' => $exam_detail->exam_id, 
            'duration_left' => $duration
        ];

        $student = opted_exam::create($student_info);

        // return response()->json($exam_detail);
        
        $question_detail = DB::table('questions')
            ->select('id')
            ->where('exam_id',$exam_detail->exam_id)
            ->get();

        if(is_null($question_detail))
        {
            return response()->json(['message'=>'questions not found!!'],404);
        }

        //make an entry in student_submissions table
        foreach ($question_detail as $question)
        {
            $submission_data = [
                'student_id' => $student_id,
                'exam_id' => $exam_detail->exam_id,
                'qid' => $question->id,
                'no_of_submissions' => 0
            ];

            $submission = student_submission::create($submission_data);
        }

        $exam_data = DB::table('questions')
                            ->join('student_submissions','questions.id','=','student_submissions.qid')
                            ->select('questions.id','questions.title','questions.description','questions.marks','student_submissions.is_attempted','student_submissions.no_of_submissions','student_submissions.created_at','student_submissions.updated_at')
                            ->get();
        
        return response()->json(['question_details' => $exam_data,'exam_details' => $exam_detail,'student_name'=>$student_details->name,'username'=>$student_details->username,'client_ip'=>$ip,'course_code' => $course,'course' => $admin_id->course_name,'duration_left' => $student->duration_left]);

        // return response()->json(['question_details' => $question_detail,'exam_details' => $exam_detail,'client_ip'=>$ip,'course_code' => $course,'course' => $admin_id->course_name,'duration_left' => $student->duration_left]);

     }

     /**
     * fetch source code api
     * @return \Illuminate\Http\Response 
     */
     public function fetch_source_code(Request $request)
     {
        $validatedData = $request->validate([
            'question_id' => 'required',
        ]);

        $question_id = $validatedData['question_id'];

        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

        $source = DB::table('student_submissions')
                    ->select('source_code','lang')
                    ->where([['student_id',$student_id],['qid',$question_id]])
                    ->first();

         if(is_null($source))
        {
            return response()->json(['message'=>'Record not found!!'],404);
        }

        return response()->json(['source_code'=>$source->source_code,'lang' => $source->lang]);
     }

     /**
     * submitSolution api
     * @return \Illuminate\Http\Response 
     */
    public function save_source(Request $request)
    {

       
        $validatedData = $request->validate([
            'question_id' => 'required',
            'source_code' => 'required',
            'lang' => 'required',
        ]);

        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

        if (DB::table('student_submissions')->where([['student_id',$student_id],['qid',$validatedData['question_id']]])->exists())
        {
            
            student_submission::where([['student_id',$student_id],['qid',$validatedData['question_id']]])
                    ->update([
                        'source_code' => $validatedData['source_code'],
                        'lang' => $validatedData['lang'],
                        'is_attempted' => 1,
                        ]);

            return response()->json(["message" => "record updated successfully"], 200);
        }
        else 
        {
            return response()->json(["message" => "record not found"], 404);
        }
        

        // if(!$result)
        // {
        //     return response()->json(['message' => 'source_code not saved !' ]);
        // }

        // return response()->json(['success' => $result]);
    }

    /**
     * update duration api
     * @return \Illuminate\Http\Response 
     */

    public function update_duration(Request $request)
    {
        $validatedData = $request->validate([
            'exam_id' => 'required',
            'duration_left' => 'required',
        ]);

        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

        if (DB::table('opted_exams')->where([['student_id',$student_id],['exam_id',$validatedData['exam_id']]])->exists())
        {
            
            // $result = DB::table('opted_exams')
            //         ->select('opted_exams.*')
            //         ->where([['student_id',$student_id],['exam_id',$validatedData['exam_id']]])
            //         ->update([
            //             'duration_left' => $validatedData['duration_left'],
            //             ]);
            opted_exam::where([['student_id',$student_id],['exam_id',$validatedData['exam_id']]])->update([
                'duration_left' => $validatedData['duration_left']
            ]);
            

            return response()->json(["message" => "record updated successfully"], 200);
        }
        else 
        {
            return response()->json(["message" => "record not found"], 404);
        }

    }

    /**
     * run api
     * @return \Illuminate\Http\Response 
     */
    
    public function run(Request $request)
    {
        $validatedData = $request->validate([
            'question_id' => 'required',
            'source_code' => 'required',
            'lang' => 'required',
            'input' => '',
        ]);

        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

       
        $body['form_params'] = [
            // 'client_id'=> 'e90140d8ed8d190ee44a33f321175c4d60433f9249c8.api.hackerearth.com',
            'client_secret' => 'f8b71acad22e556b1ff5ebc842260584263ea6e8',
            'async' => 0,
            'source' => $validatedData['source_code'],
            'input' => $request->input,
            'lang' => $validatedData['lang'],
            'time_limit' => 5,
            'memory_limit' => 262144,
        ];

       
        $client = new Client();
        $result = $client->request('POST','https://api.hackerearth.com/v3/code/run/',$body);
        $decode_result = json_decode($result->getBody());

        //check for error
        if($decode_result->compile_status == "OK")
        {
            $output = $decode_result->run_status->output;
        }
        else
        {
            $output = $decode_result->compile_status;
        }

        //update in database
        if (DB::table('student_submissions')->where([['student_id',$student_id],['qid',$validatedData['question_id']]])->exists())
        {
            
            student_submission::where([['student_id',$student_id],['qid',$validatedData['question_id']]])
                    ->update([
                        'source_code' => $validatedData['source_code'],
                        'input' => $validatedData['input'],
                        'lang' => $validatedData['lang'],
                        // 'output' => $decode_result->run_status->output,
                        'output' => $output,
                        'is_attempted' => 1,
                        ]);
            $status = ['message' => 'Record Added in database','status'=>200];
        }
        else
        {
            $status = ['message' => 'Record not found','status'=>400];
        }
        

        return response()->json(['hackerearth'=>$decode_result,'status'=>$status]);
    }


     /**
     * final submission api
     * @return \Illuminate\Http\Response 
     */
    
    public function final_submission(Request $request)
    {
        $validatedData = $request->validate([
            'question_id' => 'required',
            'source_code' => 'required',
            'lang' => 'required',
            'input' => '',
        ]);

        $accessToken = json_decode(Auth::user()->token());
        $student_id = $accessToken->user_id;

       
        $body['form_params'] = [
            // 'client_id'=> 'e90140d8ed8d190ee44a33f321175c4d60433f9249c8.api.hackerearth.com',
            'client_secret' => 'f8b71acad22e556b1ff5ebc842260584263ea6e8',
            'async' => 0,
            'source' => $validatedData['source_code'],
            'input' => $request->input,
            'lang' => $validatedData['lang'],
            'time_limit' => 5,
            'memory_limit' => 262144,
        ];

       
        $client = new Client();
        $result = $client->request('POST','https://api.hackerearth.com/v3/code/run/',$body);
        $decode_result = json_decode($result->getBody());

        //check for error
        if($decode_result->compile_status == "OK")
        {
            $output = $decode_result->run_status->output;
        }
        else
        {
            $output = $decode_result->compile_status;
        }

         
        $current_date_time = Carbon::now()->toDateTimeString();

        //update in database
        if (DB::table('student_submissions')->where([['student_id',$student_id],['qid',$validatedData['question_id']]])->exists())
        {
            
            student_submission::where([['student_id',$student_id],['qid',$validatedData['question_id']]])
                    ->update([
                        'source_code' => $validatedData['source_code'],
                        'input' => $validatedData['input'],
                        'lang' => $validatedData['lang'],
                        // 'output' => $decode_result->run_status->output,
                        'output' => $output,
                        'is_attempted' => 1,
                        'submission_time' => $current_date_time,
                        'no_of_submissions' => DB::raw('no_of_submissions+1')
                        ]);
            $status = ['message' => 'Successfully submitted !','status'=>200];
        }
        else
        {
            $status = ['message' => 'Record not found','status'=>400];
        }
        

        return response()->json(['hackerearth'=>$decode_result,'status'=>$status]);
    }

    

}
