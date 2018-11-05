@extends('layout')
@section('title', 'Register')
@section('content')
    <div class="row">
		<div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
			<div class="card card-signin my-5">
				<div class="card-body">
					<h5 class="card-title text-center">Đăng ký tài khoản</h5>
					<form class="form-signin" id="formRegister" method="post" action="{{route('register.request')}}">
						<div class="form-group">
							<input type="text" id="inputName" name="name" class="form-control" placeholder="Tên đầy đủ" required="" autofocus="">
						</div>
						<div class="form-group">
							<input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required="" autofocus="">
						</div>
						<div class="form-group">
							<input type="phone" id="inputPhone" name="phone" class="form-control" placeholder="Số điện thoại" required="" autofocus="">
						</div>
						<div class="form-group">
							<input type="text" id="inputAddress" name="address" class="form-control" placeholder="Địa chỉ" required="" autofocus="">
						</div>
						<div class="form-group">
							<input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required="">
						</div>
						<div class="form-group">
							<input type="password" id="inputRePassword" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required="">
						</div>
						<div class="custom-control custom-checkbox mb-3">
							<input type="checkbox" class="custom-control-input" id="customCheck1">
							<label class="custom-control-label" for="customCheck1">Đồng ý với các điều khoản</label>
						</div>
						<button class="btn btn-lg btn-success btn-block text-uppercase" id="btnSubmit" type="submit">Register</button>
						<hr class="my-4"> Nếu đã có tài khoản, bạn hãy <a href="{{route('login')}}">đăng nhập</a>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('css')
<link media="all" type="text/css" rel="stylesheet" href="https://cungcap.net/themes/main/assets/library/gritter/jquery.gritter.css">
@endsection
@section('script')
<script src="https://cungcap.net/themes/main/assets/library/gritter/jquery.gritter.min.js"></script>
<script src="https://cungcap.net/themes/main/assets/js/jquery.validate.min.js"></script>
<script>
	$("#formRegister").on("click","#btnSubmit",function() {
		var $valid = jQuery("#formRegister").valid();
		if(!$valid) {
			$validator.focusInvalid();
			return false;
		}else{
			$(this).addClass( "disabled"); 
			$(this).prop("disabled",true); 
			$("#formRegister :input").prop("disabled", true);
			var formData = new FormData();
			formData.append("name",$("input[name=name]").val()); 
			formData.append("email",$("input[name=email]").val()); 
			formData.append("tel",$("input[name=phone]").val()); 
			formData.append("address",$("input[name=address]").val()); 
			formData.append("password",$("input[name=password]").val()); 
			formData.append("password_confirmation",$("input[name=password_confirmation]").val()); 
			$.ajax({
				url: "{{route('register.request')}}",
				headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
				type: "post",
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				dataType:"json",
				success:function(result){ 
					console.log(result); 
					if(result.status==true){
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-success",
							sticky: false,
							time: ""
						});
					}else{
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#btnSubmit").removeClass("disabled"); 
						$("#btnSubmit").prop("disabled",false); 
						$("#formRegister :input").prop("disabled", false);
					}
						
				},
				error: function(result) {
					jQuery.gritter.add({
						title: "Thông báo!",
						text: "Không thể đăng ký tài khoản, vui lòng thử lại!", 
						class_name: "growl-danger",
						sticky: false,
						time: ""
					});
					$("#btnSubmit").removeClass("disabled"); 
					$("#btnSubmit").prop("disabled",false); 
					$("#formRegister :input").prop("disabled", false);
				}
			}); 
			return false; 
		}
	}); 
</script>
@endsection