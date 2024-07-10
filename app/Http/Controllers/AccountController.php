<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AccountController extends Controller
{
    //This method will show user registration page
    public function registration(){
        return view('front.account.registration');
    }

        //This method will save a user
        public function processRegistration(Request $request) {
            $validator = Validator::make($request->all(),[
           'name' =>'required',                                //register form validations
           'email' =>'required|email|unique:users,email',
           'password' =>'required|min:5|same:confirm_password',
           'confirm_password' =>'required',
       ]);

       if($validator->passes()){         //validation pass nam databse ekta data save()  33 & success msg

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->name = $request->name;
        $user->save();
    
        session()->flash('success','you have registerd successfully');
    
        return response()->json([
            'status'=>true,
            'errors'=>[]
        ]);

    }else{                               // validation fail nm error
        return response()->json([
            'status'=>false,
            'errors'=>$validator->errors()
        ]);
    }
}



    //This method will show user login page
    public function login(){
         return view('front.account.login');      
    }

    public function authentiate(Request $request){

        $validator = Validator::make($request->all(),[    //login form validation
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->passes()){

            if(Auth::attempt(['email'=>$request->email, 'password'=>$request->password])){
              return redirect()->route('account.profile');   //login form eke email password hari nm profile page ekta redirect
            }else{
                return redirect()->route('account.login')->with('error','Either Email/Password is incorrect'); //login form eke email,password waradi nm error msg ek & login page ekta redirect
            }

        }else{
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

    public function profile(){

        $id=Auth::user()->id;
        $user=User::where('id',$id)->first();
        
        return view('front.account.profile',[
            'user'=>$user
        ]);
    }

    public function updateProfile(Request $request){
        $id = Auth::user()->id;

        $validator=Validator::make($request->all(),[
            'name'=>'required|min:5|max:20',
            'email'=>'required|email|unique:users,email,'.$id.',id'
        ]);

        if($validator->passes()) {

            $user =User::find($id);
            $user->name=$request->name;
            $user->email=$request->email;
            $user->mobile=$request->mobile;
            $user->designation=$request->designation;
            $user->save();

            session()->flash('success','Profile updated successfully');

            return response()->json([
                'status'=>true,
                'errors'=>[]
            ]);

        } else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }
    }
    

public function logout(){
    Auth::logout();        //logout wima
    return redirect()->route('account.login');   //logout unahama user login page ekta redirect wenna
}

public function updateProfilePic(Request $request){

    $id=Auth::user()->id;

    $validator = Validator::make($request->all(),[
        'image' => 'required|image'
    ]);

    if($validator->passes()){

        $image = $request->image; //"image"-> app.blade.php file eke profilePicForm eke (id="image") name ek
        $ext = $image->getClientOriginalExtension();
        $imageName = $id.'-'.time().'.'.$ext;   //3-123458625
        $image->move(public_path('/profile_pic/'), $imageName); //(138-141) file upload proces



        User::where('id',$id)->update(['image' => $imageName]); // database ekta image ek save kirima

        session()->flash('success','Profile Picture Updatrd Successfully'); //success msg

        return response()->json([
            'status'=> true,
            'errors' => []
        ]);

    }else{
        return response()->json([
            'status'=> false,
            'errors' => $validator->errors()
        ]);
    }
}

public function createJob(){

    $categories = Category::orderBy('name','ASC')->where('status',1)->get(); //category table eke name feld eke tiyana data tika ganna
    $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();  //job_types table eke name feld eke tiyana data tika ganna

    return view('front.account.job.create',[
    'categories'=> $categories,
    'jobTypes'=> $jobTypes,
]);
}

public function saveJob(Request $request){

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

    $job = new Job();  //'Job' lesa 'model' ekak sada ee namin constructor ekk sadima  (create a job)
    $job->title = $request->title; //  {194-208} form eke ek ek feld wlin ena data '$job' veriable ekt ganna
    $job->category_id = $request->category; //ex:-'category_id' = table column name = form eke adala feeld eke 'id' ek
    $job->job_type_id = $request->jobType; 
    $job->user_id = Auth::user()->id;
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
    $job->save();   // form eken ena data database table ekta save karanna

    session()->flash('success','job added successfully.'); //form eke job detail hriyat dala submit krhama detail hriyata db table ekt save una nam success msg ek penwima

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

public function myJobs(){

    $jobs = Job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at','DESC')->paginate(10); //"orderBy('created_at','DESC')" ->job create list eke antimata add krpu job ek mulinma penwanna
    return view('front.account.job.my-jobs',[
        'jobs' => $jobs
    ]);
}



public function editJob(Request $request, $id){  //edit job
    
    $categories = Category::orderBy('name','ASC')->where('status',1)->get(); //category table eke name feld eke tiyana data tika ganna
    $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get(); //job_types table eke name feld eke tiyana data tika ganna

    $job = Job::where([
        'user_id' => Auth::user()->id,
        'id' => $id
    ])->first();

    if($job == null){
        abort(404);
    }

    return view('front.account.job.edit',[
        'categories'=>$categories,
        'jobTypes'=>$jobTypes,
        'job'=>$job,
    ]);
}

public function updateJob(Request $request, $id){

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
    $job->user_id = Auth::user()->id;
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

public function deleteJob(Request $request){

    $job = Job::where([  //job post ekk delete kirimedi adala job post eka login 'user_id' eka saha 'jobId' eken idintify kra gani
        'user_id' => Auth::user()->id,
        'id' => $request->jobId
    ])->first();

    if($job == null){
        session()->flash('error','Either job deleted or not found.'); //$job veriable ek null nam err ek display kirima
        return response()->json([
            'status'=>true
        ]);
    }
    Job::where('id',$request->jobId)->delete(); // adala job post ek delete kirima

        session()->flash('success','Job deleted successfully.'); // deleted success msg ek display kirima
        return response()->json([
            'status'=>true
        ]);
}

public function myJobApplications(){
    $jobApplications = JobApplication::where('user_id',Auth::user()->id)
    ->with(['job','job.jobType','job.applications']) //'job.jobType'->'job.php' file eke tiyana 'jobType' function ek (relation)
    ->orderBy('created_at','DESC')
    ->paginate(100);

    return view('front.account.job.my-job-applications',[
        'jobApplications' => $jobApplications
    ]);
}

public function removeJobs(Request $request){  //Job application remove process
    $jobApplication = JobApplication::where([
        'id' => $request->id,
        'user_id' => Auth::user()->id]
        )->first();

        if($jobApplication == null){
            session()->flash('error','Job application not found');
            return response()->json([
                'status' => false,
            ]);
        }

        JobApplication::find($request->id)->delete();

        session()->flash('success','Job application removed successfully.');
        return response()->json([
            'status'=>true,
        ]);
}

public function savedJobs(){
   // $jobApplications = JobApplication::where('user_id',Auth::user()->id)
   // ->with(['job','job.jobType','job.applications']) //'job.jobType'->'job.php' file eke tiyana 'jobType' function ek (relation)
   // ->paginate(10);

   $savedJobs = SavedJob::where([
        'user_id' => Auth::user()->id
   ])->with(['job','job.jobType','job.applications'])
   ->orderBy('created_at','DESC')
   ->paginate(100);

    return view('front.account.job.saved-jobs',[
        'savedJobs' => $savedJobs
    ]);
}

public function removeSavedJob(Request $request){  //saved job remove process
    $savedJob = SavedJob::where([
        'id' => $request->id,
        'user_id' => Auth::user()->id]
        )->first();

        if($savedJob == null){
            session()->flash('error','Job not found');
            return response()->json([
                'status' => false,
            ]);
        }

        SavedJob::find($request->id)->delete();

        session()->flash('success','Job removed successfully.');
        return response()->json([
            'status'=>true,
        ]);
}


public function updatePassword(Request $request){
    $validator = Validator::make($request->all(),[
        'old_password' => 'required',
        'new_password' => 'required|min:5',
        'confirm_password' => 'required|same:new_password',
    ]);

    if($validator->fails()){
        return response()->json([
            'status' =>false,
            'errors' => $validator->errors(),
        ]);
    }

    if(Hash::check($request->old_password
    ,Auth::user()->password) == false){
        session()->flash('error','Your old password is incorrect.');
        return response()->json([
            'status' =>true,
        ]);
    }

    //existing password update process
    $user = User::find(Auth::user()->id);
    $user->password = Hash::make($request->new_password);
    $user->save();

    session()->flash('success','Password updated successfully.');
    return response()->json([
        'status' => true
    ]);

}

}


