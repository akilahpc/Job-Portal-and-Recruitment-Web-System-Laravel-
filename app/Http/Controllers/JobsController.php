<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class JobsController extends Controller
{
    // this method will show jobs page
    public function index(Request $request){

        $categories = Category::where('status',1)->get(); //category table eke details ganna
        $jobTypes = JobType::where('status',1)->get();  //job_types table eke details ganna

        $jobs = Job::where('status',1);

        // Search using keyword
        if(!empty($request->keyword)){ //'keyword'->form eke feeld name ek
            $jobs = $jobs->where(function($query)use($request){
                $query->orWhere('title','like','%'.$request->keyword.'%'); //'jobs' table eke 'title' feeld eke athi wachanayak search karoth ita adala job post prnwima  
                $query->orWhere('keywords','like','%'.$request->keyword.'%');  //'jobs' table eke 'keywords' feeld eke athi wachanayak search karoth ita adala job post prnwima  
            });
        }

        // Search using location
        if(!empty($request->location)){ 
            $jobs = $jobs->where('location',$request->location);
        }

        // Search using category
        if(!empty($request->category)){ 
            $jobs = $jobs->where('category_id',$request->category);
        }
        $jobTypeArray = [];
        // Search using Job Type
        if(!empty($request->jobType)){ 
            // 1,2,3  -> job type id
            $jobTypeArray = explode(',',$request->jobType);
            $jobs = $jobs->whereIn('job_type_id',$jobTypeArray);
        }

        // Search using experience
        if(!empty($request->experience)){ 
            $jobs = $jobs->where('experience',$request->experience);
        }

        $jobs = $jobs->with(['jobType','category']);
        if(!empty($request->sort) && $request->sort == '0'){
            $jobs = $jobs->orderBy('created_at','ASC');
        }else{
            $jobs = $jobs->orderBy('created_at','DESC');
        }
        
        $jobs = $jobs->paginate(100); 
        
        
        //'jobs' table eke athi details tika ganna / "with('jobType')"->job_types table eke data samaga / ('created_at','DESC')->desending order

        return view('front.jobs',[ //'jobs' page eke pahatha feelds display krnna
            'categories'=> $categories, 
            'jobTypes'=> $jobTypes,
            'jobs'=> $jobs,
            'jobTypeArray' => $jobTypeArray
        ]);
            }

            //This method will show the job detail page
        public function detail($id){

            $job = Job::where([
                'id' => $id,
                'status' => 1
            ])->with(['jobType','category'])->first(); //'job_types' saha 'category' table eke details samaga

            if($job == null){
                abort(404);
            }

            $count = 0;
            if(Auth::user()){
                $count = SavedJob::where([
                    'user_id' => Auth::user()->id,
                    'job_id' =>$id
                ])->count();
            }

            //fetch applicants

            $applications = JobApplication::where('job_id',$id)->with('user')->get();  //JobApplication -> model

            return view('front.jobDetail',['job' => $job,
                                           'count' => $count,
                                           'applications' => $applications
            ]);
        }

        public function applyJob(Request $request){

            $id = $request->id;

            $job = Job::where('id',$id)->first();

            //If job not found in db  (db eke ema job eka nomathin nam)
            if($job == null){
                $message = 'job does not exist.';
                session()->flash('error',$message);
                return response()->json([
                    'status' => false,
                    'message' => $message
                ]);
            }
            // you can not apply on your own job   (thaman post kala job ekakata thamanta apply kala nohaka)
            $employer_id = $job->user_id;

            if($employer_id == Auth::user()->id) {  // '$employer_id' samanaida login user id ekata
                $message = 'You can not apply on your own job';
                session()->flash('error',$message);
                return response()->json([
                    'status' => false,
                    'message' => $message
                ]);
            }

            // You can not apply on a job twise
            $jobApplicationCount = JobApplication::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
            ])->count();

            if($jobApplicationCount > 0){
                $message = 'You alredy applied on this job'; // apply katapu job ekekata nawatha apply karoth
                session()->flash('error',$message);
                return response()->json([
                    'status' => false,
                    'message' => $message
                ]);
            }

            //application detals save to database process
            $application = new JobApplication();  //'JobApplication()' model name eka
            $application->job_id = $id;
            $application->user_id = Auth::user()->id; //'Auth::user()->id'=login user id
            $application->employer_id = $employer_id;
            $application->applied_date = now();
            $application->save();

            //send notification email to employer
            $employer = User::where('id',$employer_id)->first();
            $mailData = [
                'employer' => $employer,
                'user' => Auth::user(),
                'job' => $job,
            ];
            Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

            $message = 'You have successfully applied.'; //applied success message

            session()->flash('success',$message);

                return response()->json([
                    'status' => true,
                    'message' => $message
                ]);
            }

        public function saveJob(Request $request){

                $id = $request->id;

                $job = Job::find($id);

                if($job == null){
                    session()->flash('error','Job not found');

                    return response()->json([
                        'status' => false,
                    ]);
                }

                // Check if user already saved the job
                $count = SavedJob::where([
                    'user_id' => Auth::user()->id,
                    'job_id' =>$id
                ])->count();

                if ($count > 0){
                    session()->flash('error','You already saved on this job.');

                    return response()->json([
                        'status' => false,
                    ]);
                }

                $savedJob = new SavedJob;
                $savedJob->job_id = $id;
                $savedJob->user_id = Auth::user()->id;
                $savedJob->save();

                session()->flash('success','You have successfully saved the job.');

                    return response()->json([
                        'status' => true,
                    ]);
        }
        
}
