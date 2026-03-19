<?php include 'db_connect.php' ?>
<div class="container-fluid py-4">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row mb-3 align-items-center">
                    <div class="col-lg-8 col-md-12 d-flex align-items-center">
                        <label class="mb-1 mr-2 font-weight-bold text-primary" style="font-size:1.1rem">Filter Status:</label>
                        <button class="btn btn-sm btn-outline-secondary ml-1 status-filter-btn" data-status="all">All</button>
                        <button class="btn btn-sm btn-outline-secondary ml-1 status-filter-btn" data-status="0">Pending</button>
                        <button class="btn btn-sm btn-outline-secondary ml-1 status-filter-btn" data-status="1">Processing</button>
                        <button class="btn btn-sm btn-outline-secondary ml-1 status-filter-btn" data-status="2">To Claim</button>
                        <button class="btn btn-sm btn-outline-secondary ml-1 status-filter-btn" data-status="3">Claimed</button>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-right text-md-left mt-2 mt-lg-0">
                        <button class="btn btn-primary btn-sm" type="button" id="new_laundry">
                            <i class="fa fa-plus mr-1"></i> New Laundry
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered bg-white" id="laundry-list">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">Date</th>
                                <th class="text-center">Customer Name</th>
                                <th class="text-center">Customer Number</th>
                                <th class="text-center">Supplies Used</th>
                                <th class="text-center">Supply Qty</th>
                                <th class="text-center">Remarks</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Amount Tendered</th>
                                <th class="text-center">Amount Change</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sup_arr = [];
                            $supply = $conn->query("SELECT * FROM supply_list");
                            while($row = $supply->fetch_assoc()) {
                                $sup_arr[$row['id']] = $row['name'];
                            }

                            $laundry_supplies = [];
                            $supply_used_q = $conn->query("SELECT * FROM supplies_used");
                            while($row = $supply_used_q->fetch_assoc()) {
                                $laundry_supplies[$row['laundry_id']][] = [
                                    'supply_id' => $row['supply_id'],
                                    'qty' => $row['qty']
                                ];
                            }

                            $list = $conn->query("SELECT * FROM laundry_list order by status asc, id asc");
                            while($row = $list->fetch_assoc()):
                                $laundry_id = $row['id'];
                            ?>
                            <tr data-status="<?php echo $row['status'] ?>" data-id="<?php echo $row['id'] ?>">
                                <td><?php echo date("M d, Y", strtotime($row['date_created'])) ?></td>
                                <td><?php echo ucwords($row['customer_name']) ?></td>
                                <td><?php echo ucwords($row['customer_number']) ?></td>
                                <td class="text-center">
                                    <?php
                                    if (!empty($laundry_supplies[$laundry_id])) {
                                        $sup_names = [];
                                        foreach ($laundry_supplies[$laundry_id] as $s) {
                                            $sup_names[] = isset($sup_arr[$s['supply_id']])
                                                ? htmlspecialchars($sup_arr[$s['supply_id']])
                                                : 'Unknown';
                                        }
                                        echo implode(', ', $sup_names);
                                    } else {
                                        echo '<span class="text-muted">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if (!empty($laundry_supplies[$laundry_id])) {
                                        $qtys = [];
                                        foreach ($laundry_supplies[$laundry_id] as $s) {
                                            $qtys[] = $s['qty'];
                                        }
                                        echo implode(', ', $qtys);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo ucwords($row['remarks']) ?></td>
                                <td class="text-right"><?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
                                <td class="text-right"><?php echo number_format($row['amount_tendered'] ?? 0, 2); ?></td>
                                <td class="text-right"><?php echo number_format($row['amount_change'] ?? 0, 2); ?></td>
                                <td class="text-center">
                                    <?php if($row['status'] == 0): ?>
                                        <span class="badge badge-secondary badge-status" data-id="<?php echo $row['id'] ?>" data-status="0" style="cursor:pointer">Pending</span>
                                    <?php elseif($row['status'] == 1): ?>
                                        <span class="badge badge-primary badge-status" data-id="<?php echo $row['id'] ?>" data-status="1" style="cursor:pointer">Processing</span>
                                    <?php elseif($row['status'] == 2): ?>
                                        <span class="badge badge-info badge-status" data-id="<?php echo $row['id'] ?>" data-status="2" style="cursor:pointer">To Claim</span>
                                    <?php elseif($row['status'] == 3): ?>
                                        <span class="badge badge-success badge-status" data-id="<?php echo $row['id'] ?>" data-status="3" style="cursor:pointer">Claimed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm edit_laundry" data-id="<?php echo $row['id'] ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete_laundry" data-id="<?php echo $row['id'] ?>">
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
</div>

<style>
    .status-filter-btn {
        border-radius: 20px !important;
        padding: 0.4rem 1rem !important;
        font-weight: 500 !important;
        border: 2px solid #e5e7eb !important;
        background: white !important;
        color: #6b7280 !important;
        transition: all 0.3s ease !important;
        margin: 0 0.25rem !important;
    }
    
    .status-filter-btn:hover {
        border-color: #6366f1 !important;
        color: #6366f1 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 6px rgba(99, 102, 241, 0.2) !important;
    }
    
    .status-filter-btn.btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
        border-color: #6366f1 !important;
        color: white !important;
        box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3) !important;
    }
    
    .status-filter-btn.btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 10px rgba(99, 102, 241, 0.4) !important;
    }
    
    .badge-status {
        cursor: pointer;
        transition: all 0.3s ease !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.8rem !important;
    }
    
    .badge-status:hover {
        transform: scale(1.1) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    }
</style>

<script>
    $('#new_laundry').click(function() {
        uni_modal('New Laundry', 'manage_laundry.php', 'mid-large')
    });

    $('.edit_laundry').click(function() {
        uni_modal('Edit Laundry', 'manage_laundry.php?id=' + $(this).attr('data-id'), 'mid-large')
    });

    $('.delete_laundry').click(function() {
        _conf("Are you sure to remove this data from list?", "delete_laundry", [$(this).attr('data-id')])
    });

    var table = $('#laundry-list').DataTable({
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [10] }
        ]
    });

    // Save filter selection to localStorage
    $('.status-filter-btn').on('click', function() {
        var status = $(this).data('status');
        localStorage.setItem('laundry_status_filter', status);
        $('.status-filter-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        if (status === "all") {
            table.column(9).search('').draw();
        } else {
            table.column(9).search('^' + getStatusText(status) + '$', true, false).draw();
        }
    });

    // Restore filter from localStorage on page load
    $(document).ready(function() {
        var status = localStorage.getItem('laundry_status_filter');
        if (status) {
            $('.status-filter-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
            $('.status-filter-btn[data-status="' + status + '"]').removeClass('btn-outline-secondary').addClass('btn-primary');
            if (status === "all") {
                table.column(9).search('').draw();
            } else {
                table.column(9).search('^' + getStatusText(status) + '$', true, false).draw();
            }
        } else {
            $('.status-filter-btn[data-status="all"]').addClass('btn-primary').removeClass('btn-outline-secondary');
        }
    });

    function getStatusText(status) {
        status = String(status);
        if (status === "0") return "Pending";
        if (status === "1") return "Processing";
        if (status === "2") return "To Claim";
        if (status === "3") return "Claimed";
        return "";
    }

    // Status badge click handler: update status and send SMS if needed
    $('#laundry-list').on('click', '.badge-status', function() {
        var badge = $(this);
        var id = badge.data('id');
        var currentStatus = parseInt(badge.attr('data-status'));
        if (currentStatus === 3) {
            alert_toast("Already claimed. Cannot update further.", 'info');
            return;
        }
        var nextStatus = currentStatus + 1;

        $.ajax({
            url: 'ajax.php?action=update_status',
            method: 'POST',
            data: { id: id, status: nextStatus },
            success: function(resp) {
                if (resp == 1) {
                    let statusText = ['Pending', 'Processing', 'To Claim', 'Claimed'];
                    let badgeClasses = ['badge-secondary', 'badge-primary', 'badge-info', 'badge-success'];

                    badge
                        .removeClass()
                        .addClass('badge badge-status ' + badgeClasses[nextStatus])
                        .text(statusText[nextStatus])
                        .data('status', nextStatus);

                    alert_toast("Status updated to " + statusText[nextStatus], 'success');

                    // Send SMS if status is "To Claim"
                    if (nextStatus === 2) {
                        let customerNumber = badge.closest('tr').find('td').eq(2).text().trim(); // Customer Number
                        let customerName = badge.closest('tr').find('td').eq(1).text().trim();   // Customer Name
                        let message = `Hi ${customerName}, your laundry is ready for pickup. Please claim it at your convenience. Thank you!`;

                       // Cloud SMS (unchanged)
$.ajax({
    url: 'function/sms.php',
    method: 'POST',
    data: {
        username: '7LTJF0',
        password: 'siginalngkalulu',
        message: message,
        number: customerNumber, 
        server: 'cloud'
    },
    success: function(smsResp) {
        alert_toast("Cloud SMS notification sent to customer.", 'info');
        console.log("Cloud SMS sent successfully:", smsResp);
    },
    error: function(err) {
        alert_toast("Failed to send Cloud SMS notification.", 'error');
        console.error("Failed to send Cloud SMS:", err);
    }
});

// Local SMS
$.ajax({
    url: 'function/sms.php',
    method: 'POST',
    data: {
        username: 'pas',
        password: 'siginalngkalulu',
        message: message,
        number: customerNumber,
        server: 'local'
    },
    success: function(smsResp) {
        alert_toast("Local SMS notification sent to customer.", 'info');
        console.log("Local SMS sent successfully:", smsResp);
    },
    error: function(err) {
        alert_toast("Failed to send Local SMS notification.", 'error');
        console.error("Failed to send Local SMS:", err);
    }
});
                    }

                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    alert_toast("Failed to update status. Please try again.", 'error');
                }
            },
            error: function() {
                alert_toast("Server error. Please try again.", 'error');
            }
        });
    });

    window.delete_laundry = function($id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_laundry',
            method: 'POST',
            data: { id: $id },
            success: function(resp) {
                if(resp == 1) {
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function() {
                        location.reload()
                    }, 1500)
                }
            }
        })
    }
</script>