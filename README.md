# TỔNG QUAN VỀ CÁC BƯỚC CÀI ĐẶT VÀ SỬ DỤNG WEB API LẤY VÀ CẬP NHẬT THÔNG TIN NGƯỜI DÙNG
---
### Cài đặt LAMP trên Ubuntu 
- Trước khi làm việc ở Ubuntu, bạn nên tiến hành cập nhật gói phần mềm của Ubuntu lên phiên bản mới nhất với lệnh: 
> sudo apt-get update 
- Cài đặt Apache 
> sudo apt-get install apache2 
- Các file lưu trữ được đặt trong thư mục /var/www/html  
> ls /var/www/html  
- Cài đặt PHP
> sudo apt-get install php libapache2-mod-php php-mcrypt  
> sudo service apache2 restart  
- Cài đặt MySQL: trong quá trình cài đặt chương trình sẽ yêu cầu bạn nhập mật khẩu cho user root của MYSQL
> sudo apt-get install mysql-server php-mysql 
- Cài đặt phpMyAdmin: trong quá trình cài đặt chương trình sẽ yêu cầu bạn nhập mật khảu của user root MYSQL mà bạn đã khai báo ở bước trước
> sudo apt-get install phpmyadmin  
> sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf  
> sudo service apache2 restart  
### Cài đặt composer 
> curl -s http://getcomposer.org/installer | php 
- Kiểm tra composer.phar đã được cài đặt thành công chưa? 
> php composer.phar  
> sudo mv composer.phar /usr/bin/composer 
### Cài đặt laravel 
> cd /var/www/html  
> composer create-project --prefer-dist laravel/laravel blog  
- Sau khi cài đặt xong bạn hãy cho các thư mục có thể cần thiết 
> chmod -R 755 /var/www/html/blog  
> chmod -R 777 /var/www/html/blog/storage  
### Cài đặt địa chỉ tên miền 
- Bạn cần trỏ Record A tên miền về địa chỉ ip của server và sau đó thao tác thêm tên miền trong server bằng các lệnh: 
> cd /etc/apache2/sites-available/  
> sudo cp 000-default.conf loginapi.cungcap.net.conf  
> sudo vim loginapi.cungcap.net.conf  
- Thay đổi nội dung trong file loginapi.cungcap.net.conf như sau: 
```
ServerAdmin admin@example.com
DocumentRoot /var/www/html/blog/public
ServerName loginapi.cungcap.net
ServerAlias loginapi.cungcap.net 
```
- Sau đó enable site bằng lệnh 
> sudo a2ensite loginapi.cungcap.net.conf  
> a2enmod rewrite
- reload lại apache 
> sudo service apache2 reload 
### Cài đặt JSON Web Token 
> cd /var/www/html/blog  
> composer require tymon/jwt-auth  
- Cập nhật file config/app.php 
``` 
'providers' => [
	....
	Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
],
'aliases' => [
	....
	'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
], 
```  
- Tiến hành publish file config JWT 
> php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider" 
- Để mã hóa token, chúng ta cần tạo ra secret key: 
> php artisan jwt:generate
- Trong quá trình tạo secret key có thể gặp lỗi ở một số phiên bản, bạn hãy thay đổi trong file vendor\tymon\jwt-auth\src\Commands\JWTGenerateCommand.php và thêm đoạn function 
``` 
public function handle()
{
	$this->fire();
}
``` 
và sau đó chạy lại lệnh 
> php artisan jwt:generate

### Thêm route 
``` 
Route::post('api/auth/register', 'UserController@register');
Route::post('api/auth/login', 'UserController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('api/user-info', 'UserController@getUserInfo'); 
	Route::post('api/user-update', 'UserController@updateInfo');
});
``` 
### Tạo file VerifyJWTToken
> php artisan make:middleware VerifyJWTToken 
- Sử dụng middleware này, bạn có thể lọc các request và validate JWT token 
- Nội dung file app/Http/Middleware/VerifyJWTToken.php 
```
<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::toUser($request->input('token'));
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['token_expired'], $e->getStatusCode());
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['token_invalid'], $e->getStatusCode());
            }else{
                return response()->json(['error'=>'Token is required']);
            }
        }
        return $next($request);
    }
}
``` 
- Khai báo middleware trong Kernel để nó chạy trong tất cả các HTTP request app/Http/Kernel.php
```
protected $routeMiddleware = [
	...
	'jwt.auth' => \App\Http\Middleware\VerifyJWTToken::class,
	...
``` 
### Loại trừ các route không được bảo vệ CSRF trong VerifyCsrfToken 
```
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/*',
    ];
}
```
### Tạo UserController app/Http/Controllers/UserController.php
```
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
``` 
### Tạo database với tên blog 
- bằng cách truy cập vào PhpMyAdmin theo địa chỉ tên miền hoặc địa chỉ ip của bạn. (vd: http://loginapi.cungcap.net/phpmyadmin) 
- đăng nhập và tiến hành tạo một database với tên ***blog*** 
### Cài đặt kết nối cơ sở dữ liệu cho Framework Laravel 
- Mở file .env và thay đổi thông tin cơ sở dữ liệu 
``` 
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=12345678
``` 
### Tạo migrations database cho bảng user 
- migrations user thường có sẵn khi cài đặt Laravel Framework và bạn chỉ cần mở file có sẵn lên và thêm các field cần thiết 
``` 
Schema::create('users', function (Blueprint $table) {
	$table->increments('id');
	$table->string('name');
	$table->string('email')->unique();
	$table->timestamp('email_verified_at')->nullable(); 
	$table->string('address')->nullable(); 
	$table->string('tel')->nullable(); 
	$table->string('password');
	$table->rememberToken();
	$table->timestamps();
});
``` 
- tạo bảng trong CSDL bằng artisan 
> php artisan migrate

## HƯỚNG DẪN SỬ DỤNG 
- Sử dụng chương trình Postman để thử nghiệm bằng cách tải ứng dụng cài đặt tại địa chỉ: [https://www.getpostman.com/apps](https://www.getpostman.com/apps) 

#### Đăng ký tài khoản
- Đăng ký tài khoản mới sử dụng phương thức POST, địa chỉ đăng ký tài khoản [http://loginapi.cungcap.net/api/auth/register](http://loginapi.cungcap.net/api/auth/register) và nhập các trường cần thiết khi tạo mới tài khoản: 
* **name:** Tên tài khoản (trường này có thể thay đổi)
* **email:** Địa chỉ email tài khoản (trường này không thể thay đổi)
* **address:** Địa chỉ người dùng (trường này có thể thay đổi) 
* **tel:** Số điện thoại tài khoản (trường này có thể thay đổi) 
* **password:** Mật khẩu tài khoản (trường này có thể thay đổi) 
Sau khi nhập đầy đủ thông tin vào **Body** và gửi yêu cầu để đăng ký tài khoản mới. 

#### Đăng nhập và lấy thông tin Token
Địa chỉ đăng nhập để lấy thông tin token [http://loginapi.cungcap.net/api/auth/login](http://loginapi.cungcap.net/api/auth/login), nhập địa chỉ email, password vào **Body** và dùng phương thức POST để gửi yêu cầu. Sau khi gửi yêu cầu thành công, hệ thống sẽ trả về thông tin có giá trị của token. 

#### Lấy thông tin tài khoản 
Địa chỉ lấy thông tin tài khoản [http://loginapi.cungcap.net/api/user-info](http://loginapi.cungcap.net/api/user-info), nhập giá trị token vào **Params** đã được lấy ở bước đăng nhập và dùng phương thức GET để gửi yêu cầu lấy thông tin tài khoản. 

#### Cập nhật thông tin tài khoản 
Địa chỉ cập nhật thông tin tài khoản [http://loginapi.cungcap.net/api/user-update](http://loginapi.cungcap.net/api/user-update), nhập các thông tin yêu cầu cần thiết: 
* **token:** nhập giá trị token 
* **name:** Nhập mới tên tài khoản 
* **address:** Nhập mới địa chỉ tài khoản 
* **tel:** Nhập mới số điện thoại tài khoản 
* **password:** Nhập mật khẩu mới 
* **password_confirmation:** Nhập lại mật khẩu mới 
Sau khi điền đầy đủ thông tin vào **Body** và sử dụng phương thức POST để gửi yêu cầu cập nhật thông tin tài khoản. 

# Kết luận 
> Trên đây là các bước để tạo một ứng dụng web API cho phép đăng ký, đăng nhập, lấy thông tin tài khoản và cập nhật thông tin tài khoản bảo mật bằng Token và sử dụng các chương trình: 
* Server Ubuntu linux 
* PHP 7.0.32 
* Mysql  Ver 14.14 
* Laravel Framework 5.5 
> Web Api sử dụng thư viện Json Web Token để bảo mật tạo token khi lấy thông tin và cập nhật thông tin tài khoản. 
