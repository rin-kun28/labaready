<style>
	.logo {
		margin: auto;
		font-size: 24px;
		background: white;
		padding: 10px 14px;
		border-radius: 50% 50%;
		color: #000000b3;
	}
	
	.laundry-logo {
		width: 40px;
		height: 40px;
	}
	
	.topbar-brand {
		font-size: 1.5rem;
		font-weight: 700;
		background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
		letter-spacing: -0.5px;
	}
	
	.user-dropdown {
		background: white;
		padding: 0.5rem 1.25rem;
		border-radius: 50px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		text-decoration: none;
		color: #334155;
		font-weight: 500;
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
	}
	
	.user-dropdown:hover {
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		transform: translateY(-2px);
		text-decoration: none;
		color: #6366f1;
	}
	
	.user-dropdown i {
		color: #ef4444;
		transition: all 0.3s ease;
	}
	
	.user-dropdown:hover i {
		transform: rotate(90deg);
	}
</style>

<nav class="navbar navbar-light fixed-top" style="padding:0; background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%); backdrop-filter: blur(10px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
  <div class="container-fluid mt-2 mb-2">
  	<div class="col-lg-12">
  		<div class="col-md-1 float-left" style="display: flex;">
  			<div class="logo">
  				<div class="laundry-logo"></div>
  			</div>
  		</div>
      <div class="col-md-4 float-left">
        <span class="topbar-brand">LABA READY LAUNDRY SHOP</span>
      </div>
	  	<div class="col-md-2 float-right">
	  		<form action="ajax.php?action=logout" method="POST" class="m-0">
	  			<input type="hidden" name="csrf_token" value="<?php echo escape(csrf_token()); ?>">
	  			<button type="submit" class="user-dropdown border-0">
	  				<span><?php echo escape($_SESSION['login_name']); ?></span>
	  				<i class="fa fa-power-off"></i>
	  			</button>
	  	 	</form>
	    </div>
    </div>
  </div>
</nav>
