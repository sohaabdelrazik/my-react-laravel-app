<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Employee;
class EmployeeController extends Controller
{
    protected $comment;
    public function __construct(){
        $this->comment = new CommentsController();
        
    }
    public function index()
    {
        return $this->comment->all();
     
    }
    
    public function store(Request $request)
    {
     return $this->comment->create($request->all());
    
       
    }
  
    public function show(string $id)
    {
     $comment = $this->comment->find($id);  
    }

    public function update(Request $request, string $id)
    {
         $student = $this->student->find($id);
         $student->update($request->all());
         return $student;
    }
    public function destroy(string $id)
    {
     $student = $this->student->find($id);
    return $student->delete();   
    }
}

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(comment $comment)
    {
        //
    }
}
