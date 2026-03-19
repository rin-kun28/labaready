<?php
require_once __DIR__ . '/security.php';
secure_session_start();
enforce_session_security();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Laundry Management System</title>
 	

<?php
 include('./header.php'); 
 // include('./auth.php'); 
 ?>

</head>
<style>
	:root {
		--primary-color: #6366f1;
		--primary-dark: #4f46e5;
		--primary-light: #818cf8;
		--secondary-color: #10b981;
		--danger-color: #ef4444;
		--warning-color: #f59e0b;
		--info-color: #3b82f6;
		--bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		--card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
		--card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
	}
	
	body{
		background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
		font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		min-height: 100vh;
	}
	
	.card {
		border: none !important;
		border-radius: 12px !important;
		box-shadow: var(--card-shadow) !important;
		transition: all 0.3s ease !important;
		background: white !important;
	}
	
	.card:hover {
		box-shadow: var(--card-shadow-hover) !important;
	}
	
	.card-header {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
		color: white !important;
		border: none !important;
		border-radius: 12px 12px 0 0 !important;
		padding: 1rem 1.5rem !important;
		font-weight: 600 !important;
	}
	
	.btn-primary {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
		border: none !important;
		border-radius: 8px !important;
		padding: 0.5rem 1.25rem !important;
		font-weight: 500 !important;
		transition: all 0.3s ease !important;
		box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3) !important;
	}
	
	.btn-primary:hover {
		transform: translateY(-2px) !important;
		box-shadow: 0 4px 8px rgba(99, 102, 241, 0.4) !important;
	}
	
	.btn-success {
		background: linear-gradient(135deg, var(--secondary-color) 0%, #059669 100%) !important;
		border: none !important;
		border-radius: 8px !important;
		box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3) !important;
	}
	
	.btn-success:hover {
		transform: translateY(-2px) !important;
		box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4) !important;
	}
	
	.btn-danger, .btn-outline-danger {
		background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;
		border: none !important;
		border-radius: 8px !important;
		color: white !important;
		box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3) !important;
	}
	
	.btn-outline-danger:hover {
		transform: translateY(-2px) !important;
		box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4) !important;
	}
	
	.btn-outline-primary {
		border: 2px solid var(--primary-color) !important;
		color: var(--primary-color) !important;
		border-radius: 8px !important;
		background: white !important;
		transition: all 0.3s ease !important;
	}
	
	.btn-outline-primary:hover {
		background: var(--primary-color) !important;
		color: white !important;
		transform: translateY(-2px) !important;
	}
	
	.table {
		border-radius: 8px !important;
		overflow: hidden !important;
	}
	
	.table thead th {
		background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
		color: #334155 !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		font-size: 0.75rem !important;
		letter-spacing: 0.05em !important;
		border: none !important;
		padding: 1rem 0.75rem !important;
	}
	
	.table tbody tr {
		transition: all 0.2s ease !important;
	}
	
	.table tbody tr:hover {
		background: #f8fafc !important;
		transform: scale(1.01) !important;
	}
	
	.badge {
		padding: 0.4rem 0.8rem !important;
		border-radius: 6px !important;
		font-weight: 500 !important;
		font-size: 0.75rem !important;
	}
	
	.badge-primary {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
	}
	
	.badge-success {
		background: linear-gradient(135deg, var(--secondary-color) 0%, #059669 100%) !important;
	}
	
	.badge-danger {
		background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;
	}
	
	.badge-warning {
		background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%) !important;
	}
	
	.badge-info {
		background: linear-gradient(135deg, var(--info-color) 0%, #2563eb 100%) !important;
	}
	
	.badge-secondary {
		background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
	}
	
	.form-control {
		border: 2px solid #e5e7eb !important;
		border-radius: 8px !important;
		padding: 0.625rem 0.875rem !important;
		transition: all 0.3s ease !important;
	}
	
	.form-control:focus {
		border-color: var(--primary-color) !important;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
	}
	
	.modal-content {
		border-radius: 16px !important;
		border: none !important;
		box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
	}
	
	.modal-header {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
		color: white !important;
		border: none !important;
		border-radius: 16px 16px 0 0 !important;
		padding: 1.25rem 1.5rem !important;
	}
	
	.modal-header .close {
		color: white !important;
		opacity: 0.8 !important;
		text-shadow: none !important;
	}
	
	.modal-header .close:hover {
		opacity: 1 !important;
	}
	
	.modal-dialog.large {
		width: 80% !important;
		max-width: unset;
	}
	
	.modal-dialog.mid-large {
		width: 50% !important;
		max-width: unset;
	}
	
	.toast {
		border-radius: 12px !important;
		box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
	}
	
	/* DataTables styling */
	.dataTables_wrapper .dataTables_paginate .paginate_button.current {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
		border: none !important;
		color: white !important;
		border-radius: 6px !important;
	}
	
	.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
		background: var(--primary-light) !important;
		border: none !important;
		color: white !important;
	}
	
	/* Scrollbar styling */
	::-webkit-scrollbar {
		width: 8px;
		height: 8px;
	}
	
	::-webkit-scrollbar-track {
		background: #f1f5f9;
		border-radius: 10px;
	}
	
	::-webkit-scrollbar-thumb {
		background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
		border-radius: 10px;
	}
	
	::-webkit-scrollbar-thumb:hover {
		background: var(--primary-dark);
	}
</style>

<body>
	<?php include 'topbar.php' ?>
	<?php include 'navbar.php' ?>
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  <main id="view-panel" >
      <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
  	<?php include $page.'.php' ?>
  	

  </main>

  <div id="preloader"></div>
  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

<div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
      </div>
      <div class="modal-body">
        <div id="delete_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
      </div>
    </div>
  </div>
</body>
<script>
	 window.start_load = function(){
    $('body').prepend('<di id="preloader2"></di>')
  }
  window.end_load = function(){
    $('#preloader2').fadeOut('fast', function() {
        $(this).remove();
      })
  }

  window.uni_modal = function($title = '' , $url='',$size=""){
    start_load()
    $.ajax({
        url:$url,
        error:err=>{
            console.log()
            alert("An error occured")
        },
        success:function(resp){
            if(resp){
                $('#uni_modal .modal-title').html($title)
                $('#uni_modal .modal-body').html(resp)
                if($size != ''){
                    $('#uni_modal .modal-dialog').addClass($size)
                }else{
                    $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
                }
                $('#uni_modal').modal('show')
                end_load()
            }
        }
    })
}
window._conf = function($msg='',$func='',$params = []){
     $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
     $('#confirm_modal .modal-body').html($msg)
     $('#confirm_modal').modal('show')
  }
   window.alert_toast= function($msg = 'TEST',$bg = 'success'){
      $('#alert_toast').removeClass('bg-success')
      $('#alert_toast').removeClass('bg-danger')
      $('#alert_toast').removeClass('bg-info')
      $('#alert_toast').removeClass('bg-warning')

    if($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({delay:3000}).toast('show');
  }
  $(document).ready(function(){
    $('#preloader').fadeOut('fast', function() {
        $(this).remove();
      })
  })
  
</script>	
</html>
