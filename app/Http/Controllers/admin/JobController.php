<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index(){ 
        $jobs = Job::orderBy('created_at','DESC')->with('user','applications')->paginate(100);  //'Job'-> job model
        
        return view('admin.jobs.list',[
            'jobs' => $jobs
        ]);
    }

    public function edit($id){
        $job = Job::findOrFail($id);

        $categories = Category::orderBy('name','ASC')->get();
        $jobTypes = JobType::orderBy('name','ASC')->get();


        return view('admin.jobs.edit',[
            'job' => $job,
            'categories' => $categories,
            'jobTypes' => $jobTypes

        ]);
    }

    public function update(Request $request, $id){

        $rules=[
            'title' => 'required|min:5|max:200', //(form validation) ex:-'title'->adala feld eke 'name' eka
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];
    
        $validator = Validator::make($request->all(),$rules);
    
        if($validator->passes()){
    
        $job = Job::find($id);  //update a job
        $job->title = $request->title; //  {194-208} form eke ek ek feld wlin ena data '$job' veriable ekt ganna
        $job->category_id = $request->category; //ex:-'category_id' = table column name = form eke adala feeld eke 'id' ek
        $job->job_type_id = $request->jobType; 
        $job->vacancy = $request->vacancy;
        $job->salary = $request->salary;
        $job->location = $request->location;
        $job->description = $request->description;
        $job->benefits = $request->benefits;
        $job->responsibility = $request->responsibility;
        $job->qualifications = $request->qualifications;
        $job->keywords = $request->keywords;
        $job->experience = $request->title;
        $job->company_name = $request->company_name;
        $job->company_location = $request->company_location;
        $job->company_website = $request->company_website;
        $job->status = $request->status;
        $job->isFeatured = (!empty($request->isFeatured)) ? $request->isFeatured : 0;

        $job->save();   // form eken ena data database table ekta save karanna
    
        session()->flash('success','job updated successfully.'); //form eke job detail update krla submit krhama detail hriyata db table ekt save una nam success msg ek penwima
    
        return response()->json([
            'status'=>true,
            'errors'=>[]
        ]);
    
        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }
    }

    public function destroy(Request $request){

        $id = $request->id;

        $job = Job::find($id);

        if ($job == null){
            session()->flash('error','Either job deleted or not found');
            return response()->json([
                'status' => false
            ]);
        }

        $job->delete();
        session()->flash('success','Job deleted successfully');
            return response()->json([
                'status' => true
            ]);
    }

}
