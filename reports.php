<?php
include 'db_connect.php'; 
$d1 = (isset($_GET['d1']) ? date("Y-m-d",strtotime($_GET['d1'])) : date("Y-m-d")) ;
$d2 = (isset($_GET['d2']) ? date("Y-m-d",strtotime($_GET['d2'])) : date("Y-m-d")) ;
$data = $d1 == $d2 ? $d1 : $d1. ' - ' . $d2;
?>
<div class="container-fluid py-4">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form id="filter-form">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold">Date From</label>
                                <input type="date" class="form-control" name="d1" id="d1" value="<?php echo date("Y-m-d",strtotime($d1)) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold">Date To</label>
                                <input type="date" class="form-control" name="d2" id="d2" value="<?php echo date("Y-m-d",strtotime($d2)) ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary btn-block mb-2" type="button" id="filter">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary btn-block mb-2" type="button" id="print"><i class="fa fa-print"></i> Print</button>
                        </div>
                    </div>
                </form>
                <hr>
                <div class="row" id="print-data">
                    <div style="width:100%">
                        <p class="text-center mt-2">
                            <span style="font-size:1.2em;font-weight:600;">Laundry Management System Report</span>
                        </p>
                        <p class="text-center mb-3">
                            <span style="font-size:1.1em"><b><?php echo $data ?></b></span>
                        </p>
                    </div>
                    <div class="col-12 px-0">
                        <table class='table table-bordered table-hover bg-white mb-0'>
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th class="text-right">Amount Tendered</th>
                                    <th class="text-right">Amount Change</th>
                                    <th class="text-right">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $total = 0;
                                    $total_tendered = 0;
                                    $total_change = 0;
                                    $qry = $conn->query("SELECT * FROM laundry_list where pay_status = 1 and date(date_created) between '$d1' and '$d2' ");
                                    while($row=$qry->fetch_assoc()):
                                        $total += $row['total_amount'];
                                        $total_tendered += $row['amount_tendered'];
                                        $total_change += $row['amount_change'];
                                ?>
                                <tr>
                                    <td><?php echo date("M d, Y",strtotime($row['date_created'])) ?></td>
                                    <td><?php echo ucwords($row['customer_name']) ?></td>
                                    <td class='text-right'><?php echo number_format($row['amount_tendered'],2) ?></td>
                                    <td class='text-right'><?php echo number_format($row['amount_change'],2) ?></td>
                                    <td class='text-right'><?php echo number_format($row['total_amount'],2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right font-weight-bold">Total</td>
                                    <td class="text-right font-weight-bold"><?php echo number_format($total,2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    #print-data p {
        display: none;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
</style>
<noscript>
    <style>
        #div{
            width:100%;
        }
        table {
            border-collapse: collapse;
            width:100% !important;
            background: #fff !important;
        }
        tr,th,td{
            border:1px solid black;
        }
        .text-right{
            text-align: right;
        }
        .text-center{
            text-align: center;
        }
        p{
            margin:unset;
        }
        #div p {
            display: block;
        }
        p.text-center {
            text-align: -webkit-center;
        }
    </style>
</noscript>
<script>
    $('#filter').click(function(){
        location.replace('index.php?page=reports&d1='+$('#d1').val()+'&d2='+$('#d2').val())
    })
    $('#print').click(function(){
        var newWin = window.open('', '', 'height=500,width=800');
        var _html = $('#print-data').clone();
        var ns = $('noscript').clone();
        newWin.document.write('<html><head><title>Print Report</title>');
        newWin.document.write(ns.html());
        newWin.document.write('</head><body>');
        newWin.document.write(_html.html());
        newWin.document.write('</body></html>');
        newWin.document.close();
        newWin.print();
        setTimeout(function(){
            newWin.close()
        },1500)
    })
</script>