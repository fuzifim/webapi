@extends('layout')
@section('title', 'Login')
@section('content')
<div class="row">
	<div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
		<div class="card card-signin my-5">
			<div class="card-body">
				<h5 class="card-title text-center">Đăng nhập</h5>
				<form class="form-signin" id="formLogin" method="post" action="{{route('login.request')}}">
					<div id="appendToken"></div>
					<div class="form-group">
						<input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required="" autofocus="">
					</div>
					<div class="form-group">
						<input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required="">
					</div>
					<div class="custom-control custom-checkbox mb-3">
						<input type="checkbox" class="custom-control-input" id="customCheck1">
						<label class="custom-control-label" for="customCheck1">Remember password</label>
					</div>
					<button class="btn btn-lg btn-primary btn-block text-uppercase" id="btnSubmit" type="submit">Sign in</button>
					<hr class="my-4"> Nếu chưa có tài khoản, bạn hãy <a href="{{route('register')}}">đăng ký</a>
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
	$("#formLogin").on("click","#btnSubmit",function() {
		var $valid = jQuery("#formLogin").valid();
		if(!$valid) {
			$validator.focusInvalid();
			return false;
		}else{
			$(this).addClass( "disabled"); 
			$(this).prop("disabled",true); 
			$("#formLogin :input").prop("disabled", true);
			var formData = new FormData();
			formData.append("email",$("input[name=email]").val()); 
			formData.append("password",$("input[name=password]").val()); 
			$.ajax({
				url: "{{route('login.request')}}",
				headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
				type: "post",
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				dataType:"json",
				success:function(result){ 
					if(result.status==true){
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-success",
							sticky: false,
							time: ""
						}); 
						$("#appendToken").append("<div class=\"form-group\"><strong>Token của bạn là: </strong><textarea class=\"form-control\" rows=\"5\">"+result.token+"</textarea></div>"); 
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
						$("#formLogin :input").prop("disabled", false);
					}
						
				},
				error: function(result) {
					jQuery.gritter.add({
						title: "Thông báo!",
						text: "Không thể đăng đăng nhập, vui lòng thử lại!", 
						class_name: "growl-danger",
						sticky: false,
						time: ""
					});
					$("#btnSubmit").removeClass("disabled"); 
					$("#btnSubmit").prop("disabled",false); 
					$("#formLogin :input").prop("disabled", false);
				}
			}); 
			return false; 
		}
	}); 
</script>
@endsection