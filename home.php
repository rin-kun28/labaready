<style>
    .alert-success {
        background: linear-gradient(135deg, #7FA1C3, #009efd);
        color: white;
        border: 2px solid #7FA1C3;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(127, 161, 195, 0.35);
    }
    .alert-info {
        background: linear-gradient(135deg, #7FA1C3, #3a7bd5);
        color: white;
        border: 2px solid #7FA1C3;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(127, 161, 195, 0.3);
    }
    .alert-primary {
        background: linear-gradient(135deg, #a8e6cf, #7FA1C3);
        color: #005b96;
        border: 2px solid #7FA1C3;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(127, 161, 195, 0.18);
    }
    .alert {
        background: linear-gradient(135deg, #7FA1C3, #26d0ce);
        color: white;
        border: 2px solid #7FA1C3;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(127, 161, 195, 0.15);
    }
    hr {
        border-top: 2px dashed rgba(255, 255, 255, 0.7);
        margin: 10px 0;
    }
    large {
        font-size: 1.2em;
        font-weight: 600;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }
</style>
<div class="container-fluid">
	<div class="row">
		<div class="col-lg-12">
		</div>
	</div>

	<div class="row mt-3 ml-3 mr-3">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-body">
					<?php echo "Welcome back ".$_SESSION['login_name']."!"  ?>
				</div>
				<hr>
				<div class="row">
					<div class="alert alert-success col-md-3 ml-4">
						<p><b><large>Profit Today</large></b></p>
						<hr>
						<p class="text-right"><b><large><?php 
							include 'db_connect.php';
							$laundry = $conn->query("SELECT SUM(total_amount) as amount FROM laundry_list where pay_status= 1 and date(date_created)= '".date('Y-m-d')."'");
							echo $laundry->num_rows > 0 ? number_format($laundry->fetch_array()['amount'],2) : "0.00";
						?></large></b></p>
					</div>
					<div class="alert alert-info col-md-3 ml-4">
						<p><b><large>Customer Today</large></b></p>
						<hr>
						<p class="text-right"><b><large><?php 
							include 'db_connect.php';
							$laundry = $conn->query("SELECT count(id) as `count` FROM laundry_list where  date(date_created)= '".date('Y-m-d')."'");
							echo $laundry->num_rows > 0 ? number_format($laundry->fetch_array()['count']) : "0";
						?></large></b></p>
					</div>
					<div class="alert alert-primary col-md-3 ml-4">
						<p><b><large>Pending Laundry Today</large></b></p>
						<hr>
						<p class="text-right"><b><large><?php 
							include 'db_connect.php';
							$laundry = $conn->query("SELECT count(id) as `count` FROM laundry_list where status = 0 and date(date_created)= '".date('Y-m-d')."'");
							echo $laundry->num_rows > 0 ? number_format($laundry->fetch_array()['count']) : "0";
						?></large></b></p>
					</div>
					<div class="col-md-3 ml-4 alert" style="background: linear-gradient(135deg,#7FA1C3,#0077b6); color: white; border: 2px solid #7FA1C3; border-radius: 12px; box-shadow: 0 4px 15px rgba(127,161,195,0.13);">
						<p><b><large>Claimed Laundry Today</large></b></p>
						<hr>
						<p class="text-right"><b><large><?php 
							include 'db_connect.php';
							$laundry = $conn->query("SELECT count(id) as `count` FROM laundry_list where status = 3 and date(date_created)= '".date('Y-m-d')."'");
							echo $laundry->num_rows > 0 ? number_format($laundry->fetch_array()['count']) : "0";
						?></large></b></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// Optional: JS logic for dashboard if needed
</script>