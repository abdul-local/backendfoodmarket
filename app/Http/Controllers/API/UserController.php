<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;
use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller

{
    use PasswordValidationRules;

    public function login(Request $request){

        try {
           
            $request->validate([
                'email'=>'email|required',
                'password'=>'password|required'
            ]);
            // cek credintial loginnya 
            $credentials=request(['email', 'password']);
            if (!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message'=>'unautorized'
                ],
                 'Authentication Failed',500
                
                
            );
            }

            // jika hash tidak sesui makan berikan erro
            $user=User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])){
                throw new \Exception(' invalid credential');

            }
            // jika berhasil login maka berikan token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ],'Authenticated');

        } catch (Exception $error ) {

            ResponseFormatter::error([
                'message'=>'something wrong',
                'error'=>$error
            ],'Authentication Failed',500);
        }

    }
     public function register(Request $request){
        try {

            $request->validate([
                'name'=> ['required','string','max:255'],
                'email'=> ['required','string','email','max:255','unique:users'],
                'password'=>$this->passwordRules()

            ]);
            User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'houseNumber'=>$request->houseNumber,
                'phoneNumber'=>$request->phoneNumber,
                'city'=>$request->city,
                'address'=>$request->address

            ]);
            $user=User::where('email', $request->email)->first();

            $tokenResult=$user->crateToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ],'Authenticated');



            //code...
        } catch (Exception $error) {
            ResponseFormatter::error([
                'message'=>'something wrong',
                'error'=>$error
            ],'Authentication Failed',500);
            
            
        }
    }
    public function logout(Request $request){
        $token=$request->user()->currentAccesToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');


    }
    public function fetch(Request $request){

        return ResponseFormatter::success($request->user(),'Data profile user berhasil diambil');

    }

    public function updateProfile(Request $request){

            $data=$request->all();
            $user=Auth::user();
            $user->update($data);
            return ResponseFormatter::success($user, 'Profile updated');

    }
    public function updatePhoto(Request $request){
        $validator =Validator::make($request->all(),[
            'file'=>'required|image|max:2048'

        ]);
        if ($validator->fails()){
            return ResponseFormatter::error([
                'error'=>$validator->errors()
                

            ],'update photo fails', 401);
        }
        if ($request->file('file')){
            $file=$request->file->store('assets/user', 'public');

            // simpan url ke db
            $user=Auth::user();
            $user->profile_photo_path = $file;
            $user->update();
            return ResponseFormatter::success([$file], 'file successFully update profile');
        }

    }
}