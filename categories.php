<?php include('db_connect.php'); ?> 

<div class="container-fluid py-4">
    <div class="col-lg-12">
        <div class="row">
            <!-- FORM Panel -->
            <div class="col-md-4 mb-4">
                <form id="manage-category">
                    <div class="card shadow-sm">
                        <div class="card-header font-weight-bold">
                            Laundry Category
                        </div>
                        <div class="card-body pb-2">
                            <input type="hidden" name="id">
                            <div class="form-group mb-2">
                                <label class="mb-1 font-weight-bold">Category</label>
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Category name">
                            </div>
                            <div class="form-group mb-0">
                                <label class="mb-1 font-weight-bold">Price</label>
                                <input type="number" class="form-control form-control-sm text-right" min="1" step="any" name="price" placeholder="Price">
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-center">
                            <button class="btn btn-sm btn-primary" type="submit" id="save-btn">Save</button>
                            <button class="btn btn-sm btn-secondary" type="button" id="cancel-btn">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- FORM Panel -->

            <!-- Table Panel -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header font-weight-bold">
                        Categories
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:5%" class="text-center">#</th>
                                        <th style="width:65%" class="text-center">Name</th>
                                        <th style="width:30%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $cats = $conn->query("SELECT * FROM laundry_categories ORDER BY id ASC");
                                    while($row = $cats->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <b><?php echo htmlspecialchars($row['name']) ?></b><br>
                                            <small>₱ <?php echo number_format($row['price'], 2) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <button 
                                                class="btn btn-outline-primary btn-sm edit_cat" 
                                                type="button" 
                                                data-id="<?php echo $row['id'] ?>" 
                                                data-name="<?php echo htmlspecialchars($row['name']) ?>" 
                                                data-price="<?php echo $row['price'] ?>"
                                                title="Edit"
                                            >
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button 
                                                class="btn btn-outline-danger btn-sm delete_cat" 
                                                type="button" 
                                                data-id="<?php echo $row['id'] ?>"
                                                title="Delete"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table Panel -->
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Prevent save if empty
    $('#manage-category').submit(function(e){
        e.preventDefault();
        var name = $.trim($('[name="name"]').val());
        var price = $.trim($('[name="price"]').val());
        if (name === '' || price === '' || parseFloat(price) < 1) {
            alert_toast("Category and price are required. Price must be at least 1.", 'warning');
            return false;
        }
        $.ajax({
            url: 'ajax.php?action=save_category',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp){
                if(resp == 1){
                    alert_toast("Data successfully added", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1200);
                } else if(resp == 2){
                    alert_toast("Data successfully updated", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1200);
                }
            }
        });
    });

    $('#cancel-btn').click(function(){
        $('#manage-category').get(0).reset();
        $('[name="id"]').val('');
    });

    $('.edit_cat').click(function(){
        var cat = $('#manage-category');
        cat.get(0).reset();
        cat.find("[name='id']").val($(this).attr('data-id'));
        cat.find("[name='name']").val($(this).attr('data-name'));
        cat.find("[name='price']").val($(this).attr('data-price'));
    });

    $('.delete_cat').click(function(){
        _conf("Are you sure to delete this category?", "delete_cat", [$(this).attr('data-id')]);
    });
});

window.delete_cat = function($id){
    start_load()
    $.ajax({
        url: 'ajax.php?action=delete_category',
        method: 'POST',
        data: { id: $id },
        success: function(resp){
            if(resp == 1){
                alert_toast("Data successfully deleted", 'success');
                setTimeout(function(){
                    location.reload();
                }, 1200);
            } else {
                alert_toast("Failed to delete category", 'danger');
                end_load();
            }
        },
        error: function(){
            alert_toast("An error occurred", 'danger');
            end_load();
        }
    });
}
</script>