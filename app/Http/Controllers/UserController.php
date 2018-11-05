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
	public function register(){
		 return view('register');
	}
	public function login(){
		 return view('login');
	}
	public function userInfo(){
		 return view('info');
	}
    public function registerRequest(Request $request){
		$messages = array(
			'required' => 'Vui lòng nhập thông tin (*).',
			'numeric' => 'Điện thoại phải dạng số',
			'email' => 'Địa chỉ email không đúng', 
			'confirmed'=>'Nhập lại mật khẩu không chính xác'
		);
		$rules = array(
			'name' => 'required',
			'email'=>'required|email',
			'tel'=>'required|numeric',
			'address'=>'required',
			'password'=>'required|min:6|confirmed',
			'password_confirmation'=>'required|same:password',
		);
		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails())
		{
			return response()->json(['status'=>false,
				'error'=>$validator->errors(), 
				'message'=>'Lỗi! '.$validator->errors()->first()
			]);
		}else{
			
			$user = $this->user->create([
			  'name' => $request->get('name'),
			  'email' => $request->get('email'), 
			  'address' => $request->get('address'),
			  'tel' => $request->get('phone'),
			  'password' => Hash::make($request->get('password'))
			]);
			if($user){
				return response()->json([
					'status'=> true,
					'message'=> 'User created successfully',
					'data'=>$user
				]);
			}else{
				return response()->json([
					'status'=> false,
					'message'=> 'Can not create user',
				]);
			}
		}
    }
    public function loginRequest(Request $request){
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
				'status'=> false,
				'message'=> 'invalid email or password'
				]);
           }
        } catch (JWTAuthException $e) {
			return response()->json([
				'status'=> false,
				'message'=> 'failed to create token'
				]);
        }
		return response()->json([
			'status'=> true,
			'token'=> $token, 
			'message'=>'Đăng nhập thành công! '
			]);
    }

    public function userInfoRequest(Request $request){
        $user = JWTAuth::toUser($request->token); 
		if($user){
			return response()->json([
			'status'=> true,
			'user'=> $user, 
			'message'=>'Lấy thông tin tài khoản thành công! '
			]);
		}else{
			return response()->json([
			'status'=> false,
			'message'=> 'failed! token is required'
			]);
		}
    }
	public function userUpdateRequest(Request $request){
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
			return response()->json(['status'=>false,
				'error'=>$validator->errors(), 
				'message'=>'Lỗi không thể cập nhật. '.$validator->errors()->first()
			]);
		}else{
			$user->name=$request->name; 
			$user->tel=$request->tel; 
			$user->address=$request->address; 
			$user->password=Hash::make($request->password);
			$user->save(); 
			return response()->json(['status'=>true,
				'message'=>'Cập nhật tài khoản thành công! ', 
				'user'=>$user
			]);
		}
    }
}  