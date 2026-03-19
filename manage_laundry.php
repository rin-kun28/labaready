<?php
include "db_connect.php";

if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM laundry_list where id =".$_GET['id']);
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
    
    // Fetch payment information for this laundry
    $payment_qry = $conn->query("SELECT * FROM payments WHERE laundry_id = ".$_GET['id']." ORDER BY payment_date DESC LIMIT 1");
    if($payment_qry->num_rows > 0) {
        $payment_row = $payment_qry->fetch_assoc();
        $payment_method = $payment_row['payment_method'];
        $payment_ref = isset($payment_row['payment_ref']) ? $payment_row['payment_ref'] : '';
    }
}

// Fetch all supplies for the supply usage feature
$supplies = [];
$supply_q = $conn->query("SELECT * FROM supply_list ORDER BY name ASC");
while($row = $supply_q->fetch_assoc()) {
    $supplies[] = $row;
}

// Fetch supplies used for this laundry (for edit)
$used_supplies = [];
if (isset($id)) {
    $used_q = $conn->query("SELECT * FROM supplies_used WHERE laundry_id = $id");
    while($row = $used_q->fetch_assoc()) {
        $used_supplies[] = [
            'supply_id' => $row['supply_id'],
            'qty' => $row['qty']
        ];
    }
}
?>
<!-- Select2 CSS & JS for searchable dropdown -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="container-fluid">
    <form action="" id="manage-laundry" method="post" enctype="multipart/form-data">
        <div class="col-lg-12">
            <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Customer Name</label>
                        <input type="text" class="form-control" name="customer_name" value="<?php echo isset($customer_name) ? $customer_name : '' ?>">
                    </div>
                    <div class="form-group">
    <label for="customer_number" class="control-label">Customer Number</label>
    <input type="text" class="form-control" name="customer_number" id="customer_number"
        value="<?php
            // Use the value as is, but remove all non-digits for display
            if (isset($customer_number) && strlen(trim($customer_number)) > 0) {
                echo htmlspecialchars(preg_replace('/\D/', '', $customer_number));
            }
        ?>"
        oninput="validatePHNumber(this)"
        pattern="[0-9]{10,12}"
        maxlength="12"
        title="Enter a valid number, e.g., 09XXXXXXXXX or 9XXXXXXXXX">
</div>
<script>
function validatePHNumber(input) {
    // Only allow digits, max 12 numbers
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 12);
    input.value = value;
}
</script>
                    <div class="form-group">
                        <label for="" class="control-label">Supplies Used</label>
                        <div id="supplies-used-list"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-supply-btn"><i class="fa fa-plus"></i> Add Supply</button>
                    </div>
                </div>
                <?php if(isset($_GET['id'])): ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Status</label>
                        <select name="status" id="" class="custom-select browser-default">
                            <option value="0" <?php echo $status == 0 ? "selected" : '' ?>>Pending</option>
                            <option value="1" <?php echo $status == 1 ? "selected" : '' ?>>Processing</option>
                            <option value="2" <?php echo $status == 2 ? "selected" : '' ?>>To Claim</option>
                            <option value="3" <?php echo $status == 3 ? "selected" : '' ?>>Claimed</option>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label class="control-label">Remarks</label>
                    <textarea name="remarks" id="" cols="30" rows="2" class="form-control"><?php echo isset($remarks) ? $remarks : '' ?></textarea>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Laundry Category</label>
                        <select class="custom-select browser-default" id="laundry_category_id">
                            <?php
                                $cat = $conn->query("SELECT * FROM laundry_categories order by name asc");
                                while($row= $cat->fetch_assoc()):
                                    $cname_arr[$row['id']] = $row['name'];
                            ?>
                            <option value="<?php echo $row['id'] ?>" data-price="<?php echo $row['price'] ?>"><?php echo $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="" class="control-label">Weight</label>
                                <input type="number" step="0.01" min="0.01" value="1" class="form-control text-center" id="weight" style="width:80px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <button class="btn btn-info btn-sm btn-block" type="button" id="add_to_list"><i class="fa fa-plus"></i> Add to List</button>
                    </div>
                </div>
            </div>
            <div class="row">
            <table class="table table-bordered" id="list">
            <colgroup>
                <col width="18%">
                <col width="8%">
                <col width="14%">
                <col width="14%">
                <col width="14%">
                <col width="12%">
                <col width="8%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center">Category</th>
                    <th class="text-center">Weight</th>
                    <th class="text-center">Supplies Used</th>
                    <th class="text-center">Supply Qty Used</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($_GET['id'])): ?>
                <?php
                    $list = $conn->query("SELECT * from laundry_items where laundry_id = ".$id);
                    // Fetch supplies used (name and qty) for this laundry
                    $used_supplies_disp = [];
                    $used_supplies_qty_disp = [];
                    $used_q2 = $conn->query("SELECT su.*, sl.name FROM supplies_used su JOIN supply_list sl ON su.supply_id = sl.id WHERE su.laundry_id = ".$id);
                    while($r = $used_q2->fetch_assoc()){
                        $used_supplies_disp[] = htmlspecialchars($r['name']);
                        $used_supplies_qty_disp[] = $r['qty'];
                    }
                    $first_row = true;
                    while($row=$list->fetch_assoc()):
                ?>
                    <tr data-id="<?php echo $row['id'] ?>">
                        <td>
                            <input type="hidden" name="item_id[]" value="<?php echo $row['id'] ?>">
                            <input type="hidden" name="laundry_category_id[]" value="<?php echo $row['laundry_category_id'] ?>">
                            <?php echo isset($cname_arr[$row['laundry_category_id']]) ? ucwords($cname_arr[$row['laundry_category_id']]) : '' ?>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="number" class="form-control text-center" name="weight[]" value="<?php echo $row['weight'] ?>" step="0.01" min="0" style="width:60px;">
                            </div>
                        </td>
                        <?php if($first_row): ?>
                        <td class="text-center" rowspan="<?php echo $list->num_rows; ?>">
                            <?php
                            echo $used_supplies_disp ? implode(', ', $used_supplies_disp) : '<span class="text-muted">None</span>';
                            ?>
                        </td>
                        <td class="text-center" rowspan="<?php echo $list->num_rows; ?>">
                            <?php
                            echo $used_supplies_qty_disp ? implode(', ', $used_supplies_qty_disp) : '-';
                            ?>
                        </td>
                        <?php endif; ?>
                        <td class="text-right">
                            <input type="hidden" name="unit_price[]" value="<?php echo $row['unit_price'] ?>">
                            <?php echo number_format($row['unit_price'],2) ?>
                        </td>
                        <td class="text-right">
                            <input type="hidden" name="amount[]" value="<?php echo $row['amount'] ?>">
                            <p class="m-0"><?php echo number_format($row['amount'],2) ?></p>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger" type="button" onclick="rem_list($(this))" style="padding: 3px 8px;">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                <?php
                    $first_row = false;
                    endwhile;
                ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right" colspan="5">Total Amount</th>
                    <th class="text-right" id="tamount"></th>
                    <th></th>
                </tr>
            </tfoot>
            </table>
            </div>
            <hr>
            <div class="row">
                <div class="form-group">
                    <div class="custom-control custom-switch" id="pay-switch">
                      <input type="checkbox" class="custom-control-input" value="1" name="pay" id="paid" <?php echo isset($pay_status) && $pay_status == 1 ? 'checked' :'' ?>>
                      <label class="custom-control-label" for="paid">Pay</label>
                      <button type="button" onclick="showGCashModal()" style="padding: 5px 10px; font-size: 12px; background-color:rgba(29, 184, 231, 0.87); color: white; border: none; cursor: pointer; margin-left: 10px;">
                        Pay via GCash
                      </button>
                    </div>
                </div>
            </div>
            <div class="row" id="payment">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="custom-select browser-default">
                            <option value="Cash" <?php echo isset($payment_method) && $payment_method=='Cash' ? 'selected' : '' ;?>>Cash</option>
                            <option value="GCash" <?php echo isset($payment_method) && $payment_method=='GCash' ? 'selected' : '' ;?>>GCash</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Amount Tendered</label>
                        <input type="number" step="any" min="0" value="<?php echo isset($amount_tendered) ? $amount_tendered : 0 ?>" class="form-control text-right" name="tendered">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Total Amount</label>
                        <input type="number" step="any" min="1" value="<?php echo isset($total_amount) ? $total_amount : 0 ?>" class="form-control text-right" name="tamount" readonly="">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Change</label>
                        <input type="number" step="any" min="1" value="<?php echo isset($amount_change) ? $amount_change : 0 ?>" class="form-control text-right" name="change" readonly="">
                    </div>
                </div>
                <div class="col-md-6 gcash-only" style="display:none;">
                    <div class="form-group">
                        <label class="control-label">GCash Reference No.</label>
                        <input type="text" class="form-control" name="payment_ref" placeholder="Enter GCash Reference #" value="<?php echo isset($payment_ref) ? $payment_ref : '' ?>">
                    </div>
                </div>
                <div class="col-md-6 gcash-only" style="display:none;">
                    <div class="form-group">
                        <label class="control-label d-block">Pay via GCash</label>
                        <button type="button" onclick="showGCashModal()" class="btn btn-info btn-sm">Show QR</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Upload Payment Proof (optional)</label>
                        <input type="file" class="form-control" name="payment_proof" accept="image/*,application/pdf">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="gcashModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:white; padding:16px; width: 95%; max-width:360px; margin:auto; border-radius:8px; position:relative; box-shadow:0 10px 20px rgba(0,0,0,.2);">
        <span onclick="closeGCashModal()" style="position:absolute; top:6px; right:10px; cursor:pointer; font-weight:bold; font-size:20px;">&times;</span>
        <h4 style="text-align:center; margin: 6px 0 10px;">Scan to Pay via GCash</h4>
        <div style="border:1px solid #e5e7eb; border-radius:6px; padding:10px; background:#f9fafb;">
            <img id="gcashQrImg" src="uploads/gcash_qr_instapay.jpg" alt="InstaPay GCash QR Code"
                 style="width:100%; height:auto; display:block; border-radius:4px; max-width:280px; margin:0 auto;"
                 onerror="this.style.display='none'; document.getElementById('gcashMissingNote').style.display='block';">
            <div id="gcashMissingNote" style="display:none; color:#ef4444; font-size:13px; text-align:center; padding:20px;">
                <p><strong>QR Code Not Found</strong></p>
                <p>Please save your InstaPay QR code as:</p>
                <code>uploads/gcash_qr_instapay.jpg</code>
            </div>
        </div>
        <div style="text-align:center; margin:8px 0; font-size:12px; color:#6b7280;">
            <strong>InstaPay QR Code</strong><br>
            Scan with any banking app or GCash
        </div>
        <div style="display:flex; gap:8px; margin-top:10px;">
            <a href="uploads/gcash_qr_instapay.jpg" download="instapay_qr.jpg" class="btn btn-outline-primary btn-sm" style="flex:1; text-align:center;">
                Download QR
            </a>
            <button type="button" class="btn btn-primary btn-sm" style="flex:1;" onclick="window.open('uploads/gcash_qr_instapay.jpg','_blank')">
                View Full Size
            </button>
        </div>
    </div>
</div>
<script>
var supplies = <?php echo json_encode($supplies); ?>;
var used_supplies = <?php echo json_encode($used_supplies); ?>;

function getSelectedSupplyName(supply_id) {
    for (var i=0;i<supplies.length;i++) {
        if (supplies[i].id == supply_id) return supplies[i].name;
    }
    return '';
}
function getSelectedSupplyIds() {
    var ids = [];
    $('#supplies-used-list .supply-row select').each(function() {
        var val = $(this).val();
        if(val) ids.push(val);
    });
    return ids;
}
function addSupplyRow(selected_id, qty) {
    var selected_ids = getSelectedSupplyIds();
    if(selected_id) selected_ids = selected_ids.filter(function(id){ return id != selected_id; });
    var $row = $('<div class="input-group mb-1 supply-row"></div>');
    var $select = $('<select name="supply_used[]" class="form-control supply-select" style="width:50%"><option value="">Select Supply</option></select>');
    for(var i=0;i<supplies.length;i++) {
        if(selected_ids.indexOf(String(supplies[i].id)) === -1 || (selected_id && supplies[i].id == selected_id)){
            var selected = (supplies[i].id == selected_id) ? 'selected' : '';
            $select.append('<option value="'+supplies[i].id+'" '+selected+'>'+supplies[i].name+' (₱'+supplies[i].price+', Stock: '+supplies[i].qty+')</option>');
        }
    }
    var $qty = $('<input type="number" min="0.01" step="0.01" name="supply_qty[]" class="form-control ml-1" style="width:30%;" placeholder="Qty" value="'+(qty||'')+'">');
    var $rm = $('<button type="button" class="btn btn-danger btn-sm ml-1 remove-supply-btn"><i class="fa fa-times"></i></button>');
    $row.append($select).append($qty).append($rm);
    $('#supplies-used-list').append($row);
    $select.select2({placeholder: "Search supply...", allowClear: true, width: '100%'});
    $select.on('change', function(){
        updateAllSupplyDropdowns();
    });
}
function updateAllSupplyDropdowns() {
    var allSelected = getSelectedSupplyIds();
    $('#supplies-used-list .supply-row').each(function(){
        var $select = $(this).find('select');
        var currentVal = $select.val();
        $select.find('option').each(function(){
            var val = $(this).val();
            if(val === "" || val == currentVal) {
                $(this).prop('disabled', false).show();
            } else if(allSelected.indexOf(val) !== -1) {
                $(this).prop('disabled', true).hide();
            } else {
                $(this).prop('disabled', false).show();
            }
        });
    });
}
$(function() {
    if(used_supplies.length > 0) {
        $('#supplies-used-list').empty();
        for(var i=0;i<used_supplies.length;i++) {
            addSupplyRow(used_supplies[i].supply_id, used_supplies[i].qty);
        }
    } else {
        addSupplyRow();
    }
    updateAllSupplyDropdowns();
});
$('#add-supply-btn').on('click', function(){
    var selected_ids = getSelectedSupplyIds();
    if(selected_ids.length >= supplies.length) {
        alert_toast('All supplies have already been added.','warning');
        return false;
    }
    addSupplyRow();
    updateAllSupplyDropdowns();
});
$(document).on('click', '.remove-supply-btn', function(){
    $(this).closest('.supply-row').remove();
    updateAllSupplyDropdowns();
    calc();
});
$(document).on('change', '[name="supply_used[]"]', function(){
    updateAllSupplyDropdowns();
    calc();
});
$(document).on('change keyup', '[name="supply_qty[]"]', function(){
    calc();
});
function getSuppliesUsedSummary() {
    var names = [];
    var qtys = [];
    $('#supplies-used-list .supply-row').each(function(){
        var supply_id = $(this).find('select').val();
        var supply_qty = $(this).find('input').val();
        if(supply_id && supply_qty){
            names.push(getSelectedSupplyName(supply_id));
            qtys.push(supply_qty);
        }
    });
    return {names: names, qtys: qtys};
}
function calc(){
    var total = 0;
    $('#list tbody tr').each(function(){
        var _this = $(this)
        var weight = _this.find('[name="weight[]"]').val()
        var unit_price = _this.find('[name="unit_price[]"]').val()
        var amount = parseFloat(weight) * parseFloat(unit_price)
        _this.find('[name="amount[]"]').val(amount)
        _this.find('[name="amount[]"]').siblings('p').html(parseFloat(amount).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,maximumFractionDigits:2}))
        total+= amount;
    });
    $('#supplies-used-list .supply-row').each(function(){
        var supply_id = $(this).find('select').val();
        var supply_qty = parseFloat($(this).find('input').val()) || 0;
        if(supply_id && supply_qty > 0){
            for(var i=0;i<supplies.length;i++){
                if(supplies[i].id == supply_id){
                    total += supply_qty * parseFloat(supplies[i].price);
                }
            }
        }
    });
    $('[name="tamount"]').val(total);
    $('#tamount').html(parseFloat(total).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,maximumFractionDigits:2}));
}
$('#add_to_list').click(function(){
    var cat = $('#laundry_category_id').val(),
        _weight = $('#weight').val();
    var suppliesSummary = getSuppliesUsedSummary();
    var supply_names = suppliesSummary.names.join(', ');
    var supply_qtys = suppliesSummary.qtys.join(', ');
    if(cat == '' || _weight ==''){
        alert_toast('Fill the category and weight fields first.','warning')
        return false;
    }
    if($('#list tr[data-id="'+cat+'"]').length > 0){
        alert_toast('Category already exist.','warning')
        return false;
    }
    var price = $('#laundry_category_id option[value="'+cat+'"]').attr('data-price');
    var cname = $('#laundry_category_id option[value="'+cat+'"]').html();
    var amount = parseFloat(price) * parseFloat(_weight);
    var tr = $('<tr></tr>');
    tr.attr('data-id',cat)
    tr.append('<input type="hidden" name="item_id[]" id="" value=""><td class=""><input type="hidden" name="laundry_category_id[]" id="" value="'+cat+'">'+cname+'</td>');
    tr.append('<td><input type="number" class="text-center" name="weight[]" id="" value="'+_weight+'" style="width:60px;"></td>');
    tr.append('<td class="text-center supply-used-view">'+(supply_names ? supply_names : '<span class="text-muted">None</span>')+'</td>');
    tr.append('<td class="text-center supply-qty-view">'+(supply_qtys ? supply_qtys : '-')+'</td>');
    tr.append('<td class="text-right"><input type="hidden" name="unit_price[]" id="" value="'+price+'">'+(parseFloat(price).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,maximumFractionDigits:2}))+'</td>');
    tr.append('<td class="text-right"><input type="hidden" name="amount[]" id="" value="'+amount+'"><p>'+(parseFloat(amount).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,maximumFractionDigits:2}))+'</p></td>');
    tr.append('<td class="text-center"><button class="btn btn-sm btn-danger" type="button" onclick="rem_list($(this))" style="padding: 3px 8px;"><i class="fa fa-times"></i></button></td>');
    $('#list tbody').append(tr)
    calc()
    $('[name="weight[]"]').on('keyup keydown keypress change',function(){
        calc();
    })
    $('[name="tendered"]').trigger('keypress')
    $('#laundry_category_id').val('')
    $('#weight').val('')
});
function rem_list(_this){
    _this.closest('tr').remove()
    calc()
    $('[name="tendered"]').trigger('keypress')
}
if('<?php echo isset($_GET['id']) ?>' == 1){
        calc()
    }
if($('[name="pay"]').prop('checked') == true){
        $('[name="tendered"]').attr('required',true)
        $('#payment').show();
    }else{
        $('#payment').hide();
        $('[name="tendered"]').attr('required',false)
    }
$('#pay-switch').click(function(){
    if($('[name="pay"]').prop('checked') == true){
        $('[name="tendered"]').attr('required',true)
        $('#payment').show('slideDown');
    }else{
        $('#payment').hide('SlideUp');
        $('[name="tendered"]').attr('required',false)
    }
});
$('[name="tendered"],[name="tamount"]').on('keypup keydown keypress change input',function(){
    var tend = $('[name="tendered"]').val();
    var amount = $('[name="tamount"]').val();
    var change = parseFloat(tend) - parseFloat(amount)
    change = parseFloat(change).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,minimumFractionDigits:2})
    $('[name="change"]').val(change)
});
$(document).ready(function(){
    $('.supply-select').select2({placeholder: "Search supply...", allowClear: true, width: '100%'});
    // Ensure only one submit handler is bound, and only unique supplies are posted
    $('#manage-laundry').off('submit').on('submit', function(e){
        e.preventDefault();
        if($('#list tbody tr').length <= 0){
            alert_toast("Please add at least one laundry item","warning");
            return false;
        }
        // Require pay for new laundry (no id means add, not edit)
        var isEdit = $('[name="id"]').val() != '';
        if(!isEdit && !$('[name="pay"]').prop('checked')) {
            alert_toast("You must pay before saving a new laundry entry.","warning");
            return false;
        }
        if($('[name="pay"]').prop('checked') == true) {
            var total = parseFloat($('[name="tamount"]').val());
            var tendered = parseFloat($('[name="tendered"]').val());
            if(tendered < total) {
                alert_toast("Amount tendered cannot be less than total amount","warning");
                return false;
            }
            var pm = $('#payment_method').val();
            if(pm === 'GCash'){
                var ref = ($('[name="payment_ref"]').val() || '').trim();
                if(ref.length === 0){
                    alert_toast("GCash reference number is required.", 'warning');
                    end_load();
                    return false;
                }
            }
        }
        $('.hidden-supplies-input').remove();
        // Only append unique supplies
        var seen = {};
        $('#supplies-used-list .supply-row').each(function(i){
            var sid = $(this).find('select').val();
            var qty = $(this).find('input').val();
            if(sid && qty && !seen[sid]){
                $('#manage-laundry').append('<input type="hidden" name="supply_used[]" class="hidden-supplies-input" value="'+sid+'">');
                $('#manage-laundry').append('<input type="hidden" name="supply_qty[]" class="hidden-supplies-input" value="'+qty+'">');
                seen[sid]=true;
            }
        });
        start_load();
        var fd = new FormData(document.getElementById('manage-laundry'));
        $.ajax({
            url: 'ajax.php?action=save_laundry',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(resp){
                if(resp == 1){
                    alert_toast("Data successfully saved", 'success');
                    setTimeout(function(){
                        location.href = 'index.php?page=laundry';
                    }, 1500);
                } else {
                    alert_toast("Error: " + resp, 'error');
                    end_load();
                }
            },
            error: function(xhr, status, error){
                alert_toast("An error occurred: " + error, "error");
                end_load();
            }
        });
    });
    
    // initialize payment method UI
    $('#payment_method').off('change').on('change', handlePaymentMethodUI);
    handlePaymentMethodUI();
});

function handlePaymentMethodUI(){
    var paying = $('[name="pay"]').prop('checked');
    var pm = $('#payment_method').val();
    if(!paying){
        $('.gcash-only').hide();
        $('[name="tendered"]').prop('readonly', false);
        return;
    }
    if(pm === 'GCash'){
        $('.gcash-only').show();
        $('[name="payment_ref"]').attr('required', true);
        var total = parseFloat($('[name="tamount"]').val()) || 0;
        $('[name="tendered"]').val(total.toFixed(2)).prop('readonly', true).trigger('input');
        $('[name="change"]').val('0.00');
    }else{
        $('.gcash-only').hide();
        $('[name="payment_ref"]').removeAttr('required');
        $('[name="tendered"]').prop('readonly', false);
    }
}

function showGCashModal() {
    // Switch to GCash method and refresh UI so tendered = total
    var $pm = $('#payment_method');
    if($pm.length){
        $pm.val('GCash').trigger('change');
        if(typeof handlePaymentMethodUI === 'function') handlePaymentMethodUI();
    }
    // Ensure Pay is ON so amount is recorded on save
    var $paid = $('#paid');
    if($paid.length && !$paid.prop('checked')){
        $paid.prop('checked', true);
        $('#pay-switch').trigger('click');
    }
    document.getElementById("gcashModal").style.display = "flex";
}

function closeGCashModal() {
    document.getElementById("gcashModal").style.display = "none";
}
</script>