<?php

namespace App\Http\Controllers;
use App\Models\Comment;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Event;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     public function blocked($charityId,$userId){
        return DB::table('blockings')
        ->where('user_id',$userId)
        ->where('charity_id',$charityId)
        ->exists();

    }
     public function store(Request $request)
    {    
        $charity=auth('charity')->user();
        $user=auth('user')->user();
        if(!$charity&&!$user){
            return response()->json(['error'=>'Unauthorized'],422);
        }
        elseif($charity&&$user){
            return response()->json(['error'=>'overwrite'],422);
        }
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['errors' => 'No token provided'], 401);}
       
        $validator=Validator::make($request->all(),[
            'content'=>'string',
            'event_id'=>'exists:events,id',

        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],422);
        }
        if($charity){
        $comment=Comment::create([
            'charity_id'=>$charity->id,
            'charity_name'=>$charity->name,
            'content'=>$request->content,
            'event_id'=>$request->event_id,
        ]);
        return response()->json(['message'=>'comment created successfully','comment'=>$comment->makeHidden(['charity_id','event_id','id','user_id','user_name','updated_at'])],200);
    }
        if($user){
            $event=Event::where('id',$request->event_id)
            ->first();
            $block=$this->blocked($event->charity_id,$user->id);
            if($block){
                return response()->json(['message'=>'access denial'],403);
        
            }
        $comment=Comment::create([
            'user_id'=>$user->id,
            'user_name'=>$user->name,
            'content'=>$request->content,
            'event_id'=>$request->event_id,
        ]);
        return response()->json(['message'=>'comment created successfully','comment'=>$comment->makeHidden(['user_id','event_id','id','charity_name','charity_id','updated_at'])],200);
    }
    return response()->json(['error'=>'unauthorized'],422);
}
    public function showAll($eventId){
        $user=auth('user')->user();
        $charity=auth('charity')->user();
        if(!$charity&&!$user){
            return response()->json(['error'=>'unauthorized'],422);
        }
        $comments=Comment::where('event_id',$eventId)->orderByDesc('id')
        ->get(['charity_name','user_name','content','created_at']);
        //to know it's charity or user and choice which one the name will be
       $commentList=$comments->map(fn($comment)=>
        [
            'name'=>$comment->charity_name ??$comment->user_name,
            'content'=>$comment->content,
            'created_at'=>$comment->created_at
        ]
       );
        return response()->json(['message'=>'all comments for this event are',
        'comments'=>$commentList],201);
    }

    public function show($id){
        $charity=auth('charity')->user();
        $user=auth('user')->user();

        if(!$charity&&!$user){
            return response()->json(['error'=>'unauthorized'],422);
        }
        else if($charity&&$user){
            return response()->json(['error'=>'overwrite'],422);
        }
        $comment=Comment::find($id);
        if(!$comment){
            return response()->json(['error'=>'comment not found'],404);

        }
        return response()->json([$comment],201);

    }
    public function update(Request $request,$id){
        $charity=auth('charity')->user();
        $user=auth('user')->user();
        if(!$user&&!$charity){
            return response()->json(['error'=>'unauthorized'],422);
        }
        $comment=Comment::find($id);
        if(!$comment){
            return response()->json(['error'=>'comment not found'],422);

        }
        if($user&&$comment->user_id!=$user->id){
            return response()->json(['error'=>'not your comment'],422);

        }
        if($charity&&$comment->charity_id!=$charity->id){
            return response()->json(['error'=>'not your comment'],422);

        }
        $request->validate([
            "content"=>"string|required"
        ]);
        $comment->update([
            "content"=>$request->content
        ]);
        return response()->json(['message'=>'comment updated successfully']);
    }
    public function destroy($id)
    {
        $charity=auth('charity')->user();
        $user=auth('user')->user();
        if(!$charity&&!$user){
            return response()->json(['error'=>'unauthorized'],402);
        }
           $comment=Comment::find($id);
           if(!$comment){
            return response()->json(['error'=>'comment not found'],404);
           }
           if(!$user){
           if($comment->charity_id!=$charity->id){
            return response()->json(['error'=>'cannot delete the comment'],403);
           }
        }
           if(!$charity){
            if($comment->user_id!=$user->id){
             return response()->json(['error'=>'cannot delete the comment'],403);
            }}
           $comment->delete();
            return response()->json(['message'=>'comment deleted successfully'],201);
        
    }

}