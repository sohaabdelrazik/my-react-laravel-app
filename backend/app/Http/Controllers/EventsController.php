<?php

namespace App\Http\Controllers;
use App\Models\Charity;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allEvents()
{
    $charity=auth('charity')->user();
    $user=auth('user')->user();
    if(!$user&&!$charity){
        return response()->json(['error'=>'unauthorized'],401);
    }
    $events = Event::orderByDesc('id')->get()->makeHidden(['id','charity_id']);
    return response()->json(["events"=>$events],201);
}
public function eventsByCharityName($charityName)
{
    $charity = Charity::where('name', $charityName)->first();

    if (!$charity) {
        return response()->json(['error' => 'Charity not found'], 404);
    }

    $events = Event::where('charity_id', $charity->id)->orderByDesc('id')->get();

    return response()->json(["events"=>$events->makeHidden(['id','charity_id'])],201);
}
    public function myEvents()
{
    $charity = auth('charity')->user();

    if (!$charity) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $events = Event::where('charity_id', $charity->id)->orderByDesc('id')->get();

    return response()->json($events->makeHidden(['id','charity_id']));
}   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    
        $charity=auth('charity')->user();
        if(!$charity){
            return response()->json(['error'=>'Unauthorized'],422);
        }
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['errors' => 'No token provided'], 401);}
       
        $validator=Validator::make($request->all(),[
            'title'=>'string|required|max:250',
            'description'=>'string|required|max:1000',
            'due_date'=>'date|required',
            'priority'=>'nullable|in:Low,Medium,High',
            'category'=>'string|required|max:255',
            'location'=>'string|nullable'
            
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],422);
        }
        
        $event=Event::create([
            'charity_id'=>$charity->id,
            'charity_name'=>$charity->name,
            'title'=>$request->title,
            'description'=>$request->description,
            'due_date'=>$request->due_date,
            'priority'=>$request->priority,
            'category'=>$request->category,
            'location'=>$request->location
        ]);
        return response()->json(['message'=>'Event created successfully','event'=>$event->makeHidden(['charity_id'])],200);
    }
    public function listInterestedEvents()
    {
        $user=auth('user')->user();
        if(!$user){
            return response()->json(['error'=>'no token provided or expired'],401);
        }
        $userEvents=DB::table('event_user')->where('user_id',$user->id)
        ->where('state','interested')
        ->pluck('event_id');
        $eventsList=Event::whereIn('id',$userEvents)->orderByDesc('id')->get();
        return response()->json($eventsList->makeHidden(['id','charity_id']));
    }
//going events in going_events_page in user dashboard
    public function listGoingEvents()
    {
        $user = auth('user')->user();
        if(!$user){
            return response()->json(['error'=>'no token provided or expired'],401);
        }
        $userEvents=DB::table('event_user')->where('user_id',$user->id)
        ->where('state','going_to')->pluck('event_id');
        $eventList=Event::whereIn('id',$userEvents)->orderByDesc('id')->get();
        return response()->json($eventList->makeHidden(['id','charity_id']));       
    }
    public function listGoingUsers($eventId){
        $charity=auth('charity')->user();
        if(!$charity){
            return response()->json(['error'=>'unauthorized'],401);
        }
        $userIdList=DB::table('event_user')->where('event_id',$eventId)->pluck('user_id');
        $user=User::whereIn('id',$userIdList)->pluck('name');
        return response()->json($user);
    }
    public function updateUserRate($userId){
        $verifiedEvents=DB::table('event_user_verified')
        ->where('user_id',$userId)
        ->where('state','verified')
        ->count();
        $goingToEvents=DB::table('event_user')
        ->where('user_id',$userId)
        ->where('state','going_to')
        ->count();
        $rate=($goingToEvents==0)?0:($verifiedEvents/$goingToEvents)*100;
        User::where('id',$userId)->update(['rate'=>$rate]);
        return response()->json(['message'=>'user rate updated'],200);
    }
    public function  markUserInterestedEvent($eventId){
        $event=Event::where('id',$eventId)->first();
        if(!$event)
        {
           return response()->json(['error'=>'event not exist'],404);
        }
        $userId=auth('user')->id();
        DB::table('event_user')->updateOrInsert(['user_id'=>$userId,'event_id'=>$event->id],
        ['state'=>'interested']);
        return response()->json(['message'=>'Event marked as interested'],200);
    }
    public function markUserGoingToEvent($eventId)
    {    $user=auth('user')->user();
        if(!$user){
            return response()->json(['error'=>'no token provided or expired'],401);
        }
         $event=Event::where('id',$eventId)->first();
        if(!$event)
        {
           return response()->json(['error'=>'event not exist'],404);
        }
        $userId=auth('user')->id();
        // Insert into pivot table
        DB::table('event_user')->updateOrInsert(
            ['user_id' => $userId, 'event_id' => $event->id],
            ['state' => 'going_to']
        );

        // Update user rate
        $this->updateUserRate($userId);

        return response()->json(['message' => 'User marked as going']);
    }

    // Function for verifying user attendance
    public function verifyUserAttendance(Request $request)
    {       
        $userId=auth('user')->id();
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        // Insert into event_user_verified pivot table
        DB::table('event_user_verified')->updateOrInsert(
            ['user_id' => $userId, 'event_id' => $request->event_id],
            ['state' => 'verified']
        );

        // Update user rate
        $this->updateUserRate($userId);

        return response()->json(['message' => 'User attendance verified']);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $charity=auth('charity')->user();
        if(!$charity){
            return response()->json(['error'=>'unauthorized'],402);
        }
           $event=Event::find($id);
           if(!$event){
            return response()->json(['error'=>'event not found'],404);
           }
           if($event->charity_id!=$charity->id){
            return response()->json(['error'=>'cannot delete the event'],403);
           }
           $event->delete();
            return response()->json(['message'=>'event deleted successfully'],201);
        
    }
}
