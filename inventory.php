<?php include 'db_connect.php' ?>
<div class="container-fluid py-4">
    <div class="col-lg-12">
        <div class="row">
            <!-- Inventory List -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0 font-weight-bold">Inventory</span>
                        <button class="btn btn-success btn-sm" id="add-supply"><i class="fa fa-plus mr-1"></i> Add Supply</button>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="supply-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width:5%;">#</th>
                                        <th class="text-center" style="width:45%;">Supply Name</th>
                                        <th class="text-center" style="width:20%;">Price (₱)</th>
                                        <th class="text-center" style="width:20%;">Stocks</th>
                                        <th class="text-center" style="width:10%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $i = 1;
                                        $supply = $conn->query("SELECT * FROM supply_list ORDER BY name ASC");
                                        while($row = $supply->fetch_assoc()):
                                            $sup_arr[$row['id']] = $row['name'];
                                            $available = $row['qty'];
                                    ?>
                                    <tr data-id="<?php echo $row['id'] ?>" data-name="<?php echo htmlspecialchars($row['name']) ?>" data-price="<?php echo $row['price'] ?>" data-stock="<?php echo $available ?>">
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td><?php echo htmlspecialchars($row['name']) ?></td>
                                        <td class="text-right"><?php echo number_format($row['price'], 2) ?></td>
                                        <td class="text-right"><?php echo $available ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm edit-supply" 
                                                data-id="<?php echo $row['id'] ?>" 
                                                data-name="<?php echo htmlspecialchars($row['name']) ?>"
                                                data-price="<?php echo $row['price'] ?>"
                                                data-stock="<?php echo $available ?>"
                                                title="Edit">
                                                <i class="fa fa-edit"></i>
                                            <!-- </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-supply" 
                                                data-id="<?php echo $row['id'] ?>"
                                                title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button> -->
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Supply In/Out List -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0 font-weight-bold">Supply In/Out List</span>
                        <button class="btn btn-light btn-sm" id="manage-supply"><i class="fa fa-cogs"></i> Manage Supply</button>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="inventory-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Supply Name</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Past Qty</th>
                                        <th class="text-center">Total Qty</th>
                                        <th class="text-center">Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $supplies = [];
                                        $supply_res = $conn->query("SELECT id, qty, name FROM supply_list");
                                        while ($s = $supply_res->fetch_assoc()) {
                                            $supplies[$s['id']] = [
                                                'qty' => $s['qty'],
                                                'name' => $s['name'],
                                            ];
                                        }
                                        $inventory = $conn->query("SELECT * FROM inventory ORDER BY id DESC");
                                        $inv_arr = [];
                                        while ($row = $inventory->fetch_assoc()) {
                                            $inv_arr[] = $row;
                                        }
                                        $supply_qtys = [];
                                        foreach ($supplies as $sid => $s) $supply_qtys[$sid] = $s['qty'];
                                        foreach ($inv_arr as $row):
                                            $supply_id = $row['supply_id'];
                                            $qty = $row['qty'];
                                            $stock_type = $row['stock_type'];
                                            $current_qty = isset($supply_qtys[$supply_id]) ? $supply_qtys[$supply_id] : 0;
                                            $past_qty = $current_qty;
                                            if ($stock_type == 1) $past_qty -= $qty; // IN
                                            elseif ($stock_type == 2) $past_qty += $qty; // USED
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo date("Y-m-d",strtotime($row['date_created'])) ?></td>
                                        <td><?php echo htmlspecialchars($supplies[$supply_id]['name'] ?? 'Unknown') ?></td>
                                        <td class="text-right"><?php echo $qty ?></td>
                                        <td class="text-right"><?php echo $past_qty ?></td>
                                        <td class="text-right"><?php echo $current_qty ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $stock_type == 1 ? 'badge-primary' : 'badge-secondary' ?>">
                                                <?php echo $stock_type == 1 ? 'IN' : 'Used' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                            $supply_qtys[$supply_id] = $past_qty;
                                        endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing supply -->
<div class="modal fade" id="editSupplyModal" tabindex="-1" role="dialog" aria-labelledby="editSupplyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="edit-supply-form">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Supply</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-1">
                    <input type="hidden" id="edit-supply-id" name="id">
                    <div class="form-group">
                        <label for="edit-supply-name">Supply Name</label>
                        <input type="text" class="form-control" id="edit-supply-name" name="name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-supply-price">Price (₱)</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="edit-supply-price" name="price" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="edit-supply-stock">Stock</label>
                        <input type="number" min="0" step="1" class="form-control" id="edit-supply-stock" name="stock" required>
                        <small class="text-muted">To decrease stock, enter a lower value; to increase, enter a higher value.</small>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // DataTables
    $('#supply-table, #inventory-table').DataTable({
        "ordering": false,
        "pageLength": 7,
        "lengthChange": false,
        "autoWidth": false,
        "info": false
    });

    $('#manage-supply').click(function(){
        uni_modal("Manage Supply", "manage_inv.php");
    });

    $('#add-supply').click(function(){
        uni_modal("Add New Supply", "add_supply.php");
    });

    // Edit
    $(document).on('click', '.edit-supply', function(){
        let $btn = $(this);
        $('#edit-supply-id').val($btn.data('id'));
        $('#edit-supply-name').val($btn.data('name'));
        $('#edit-supply-price').val($btn.data('price'));
        $('#edit-supply-stock').val($btn.data('stock'));
        $('#editSupplyModal').modal('show');
    });

    // Edit form submit
    $('#edit-supply-form').submit(function(e){
        e.preventDefault();
        var id = $('#edit-supply-id').val();
        var price = $('#edit-supply-price').val();
        var newStock = parseInt($('#edit-supply-stock').val());
        var oldStock = parseInt($('#supply-table tr[data-id="' + id + '"]').data('stock'));
        var stockDiff = newStock - oldStock;

        if(price === '' || isNaN(newStock) || newStock < 0) {
            alert_toast("Price and stock must not be empty.", "warning");
            return false;
        }

        start_load();
        $.ajax({
            url: 'ajax.php?action=edit_supply',
            method: 'POST',
            data: {
                id: id,
                price: price,
                stock: newStock,
                stock_diff: stockDiff
            },
            success: function(resp){
                if(resp == 1){
                    alert_toast("Supply updated successfully", "success");
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert_toast("Update failed: " + resp, "danger");
                    end_load();
                }
            }
        });
        $('#editSupplyModal').modal('hide');
    });

    // Delete
    $(document).on('click', '.delete-supply', function(){
        _conf("Are you sure to delete this supply?", "delete_supply", [$(this).attr('data-id')]);
    });
});

window.delete_supply = function($id){
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_supply',
        method: 'POST',
        data: {id: $id},
        success: function(resp){
            if(resp == 1){
                alert_toast("Supply successfully deleted", "success");
                setTimeout(() => location.reload(), 1200);
            } else {
                alert_toast("Failed to delete supply", "danger");
                end_load();
            }
        },
        error: function(){
            alert_toast("An error occurred", "danger");
            end_load();
        }
    });
}
</script>