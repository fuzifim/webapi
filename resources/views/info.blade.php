@extends('layout')
@section('title', 'Login')
@section('content')
<div class="row">
	<div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
		<div class="card card-signin my-5">
			<div class="card-body">
				<h5 class="card-title text-center">Lấy thông tin tài khoản</h5>
				<form class="form-signin" id="formGetInfo" method="post" action="{{route('user.info.request')}}">
					<div class="form-group">
						<label for="token">Nhập token của bạn để lấy thông tin tài khoản</label>
						<textarea class="form-control" name="token" id="token" rows="5"></textarea>
					</div>
					<div id="appendUserInfo"></div>
					<button class="btn btn-lg btn-primary btn-block text-uppercase" id="btnSubmit" type="submit">Get User Info</button>
					<hr class="my-4"> Nếu chưa có token hãy <a href="{{route('login')}}">đăng nhập</a>
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
	$("#formGetInfo").on("click","#btnSubmit",function() {
		var $valid = jQuery("#formGetInfo").valid();
		if(!$valid) {
			$validator.focusInvalid();
			return false;
		}else{
			$(this).addClass( "disabled"); 
			$(this).prop("disabled",true); 
			$("#formGetInfo :input").prop("disabled", true);
			var formData = new FormData();
			formData.append("token",$("textarea[name=token]").val()); 
			$.ajax({
				url: "{{route('user.info.request')}}",
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
						$("#appendUserInfo").addClass("form-group"); 
						jQuery(result.user).each(function(i, item){
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Email: </label>"
								+"<input type=\"text\" name='email' class='form-control' value='"+item.email+"' readonly='readonly' disabled>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Name: </label>"
								+"<input type=\"text\" name='name' class='form-control' value='"+item.name+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Address: </label>"
								+"<input type=\"text\" name='address' class='form-control' value='"+item.address+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Tel: </label>"
								+"<input type=\"text\" name='tel' class='form-control' value='"+item.tel+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Mật khẩu: </label>"
								+"<input type=\"password\" name='password' class='form-control' value='' placeholder='Nhập mật khẩu cần thay đổi' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Nhập lại mật khẩu: </label>"
								+"<input type=\"password\" name='password_confirmation' class='form-control' placeholder='Nhập lại mật khẩu' value='' required>"
							+"</div>");  
						}); 
						$(".card-title").text("Cập nhật thông tin tài khoản"); 
						$("#btnSubmit").removeClass("disabled"); 
						$("#btnSubmit").prop("disabled",false); 
						$("#btnSubmit").html('Update User');
						$("#btnSubmit").attr('id', 'btnUpdate'); 
					}else{
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.error, 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#btnSubmit").removeClass("disabled"); 
						$("#btnSubmit").prop("disabled",false); 
						$("#formGetInfo :input").prop("disabled", false);
					}
						
				},
				error: function(result) {
					jQuery.gritter.add({
						title: "Thông báo!",
						text: "Không thể lấy thông tin, vui lòng thử lại!", 
						class_name: "growl-danger",
						sticky: false,
						time: ""
					});
					$("#btnSubmit").removeClass("disabled"); 
					$("#btnSubmit").prop("disabled",false); 
					$("#formGetInfo :input").prop("disabled", false);
				}
			}); 
			return false; 
		}
	}); 
	$("#formGetInfo").on("click","#btnUpdate",function() {
		var $valid = jQuery("#formGetInfo").valid();
		if(!$valid) {
			$validator.focusInvalid();
			return false;
		}else{
			$(this).addClass( "disabled"); 
			$(this).prop("disabled",true); 
			$("#formGetInfo :input").prop("disabled", true);
			var formData = new FormData();
			formData.append("token",$("textarea[name=token]").val()); 
			formData.append("name",$("#appendUserInfo input[name=name]").val()); 
			formData.append("address",$("#appendUserInfo input[name=address]").val()); 
			formData.append("tel",$("#appendUserInfo input[name=tel]").val()); 
			formData.append("password",$("#appendUserInfo input[name=password]").val()); 
			formData.append("password_confirmation",$("#appendUserInfo input[name=password_confirmation]").val()); 
			$.ajax({
				url: "{{route('user.update.request')}}",
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
						$("#appendUserInfo").empty(); 
						$("#appendUserInfo").addClass("form-group"); 
						jQuery(result.user).each(function(i, item){
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Email: </label>"
								+"<input type=\"text\" name='email' class='form-control' value='"+item.email+"' readonly='readonly' disabled>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Name: </label>"
								+"<input type=\"text\" name='name' class='form-control' value='"+item.name+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Address: </label>"
								+"<input type=\"text\" name='address' class='form-control' value='"+item.address+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Tel: </label>"
								+"<input type=\"text\" name='tel' class='form-control' value='"+item.tel+"' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Mật khẩu: </label>"
								+"<input type=\"password\" name='password' class='form-control' value='' placeholder='Nhập mật khẩu cần thay đổi' required>"
							+"</div>"); 
							$("#appendUserInfo").append("<div class=\"form-group\"><label>Nhập lại mật khẩu: </label>"
								+"<input type=\"password\" name='password_confirmation' class='form-control' placeholder='Nhập lại mật khẩu' value='' required>"
							+"</div>"); 
						}); 
						$(".card-title").text("Cập nhật thông tin tài khoản"); 
						$("#btnUpdate").removeClass("disabled"); 
						$("#btnUpdate").prop("disabled",false); 
					}else{
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#btnUpdate").removeClass("disabled"); 
						$("#btnUpdate").prop("disabled",false); 
						$("#formGetInfo :input").prop("disabled", false);
					}
						
				},
				error: function(result) {
					jQuery.gritter.add({
						title: "Thông báo!",
						text: "Không thể lấy thông tin, vui lòng thử lại!", 
						class_name: "growl-danger",
						sticky: false,
						time: ""
					});
					$("#btnUpdate").removeClass("disabled"); 
					$("#btnUpdate").prop("disabled",false); 
					$("#formGetInfo :input").prop("disabled", false);
				}
			}); 
			return false; 
		}
	}); 
</script>
@endsection