<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create(Request $request) {

        $rules = [ 
            'name' => ['required'],
            'email' => ['required', 'unique:users,email', 'email'],
            'password' => ['required'],
            'confirm_password' => ['required', 'same:password']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], Response::HTTP_OK);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = password_hash($password, PASSWORD_DEFAULT);

        $newUser->save();

        $item = rand(0,9999).time().rand(0,9999);
        $token = $newUser->createToken($item)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $newUser
        ]);
    }

    public function login(Request $request){

       
        $rules = [ 
            'email' => ['required', 'email'],
            'password' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $creds = $request->only('email', 'password');

        if(Auth::attempt($creds)){
            $user = User::where('email', $creds['email'])->first();
            $item = rand(0,9999).time().rand(0,9999);
            $token = $user->createToken($item)->plainTextToken;
        }else {
            return response()->json([
                'error' => "E-mail e/ou senha incorretos."
            ]);
        }

        return response()->json([
            'error' => '',
            'token' => $token,
        ]);
    }

    public function unauthorized() {
        return response()->json([
            'erro' => 'deu ruim'
        ]);
    }

    public function logout(Request $request) {

        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'success' => 'Deslogado com sucesso.'
        ]);
    }

    public function validaToken($token) {

    }
}
