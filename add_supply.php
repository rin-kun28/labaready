<?php include 'db_connect.php'; ?>
<div class="container-fluid">
	<form id="add-supply-form">
		<div class="form-group">
			<label for="name">Supply Name</label>
			<input type="text" name="name" class="form-control" required>
		</div>
		<div class="form-group">
			<label for="qty">Initial Quantity</label>
			<input type="number" name="qty" class="form-control" required>
		</div>
		<div class="form-group">
			<label for="price">Price per Unit</label>
			<input type="number" step="0.01" name="price" class="form-control" required>
		</div>
	</form>
</div>
<script>
$('#add-supply-form').submit(function(e){
	e.preventDefault()
	start_load()
	$.ajax({
		url: 'ajax.php?action=add_supply',
		method: 'POST',
		data: $(this).serialize(),
		success: function(resp){
			if(resp == 1){
				alert_toast("New supply added successfully", "success")
				setTimeout(function(){
					location.reload()
				}, 1500)
			}
		}
	})
})
</script>