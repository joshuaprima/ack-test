<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use \Validator;
use Illuminate\Support\Facades\Auth;
use \File;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:users');
    }

    public function getUserProfile(){
        $id = auth()->id();
        $data = User::find($id);

        return response()->json([
           'success' => true,
           'message' => 'User profile obtained!',
           'data' => $data
        ],200);
    }

    public function editProfile(Request $request){
        $rules = [
            'name' => 'required|string',
            'phone' => 'required',
            'password' => 'required|confirmed',
            'foto' => 'image'
        ];

        $messages = [
            'name.required' => 'Name must be filled',
            'phone.required' => 'Phone number must be filled',
            'password.required' => 'Password must be filled',
            'password.confirmed'=> 'Password is not the same as confirm password',
            'foto.image' => 'Invalid type of file'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Something is wrong!',
                'data' => $validator->errors()
            ],400);
        }else{
            $id = auth()->id();
            $data = User::find($id);

            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->password = bcrypt($request->password);

           if (!empty($request->foto)){
               $file = $request->file('foto');
               $filename = date('YmdHis').'-'.$request->name.'-'.$file->getClientOriginalName();
               $folder = 'images/user';

               if ($file->move($folder, $filename)){
                   File::delete($data->foto);

                   $data->foto = $folder.'/'.$filename;
                   if ($data->save()){
                       return response()->json([
                          'success' => true,
                          'message' => 'Profile updated!',
                          'data' => $data
                       ],200);
                   }else{
                       return response()->json([
                           'success' => false,
                           'message' => 'Failed to update profile!',
                       ],401);
                   }
               }
           }else{
               if ($data->save()){
                   return response()->json([
                       'success' => true,
                       'message' => 'Profile updated!',
                       'data' => $data
                   ],200);
               }else{
                   return response()->json([
                       'success' => false,
                       'message' => 'Failed to update profile!',
                   ],401);
               }
           }
        }
    }
}
