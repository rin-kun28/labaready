<?php
include 'db_connect.php';
$supply = $conn->query("SELECT * FROM supply_list ORDER BY name ASC");
$sup_arr = [];
$sup_stock = [];
while($row = $supply->fetch_assoc()){
    $sup_arr[$row['id']] = $row['name'];
    $sup_stock[$row['id']] = $row['qty'];
}
?>
<div class="container-fluid">
    <form id="manage-inv-form" autocomplete="off">
        <div class="form-group">
            <label for="supply_id">Supply Name</label>
            <select class="custom-select browser-default" name="supply_id" id="supply_id" required>
                <option value="" disabled selected>Select Supply</option>
                <?php foreach($sup_arr as $id => $name): ?>
                <option value="<?php echo $id; ?>" data-stock="<?php echo $sup_stock[$id]; ?>">
                    <?php echo htmlspecialchars($name); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small id="current-stock-label" class="text-info"></small>
        </div>
        <div class="form-group">
            <label for="qty">Quantity</label>
            <input type="number" step="1" min="1" class="form-control text-right" name="qty" id="qty" required>
        </div>
        <div class="form-group">
            <label for="stock_type">Type</label>
            <select name="stock_type" id="stock_type" class="custom-select browser-default" required>
                <option value="1">Stock In</option>
                <option value="2">Use</option>
            </select>
        </div>
    
    </form>
</div>
<script>
// Show current stock when supply is selected
$('#supply_id').on('change', function() {
    var stock = $('option:selected', this).data('stock');
    $('#current-stock-label').text('Current Stock: ' + (typeof stock !== "undefined" ? stock : ''));
});
// Prevent non-numeric input in qty
$('#qty').on('input', function() {
    // Remove any non-digit character
    this.value = this.value.replace(/[^0-9]/g, '');
});
// Prevent submitting if fields are invalid (blank, zero, etc)
$('#manage-inv-form').submit(function(e){
    var supplyVal = $('#supply_id').val();
    var qtyVal = $('#qty').val();
    // Validate: supply selected, qty is not blank, is positive integer, not zero
    if (!supplyVal) {
        alert_toast("Please select a supply.",'danger');
        $('#supply_id').focus();
        e.preventDefault();
        return false;
    }
    if (!/^\d+$/.test(qtyVal) || parseInt(qtyVal) < 1) {
        alert_toast("Quantity must be a positive number and not blank!",'danger');
        $('#qty').focus();
        e.preventDefault();
        return false;
    }
    start_load();
    $.ajax({
        url:'ajax.php?action=save_inv',
        method:'POST',
        data:$(this).serialize(),
        success:function(resp){
            if(resp == 1){
                alert_toast("Inventory successfully updated",'success');
                setTimeout(function(){
                    location.reload();
                },800);
            } else {
                alert_toast("Error: "+resp,'danger');
            }
            end_load();
        }
    });
    return false;
});
</script>