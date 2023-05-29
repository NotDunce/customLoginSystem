<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CustomAuthController extends Controller
{
    //

    public function login(){
        return view("auth.login");
    }

    public function registration(){
        return view("auth.registration");
    }
    
    public function registerUser(Request $request){
        $request->validate([
            'name' => 'required|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|max:20',
            'date' => 'required|date|before:-18 years'
        ]);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->date = $request->date;
        $res = $user->save();
        if($res){
            $request->session()->put('loginId',$user->id);
            return redirect('dashboard');
        }else{
            return back()->with('error', 'Something went wrong.');
        }
    }

    public function loginUser(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5|max:20',
        ]);

        $user = User::where('email', '=', $request->email)->first();

        if($user){
            if(Hash::check($request->password,$user->password)){
                $request->session()->put('loginId',$user->id);
                return redirect('dashboard');
            }
            else{
                return back()->with('fail', 'Entered password does not match. Try again.');
            }
        }else{
            return back()->with('fail', 'This email is not registered');
        }
    }

    public function dashboard(){

        $data = array();
        if(Session::has('loginId')){
            $data = User::where('id', '=', Session::get('loginId'))->first();
        }

        return view('auth.dashboard',compact('data'));
    }

    public function logout(){
        if(Session::has('loginId')){
            Session::pull('loginId');
            return redirect('login');
        }
    }
}
