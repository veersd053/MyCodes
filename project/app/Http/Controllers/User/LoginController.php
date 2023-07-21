<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'userLogout']]);
    }
   
    public function login(Request $request)
    {
        //--- Validation Section
        $rules = [
                  'email'   => 'required|email',
                  'password' => 'required'
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
    
        
      // Attempt to log the user in
      if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
        if( $request->is('api/*')) {  
          $user = User::where('email',$request->email)->first();
          $token = $user->createToken('my-app-token')->plainTextToken;
          $response = ["user"=>$user,"token"=>$token];
          return response()->json($response);
        }
          // Login Via Modal
          if($request->modal == 1)
          {
          return response()->json(url()->previous());          
          }


        // if successful, then redirect to their intended location
        return response()->json(route('user.dashboard'));
      }

      // if unsuccessful, then redirect back to the login with the form data
          return response()->json(array('errors' => [ 0 => 'Credentials Doesn\'t Match !' ]));     
    }


    public function forgot(Request $request)
    {
      $gs = Generalsetting::findOrFail(1);
      $input =  $request->all();
      if (Admin::where('email', '=', $request->email)->count() > 0) {
      // user found
      $admin = Admin::where('email', '=', $request->email)->firstOrFail();
      $autopass = Str::random(8);
      $input['password'] = bcrypt($autopass);
      $admin->update($input);
      $subject = "Reset Password Request";
      $msg = "Your New Password is : ".$autopass;
      if($gs->is_smtp == 1)
      {
          $data = [
                  'to' => $request->email,
                  'subject' => $subject,
                  'body' => $msg,
          ];

          $mailer = new GeniusMailer();
          $mailer->sendCustomMail($data);                
      }
      else
      {
          $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
          mail($request->email,$subject,$msg,$headers);            
      }
      return response()->json('Your Password Reseted Successfully. Please Check your email for new Password.');
      }
      else{
      // user not found
      return response()->json(array('errors' => [ 0 => 'No Account Found With This Email.' ]));    
      }  
    }

    public function logout(Request $request)
    {
      if( $request->is('api/*')) {  
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json(['success'=>"Logged out successfully"]);
      }
      Auth::logout();
      return redirect('/');
    }
}
