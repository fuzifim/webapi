<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use JWTAuthException;
use Hash;
use Validator; 
class UserController extends Controller
{   
    private $user;

    public function __construct(User $user){
        $this->user = $user;
    }
   
    public function register(Request $request){
        $user = $this->user->create([
          'name' => $request->get('name'),
          'email' => $request->get('email'), 
		  'address' => $request->get('address'),
		  'tel' => $request->get('tel'),
          'password' => Hash::make($request->get('password'))
        ]);

        return response()->json([
            'status'=> 200,
            'message'=> 'User created successfully',
            'data'=>$user
        ]);
    }
    
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['invalid_email_or_password'], 422);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function getUserInfo(Request $request){
        $user = JWTAuth::toUser($request->token);
        return response()->json(['result' => $user]);
    }
	public function updateInfo(Request $request){
        $user = JWTAuth::toUser($request->token); 
		$messages = array(
			'required' => 'Vui lòng nhập thông tin (*).',
			'numeric' => 'Điện thoại phải dạng số',
			'confirmed'=>'Nhập lại mật khẩu không chính xác'
		);
		$rules = array(
			'name' => 'required',
			'tel'=>'required|numeric',
			'address'=>'required',
			'password'=>'required|min:6|confirmed',
			'password_confirmation'=>'required|same:password',
		);
		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails())
		{
			return response()->json(['success'=>false,
				'message'=>$validator->errors()
			]);
		}else{
			$user->name=$request->name; 
			$user->tel=$request->tel; 
			$user->address=$request->address; 
			$user->password=Hash::make($request->password);
			$user->save(); 
			return response()->json(['result' => $user]);
		}
    }
}  