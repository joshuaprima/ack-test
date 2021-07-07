<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use \File;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:users', ['except' => ['login', 'register']]);
    }

    public function register(Request $request) {
        $rules = [
            'name' => 'required|string',
            'username' => 'required|unique:user,username',
            'phone' => 'required',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|confirmed',
            'foto' => 'image'
        ];

        $messages = [
            'name.required' => 'Name must be filled',
            'username.required' => 'Name must be filled',
            'email.required' => 'Email must be filled',
            'email.email' => 'Email invalid',
            'email.unique' => 'Email already exists',
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
            if (!empty($request->foto)){
                $file = $request->file('foto');
                $filename = date('YmdHis').'-'.$request->name.'-'.$file->getClientOriginalName();
                $folder = 'images/user';

                if ($file->move($folder, $filename)){
                    $user = User::create(array_merge(
                        $validator->validated(),
                        [
                            'password' => bcrypt($request->password),
                            'foto' => $folder.'/'.$filename
                        ]
                    ));

                    if ($user){
                        $basic  = new \Vonage\Client\Credentials\Basic("e3d7a938", "zrb9tNUNLsuYBJDz");
                        $client = new \Vonage\Client($basic);

                        $response = $client->sms()->send(
                            new \Vonage\SMS\Message\SMS($request->phone, 'ACK', 'Your account has been registered! Thank you.')
                        );

                        if ($response->current()->getStatus() == 0){
                            return response()->json([
                                'success' => true,
                                'message' => 'User created and message sent!'
                            ],200);
                        }else{
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to send message!'
                            ],$response->current()->getStatus());
                        }
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create user!'
                        ],401);
                    }
                }
            }else{
                $user = User::create(array_merge(
                    $validator->validated(),
                        ['password' => bcrypt($request->password),]
                ));

                if ($user){
                    $basic  = new \Vonage\Client\Credentials\Basic("e3d7a938", "zrb9tNUNLsuYBJDz");
                    $client = new \Vonage\Client($basic);

                    $response = $client->sms()->send(
                        new \Vonage\SMS\Message\SMS($request->phone, 'ACK', 'Your account has been registered! Thank you.')
                    );

                    if ($response->current()->getStatus() == 0){
                        return response()->json([
                            'success' => true,
                            'message' => 'User created and message sent!'
                        ],200);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to send message!'
                        ],$response->current()->getStatus());
                    }
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create user!'
                    ],401);
                }
            }
        }
    }

    public function login(Request $request){
        $rules = [
            'username' => 'required',
            'password' => 'required|string',
        ];

        $messages = [
            'username.required' => 'Name must be filled',
            'password.required' => 'Password must be filled'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Something is wrong!',
                'data' => $validator->errors()
            ],400);
        }else{
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            if (! $token = auth()->attempt(array($fieldType => $request['username'], 'password' => $request['password']))){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized!'
                ],401);
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Login success!',
                    'data' => $this->createNewToken($token)
                ],401);
            }
        }
    }

    public function logout() {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'User successfully signed out'
        ],200);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
