<?php
require_once __DIR__ . '/security.php';
secure_session_start();
enforce_session_security();
require_admin();
?>
<?php 
include('db_connect.php');
if(isset($_GET['id'])){
	$stmt = $conn->prepare("SELECT id, name, username, type FROM users WHERE id = ?");
	$id = (int) $_GET['id'];
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$user = $stmt->get_result();
	if ($row = $user->fetch_assoc()) {
		foreach($row as $k => $v){
			$meta[$k] = $v;
		}
	}
	$stmt->close();
}
?>
<div class="container-fluid">
	
	<form action="" id="manage-user">
		<input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id']: '' ?>">
		<input type="hidden" name="csrf_token" value="<?php echo escape(csrf_token()); ?>">
		<div class="form-group">
			<label for="name">Name</label>
			<input type="text" name="name" id="name" class="form-control" value="<?php echo isset($meta['name']) ? escape($meta['name']): '' ?>" required>
		</div>
		<div class="form-group">
			<label for="username">Username</label>
			<input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? escape($meta['username']): '' ?>" required>
		</div>
		<div class="form-group">
			<label for="password">Password <?php echo isset($meta['id']) ? '<small class="text-muted">(leave blank to keep current password)</small>' : ''; ?></label>
			<input type="password" name="password" id="password" class="form-control" <?php echo isset($meta['id']) ? '' : 'required'; ?>>
		</div>
		<div class="form-group">
			<label for="type">User Type</label>
			<select name="type" id="type" class="custom-select">
				<option value="1" <?php echo isset($meta['type']) && $meta['type'] == 1 ? 'selected': '' ?>>Admin</option>
				<option value="2" <?php echo isset($meta['type']) && $meta['type'] == 2 ? 'selected': '' ?>>Staff</option>
			</select>
		</div>
	</form>
</div>
<script>
	$('#manage-user').submit(function(e){
		e.preventDefault();
		start_load()
		$.ajax({
			url:'ajax.php?action=save_user',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp ==1){
					alert_toast("Data successfully saved",'success')
					setTimeout(function(){
						location.reload()
					},1500)
				}
			}
		})
	})
</script>
