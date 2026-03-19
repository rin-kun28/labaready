<?php
require_once __DIR__ . '/security.php';
secure_session_start();
enforce_session_security(true);

if (isset($_SESSION['login_id'])) {
    header('location:index.php?page=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin | Laundry Management System</title>
 	

<?php include('./header.php'); ?>
<?php include('./db_connect.php'); ?>

</head>
<style>
	body{
		width: 100vw;
	    height: 100vh;
	    min-height: 100vh;
	    background-image: url('assets/img/yawa.ico');
	    background-size: auto; /* Show original size */
	    background-repeat: repeat; /* Repeat the image */
	    background-position: top left;
	    margin: 0;
	    padding: 0;
	    display: flex;
	    justify-content: center;
	    align-items: center;
	}
	#login-center-wrapper {
		width: 100vw;
		height: 100vh;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.card {
		background: #fff;
		border-radius: 8px;
		box-shadow: 0 2px 16px rgba(0,0,0,0.13);
		padding: 2rem 2.5rem;
		width: 100%;
		max-width: 400px;
		margin: 0 auto;
	}
</style>

<body>
  <div id="login-center-wrapper">
    <div class="card">
      <div class="card-body">
        <form id="login-form" >
          <input type="hidden" name="csrf_token" value="<?php echo escape(csrf_token()); ?>">
          <div class="form-group">
            <label for="username" class="control-label">Username</label>
            <input type="text" id="username" name="username" class="form-control">
          </div>
          <div class="form-group">
            <label for="password" class="control-label">Password</label>
            <input type="password" id="password" name="password" class="form-control">
          </div>
          <center><button class="btn-sm btn-block btn-wave col-md-4 btn-primary">Login</button></center>
        </form>
      </div>
    </div>
  </div>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<script>
	$('#login-form').submit(function(e){
		e.preventDefault()
		const $button = $('#login-form button[type="submit"]');
		$button.attr('disabled',true).html('Logging in...');
		if($(this).find('.alert-danger').length > 0 )
			$(this).find('.alert-danger').remove();
		$.ajax({
			url:'ajax.php?action=login',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
				const message = err?.responseText || 'Login failed. Please try again.';
				$('#login-form').prepend('<div class="alert alert-danger">'+ message +'</div>')
				$button.removeAttr('disabled').html('Login');

			},
			success:function(resp){
				if(resp == 1){
					location.href ='index.php?page=home';
				}else if(resp == 2){
					location.href ='voting.php';
				}else{
					$('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>')
					$button.removeAttr('disabled').html('Login');
				}
			}
		})
	})
</script>	
</html>
