<?php
ob_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "laundry_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch expenditures ordered by date (latest first)
$results = $conn->query("SELECT * FROM expenditures ORDER BY date DESC");

// Fetch unique existing details for the select dropdown
$detailsRes = $conn->query("SELECT DISTINCT details FROM expenditures WHERE details IS NOT NULL AND details != '' ORDER BY details ASC");
$detailOptions = [];
while($dr = $detailsRes->fetch_assoc()) {
    if(trim($dr['details'])) $detailOptions[] = htmlspecialchars($dr['details']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expenditure Management</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
    .card, .modal-content, #addModal input, #addModal select, #addModal textarea {
        background: #fff !important;
    }
    #addModal input,
    #addModal select,
    #addModal textarea {
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        box-shadow: none !important;
        outline: none !important;
        background-color: #fff !important;
        width: 100%;
        min-height: 38px;
        padding: 8px 12px;
        margin-bottom: 8px;
        transition: border-color 0.3s;
    }
    #addModal input:focus,
    #addModal select:focus,
    #addModal textarea:focus {
        border-color: #86b7fe !important;
        background-color: #f8f9fa !important;
    }
    #custom-alert {
        position: fixed;
        top: 20px;
        right: 40px;
        z-index: 99999;
        min-width: 320px;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-table, #printable-table * {
            visibility: visible;
        }
        #printable-table {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <h2 class="text-center mb-4">Expenditure Data</h2>
    <div id="custom-alert"></div>
    <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-secondary mr-2" onclick="printTable()">🖨️ Print</button>
        <button class="btn btn-success" data-toggle="modal" data-target="#addModal">+ Add Expenditure</button>
    </div>
    <div id="printable-table">
        <table class="table table-striped table-bordered table-sm bg-white">
            <thead class="thead-light">
                <tr>
                    <th>Details</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Image</th>
                    <th class="text-center" style="width:120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()): ?>
                <tr id="expenditure-row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['details']) ?></td>
                    <td>₱<?= number_format($row['total'], 2) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td>
                      <?php if ($row['image_path']): ?>
                        <a href="<?= htmlspecialchars($row['image_path']) ?>" target="_blank"><img src="<?= htmlspecialchars($row['image_path']) ?>" alt="img" style="max-width:60px;max-height:60px"></a>
                      <?php else: ?>
                        <span class="text-muted">No Image</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm edit-btn"
                            data-id="<?= $row['id'] ?>"
                            data-details="<?= htmlspecialchars($row['details'], ENT_QUOTES) ?>"
                            data-total="<?= $row['total'] ?>"
                            data-date="<?= $row['date'] ?>"
                            data-image="<?= htmlspecialchars($row['image_path']) ?>">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-outline-danger btn-sm delete-btn"
                            data-id="<?= $row['id'] ?>">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="addExpenditureForm" enctype="multipart/form-data" class="needs-validation" novalidate>
    <input type="hidden" name="id" id="expenditure_id">
    <div class="modal-content bg-white">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Add/Edit Expenditure</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <select name="details" id="details" class="form-control" required>
              <option value="">-- Select Details --</option>
              <?php foreach($detailOptions as $opt): ?>
                <option value="<?= $opt ?>"><?= $opt ?></option>
              <?php endforeach; ?>
              <option value="__custom__">Other (Type New...)</option>
            </select>
            <input type="text" name="custom_details" id="custom_details" class="form-control mt-2 d-none" placeholder="Type new details here">
            <div class="invalid-feedback">Please select or enter the details.</div>
          </div>
          <div class="mb-3">
            <label for="total" class="form-label">Total Amount</label>
            <input type="number" step="0.01" name="total" id="total" class="form-control" required>
            <div class="invalid-feedback">Please enter the total amount.</div>
          </div>
          <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" id="date" class="form-control" required>
            <div class="invalid-feedback">Please select a date.</div>
          </div>
          <div class="mb-3">
            <label for="image" class="form-label">Receipt Image (optional)</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div id="current-image" class="mt-2"></div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="saveExpenditure">Save</button>
      </div>
    </div>
    </form>
  </div>
</div>

<script src="assets/jquery/jquery-3.6.0.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function printTable() {
    window.print();
}

$('.edit-btn').on('click', function() {
    $('#addModalLabel').text('Edit Expenditure');
    $('#expenditure_id').val($(this).data('id'));

    let details = $(this).data('details');
    let found = false;
    $('#details option').each(function(){
        if($(this).val() == details){
            $(this).prop('selected', true);
            found = true;
        }
    });
    if(!found && details){
        $('#details').val('__custom__');
        $('#custom_details').val(details).removeClass('d-none');
    } else {
        $('#custom_details').addClass('d-none').val('');
    }

    $('#total').val($(this).data('total'));
    $('#date').val($(this).data('date'));
    let img = $(this).data('image');
    $('#current-image').html(img ? `<a href="${img}" target="_blank"><img src="${img}" style="max-width:50px"></a>` : '');
    $('#addModal').modal('show');
});

$('#addModal').on('hidden.bs.modal', function() {
    $('#addModalLabel').text('Add Expenditure');
    $('#addExpenditureForm')[0].reset();
    $('#expenditure_id').val('');
    $('#current-image').html('');
    $('#custom_details').addClass('d-none').val('');
    $('#details').val('');
});

$('#details').on('change', function(){
    if($(this).val() === '__custom__'){
        $('#custom_details').removeClass('d-none').focus();
    } else {
        $('#custom_details').addClass('d-none').val('');
    }
});

// Bootstrap form validation
(() => {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})();

$('#addExpenditureForm').on('submit', function(e) {
    e.preventDefault();

    // Client-side quick required check before AJAX
    let details = $('#details').val();
    if(details === '__custom__') details = $('#custom_details').val();
    if(!details || !$('#total').val() || !$('#date').val()){
        alert_toast("Please fill in all required fields.", "danger");
        return;
    }

    if($('#details').val() === '__custom__'){
        if(!$('#custom_details').val()){
            alert_toast('Please enter the new details name', 'danger');
            $('#custom_details').focus();
            return;
        }
        $('<input>').attr({
            type: 'hidden',
            name: 'details',
            value: $('#custom_details').val()
        }).appendTo('#addExpenditureForm');
    }
    let formData = new FormData(this);
    $.ajax({
        url: 'ajax.php?action=save_expenditure',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            if(resp == 1){
                $('#addModal').modal('hide');
                alert_toast("Data successfully added", 'success');
                setTimeout(()=>location.reload(), 1200);
            }else if(resp == 2){
                $('#addModal').modal('hide');
                alert_toast("Data successfully updated", 'success');
                setTimeout(()=>location.reload(), 1200);
            }else if(resp == 0){
                alert_toast("Please fill in all required fields.", 'danger');
            }else{
                alert_toast("Failed to save data!", 'danger');
            }
        }
    });
});

// Delete expenditure
$('.delete-btn').on('click', function() {
    _conf("Are you sure to delete this expenditure?", "delete_expenditure", [$(this).attr('data-id')]);
});

window.delete_expenditure = function($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_expenditure',
        method: 'POST',
        data: { id: $id },
        success: function(resp) {
            if(resp == 1) {
                alert_toast("Expenditure successfully deleted", 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert_toast("Failed to delete expenditure", 'danger');
                end_load();
            }
        },
        error: function() {
            alert_toast("An error occurred", 'danger');
            end_load();
        }
    });
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>    