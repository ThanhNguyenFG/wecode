<?php

namespace App\Http\Controllers;

use App\Submission;
use App\Assignment;
use App\Problem;
use App\Queue_item;
use App\Language;
use App\Setting;
use Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class submission_controller extends Controller
{
	public function __construct()
	{
		$this->middleware('auth'); // phải login
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($assignment_id = NULL, $user_id = 'all', $problem_id = 'all', $choose = 'all')
	{
		// if ($assignment_id == 0)
		//     abort(403,'You have not selected assignment');
		Auth::user()->selected_assignment_id = $assignment_id;
		Auth::user()->save(); 

		$assignment = Assignment::with('submissions.user', 'submissions.problem')->find($assignment_id);
		if ( in_array( Auth::user()->role->name, ['student']) )
		{
			$submissions =$assignment->submissions()->where('user_id',Auth::user()->id);
		}
		else 
		{
			$submissions =$assignment->submissions();
			if ($user_id != 'all'){
				$submissions = $submissions->where('user_id',intval($user_id));
			}
		}

		if ($choose == 'final'){
			$submissions = $submissions->where('is_final',1);
		}
		if ($problem_id != 'all'){
			$submissions = $submissions->where('problem_id',intval($problem_id));
		}
		
		$submissions = $submissions->with(['language','user'])->latest()->get();
		$all_problems = Assignment::find($assignment_id)->problems->keyBy('id');
		foreach ($submissions as $submission)
			$this->status($all_problems, $submission);


		return view('submissions.list',['submissions' => $submissions, 'assignment' => $assignment, 'user_id' => $user_id, 'problem_id' => $problem_id, 'choose' => $choose, 'all_problems' => $all_problems]); 
	}

	public function create($assignment_id, $problem_id){
		$assignment = Assignment::find($assignment_id);
		
		if ($assignment_id == 0){
			$problem = Problem::find($problem_id);
			if ($problem->allow_practice == 0 && in_array( Auth::user()->role->name, ['student']) ){
				abort(404);
			}
		}
		else if ($problem_id != 0)
			$problem = $assignment->problems->find($problem_id);
		else
			$problem = $assignment->problems->first();
		
		if ($problem == NULL) abort(404);
		return view('submissions.create', ['assignment' => $assignment, 'problem' => $problem]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'assignment' => ['integer', 'gt:-1'],
			'problem' => ['integer', 'gt:0'],
		]);
		
		if ($this->upload($request))
			return redirect()->route('submissions.index', [$request->assignment, 'all', 'all', 'all']);
		else
			abort(403,'Error Uploading File');
	}
	

 
	public function upload_file_code($request, $user_dir, $submission)
	{
		$ext = $request->userfile->extension();
		$file_name = basename($request->userfile->getClientOriginalName(), ".{$ext}"); // uploaded file name without extension    
		$file_name = preg_replace('/[^a-zA-Z0-9_\-()]+/', '', $file_name);

		$path = $request->userfile->storeAs($user_dir, $file_name, 'my_local');

		if ($path)
		{      
			$this->add_to_queue($submission, $submission->assignment, $file_name);   
			return TRUE;
		}
		
		return FALSE;
	}

	public function upload_post_code($code, $user_dir, $submission)
	{
		if (strlen($code) > Setting::get("file_size_limit") * 1024 )
			//string length larger tan file size limit
			abort(403, "Your submission is larger than system limited size");

		$ext = $submission->language->extension;
		$file_name = "solution";
		file_put_contents("{$user_dir}/{$file_name}-"
							.($submission->assignment->total_submits+1)
							. "." . $ext, $code);

		
		$this->add_to_queue($submission, $submission->assignment
								, "{$file_name}-".($submission->assignment->total_submits+1) 
							);
		return TRUE;
	}

	private function in_queue ($user_id, $assignment_id, $problem_id)
	{
		$queries = Queue_item::all();
		foreach ($queries as $query)
		{
			$tmp = $query->submission->where(array('user_id' => $user_id, 'assignment_id' => $assignment_id, 'problem_id' => $problem_id))->get();
			if ($tmp->count() > 0) 
					return TRUE;
		}
		return FALSE;
	}

	private function add_to_queue($submission, $assignment, $file_name)
	{
		$assignment->increment('total_submits');
		$submission->file_name = $file_name;
		$submission->save();

		Queue_item::add_and_process($submission->id, 'judge');
		
	}

	private function get_path($username, $assignment_id, $problem_id)
	{
		$assignment_root = rtrim(Setting::get("assignments_root"),'/');
		return $assignment_root . "/assignment_{$assignment_id}/problem_{$problem_id}/{$username}";
	}


	public function rejudge_view()
	{
		if (in_array( Auth::user()->role->name, ['student']))
			abort(403,"You don't have permission to do that");
		if (Auth::user()->selected_assignment_id != null)
			$assignment = Assignment::with('problems')->find(Auth::user()->selected_assignment_id);
		else
			$assignment = Assignment::with('problems')->find(0);
		return view('submissions.rejudge', ['assignment' => $assignment, 'problems' => $assignment->problems]);
	}


	public function rejudge_all_problems_assignment(Request $request)
	{
		if (in_array( Auth::user()->role->name, ['student']))
			abort(403,"You don't have permission to do that");
		if (Auth::user()->selected_assignment_id != null)
			$assignment = Assignment::with('problems')->find(Auth::user()->selected_assignment_id);
		else
			$assignment = Assignment::with('problems')->find(0);
		if ($request->problem_id == 'all')
			$submissions = Submission::where('assignment_id',$assignment->id)->get();
		else
			$submissions = Submission::where('assignment_id',$assignment->id)->where('problem_id', $request->problem_id)->get();
		foreach ($submissions as $submission)
		{
			$a = Queue_item::add_and_process($submission->id, 'rejudge');
			$submission->status = 'PENDING';
			$submission->save();
		}
		return redirect()->back()->with('success', 'Rejudge in progress');   
	}

	public function rejudge(Request $request){
		if (!in_array( Auth::user()->role->name, ['student'])){
			$validated = $request->validate([
				'submission_id' => ['integer'],
			]);	
			
			$sub = Submission::find($request->submission_id);
			
			if ($sub == NULL) abort(404);

			$a = Queue_item::add_and_process($sub->id, 'rejudge');
			$sub->status = 'PENDING';
			$sub->save();
			
			return response()->json(
				['done' => 1]
			);
		}
	}

	public function get_template(Request $request){
		$validated = $request->validate([
			'assignment_id' => ['integer'],
			'problem_id' => 'integer',
		]);
		// dd($request->input());
		$assignment = Assignment::with('problems')->find($request->input('assignment_id'));
		// dd($assignment->can_submit(Auth::user()));
		if ($assignment == NULL || $assignment->can_submit(Auth::user())->can_submit == false){
			abort(403, 'Either assigment ID is invalid or you cannot submit to this assigment');
		}
		
		$problem = Problem::find($request->problem_id);
		if (
			$problem == NULL  ||
			( $assignment->id != 0 &&
			!in_array($request->input('problem_id'), $assignment->problems()->pluck('id')->all())
			)
		)
		{
			abort(403, 'Invalid problem ID');
		}

		$template = $problem->template_content('cpp');
		
		if ($template == NULL)
			$result = array('banned' => '', 'before'  => '', 'after' => '');

		preg_match("/(\/\*###Begin banned.*\n)((.*\n)*)(###End banned keyword\*\/)/"
			, $template, $matches
		);
	
		$set_or_empty = function($arr, $key){
			if(isset($arr[$key])) return $arr[$key];
			return "";
		};

		$banned = $set_or_empty($matches, 2);

		preg_match("/(###End banned keyword\*\/\n)((.*\n)*)\/\/###INSERT CODE HERE -\n?((.*\n?)*)/"
			, $template, $matches
		);

		$before = $set_or_empty($matches, 2);
		$after = $set_or_empty($matches, 4);

		$result = array('banned' => $banned, 'before'  => $before, 'after' => $after);

		return response()->json($result);

	}

	public function upload($request)
	{
		$problem = Problem::find($request->problem);
		$assignment = Assignment::find($request->assignment);
		$language = Language::find($request->language);

		$coefficient = 100;
		if ($assignment->id == 0)
			if (!in_array( Auth::user()->role->name, ['admin']) && $problem->allow_practice!=1)
				abort(403,'Only admin can submit without assignment');
		else
		{
			$coefficient = $this->eval_coefficient($assignment);

			$a = $assignment->can_submit(Auth::user());
			if(!$a->can_submit) abort(403, $a->error_message);

			if ($this->in_queue(Auth::user()->id, $assignment->id, $problem->id))
				abort(403,'You have already submitted for this problem. Your last submission is still in queue.');

			
			if ($problem->languages->where('id',$language->id)->count() == 0)
				abort(403,'This file type is not allowed for this problem.');
		}

		$submission = new Submission ([
			'assignment_id' => $assignment->id,
			'problem_id' => $problem->id,
			'user_id' => Auth::user()->id,
			'is_final' => 0,
			'status' => 'pending',
			'pre_score' => 0,
			'coefficient' => $coefficient,
			'file_name' => null,
			'language_id' => $language->id,
		]);

		$user_dir = $this->get_path(Auth::user()->username, $assignment->id, $problem->id);

		if (!file_exists($user_dir))
			mkdir($user_dir, 0700, TRUE);

		$code = $request->code;
		if ($code != NULL)
			return $this->upload_post_code($code, $user_dir, $submission);
		else 
		{
			if ($request->hasFile('userfile')) 
			{
				return $this->upload_file_code($request, $user_dir, $submission);
			}
			else abort(403,'No file chosen');
		}
	}

	public function select_final(Request $request)
	{
		$submission_curr = Submission::find($request->submission);
		if (!$submission_curr) abort(403,"Submission not found");

		$submission_final = Submission::where(array('user_id' => Auth::user()->id, 'assignment_id' => $submission_curr->assignment_id, 'problem_id' => $submission_curr->problem_id, 'is_final' => 1))->get();

		$submission_final->is_final = 0;
		$submission_final->save();

		$submission_curr->is_final = 1;
		$submission_curr->save();
	}
	public function view_code()
	{
		$submit_id = $_POST['submit_id'];
		$type = $_POST['type'];

		$submission = Submission::find($submit_id);

		if (!$submission) abort(403,"Submission not found");
		if (in_array(Auth::user()->role->name, ['student']) && $submission->user_id!=Auth::user()->id)
			abort(403,"You don't have permission to view another user's code");

		$submit_path = $this->get_path(Auth::user()->username, $submission->assignment_id, $submission->problem_id);
		$file_extension = $submission->language->extension;

		if ($type == "code")
			$file_path = $submit_path . "/{$submission->file_name}.". $file_extension;
		elseif ($type == "log")
			$file_path = $submit_path . "/result-{$submission['submit_id']}.html";
		elseif ($type == "result")
			$file_path = $submit_path . "/result-{$submission->id}.html";

		$result = array(
				'file_name' => $submission->file_name .'.'. $file_extension,
				'text' => Storage::disk('my_local')->exists($file_path) ? Storage::disk('my_local')->get($file_path):"File Not Found");

		return $result;
	}

	private function eval_coefficient($assignment)
	{
		ob_start();
		try 
		{
			eval($assignment->late_rule);
		}
		catch (\Throwable $e) 
		{
			$coefficient = "error";
		}
		if (!isset($coefficient))
			$coefficient = "error";
		ob_end_clean();
		return $coefficient;
	}
	private function status($all_problem, $submission)
	{
		$score = ceil($submission->pre_score*
							($submission->assignment->problems[$submission->problem_id]->pivot->score??0)
							/10000);
		if ($submission->coefficient == 'error')
			$submission->final_score = 0;
		else
			$submission->final_score = ceil($score*$submission->coefficient/100);
	}
}
