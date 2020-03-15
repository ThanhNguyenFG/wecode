<?php

namespace App\Http\Controllers;

use App\Queue_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class queue_controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        
    }
    public function index()
    {
        //
        if ( ! in_array( Auth::user()->role->name, ['admin', 'head_instructor']) )
            abort(404);
            return view('admin.queue', ['queue' => Queue_item::latest()->get()] ); 
        }
    
    public function work(){
        if ( ! in_array( Auth::user()->role->name, ['admin', 'head_instructor']) )
            abort(404);
        // dd('php ' . escapeshellarg(base_path() . '/artisan').  ' work_queue');
        $a = shell_exec('php ' . escapeshellarg(base_path() . '/artisan').  ' work_queue');
        // dd($a);
        return redirect(route('queue.index'));
    }
    
    public function unlock( $item){
        if ( ! in_array( Auth::user()->role->name, ['admin', 'head_instructor']) ) abort(403);
        $item = Queue_item::find($item);
        // dd($item);
        $item->processid = NULL;
        $item->save();
        return redirect(route('queue.index'));
    }
    
    public function empty(){
        if ( ! in_array( Auth::user()->role->name, ['admin', 'head_instructor']) ) abort(403);
        Queue_item::truncate();
        return redirect(route('queue.index'));
    }
    
}
