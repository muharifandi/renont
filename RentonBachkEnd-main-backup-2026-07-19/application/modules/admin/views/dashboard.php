<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="http://www.daterangepicker.com/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://www.daterangepicker.com/daterangepicker.css" />

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Analitik</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>
    
    <section class="content">
        <div class="row">
            <center>
                <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;width:350px;margin:5px; ">
                    <center>
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                    </center>
                </div>
            </center>
            
        </div>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="total_transaction">-</h3>
                        <p>Transaksi</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="total_register">-</h3>
                        <p>Pendaftaran Baru</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="total_partner">-</h3>
                        <p>Mitra Baru</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="total_claim_reward">-</h3>
                        <p>Klaim Hadiah</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cube"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="total_topup">-</h3>
                        <p>Permintaan Topup</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-arrow-down-a"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">        
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="total_withdraw">-</h3>
                        <p>Permintaan Pencairan</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-arrow-up-a"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-6 col-xs-12">
                
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="total_admin_fee_transaction">-</h3>
                        <p>Pendapatan Admin</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-6 col-xs-12">
                
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="total_income_promote_transaction">-</h3>
                        <p>Pendapatan Promosi / Iklan</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-6 col-xs-12">
                
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="total_agent_commission">-</h3>
                        <p>Komisi Agen</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-12 col-xs-12">
                
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="revenue">-</h3>
                        <p>Pendapatan Bersih</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
                    </div>
                    <a href="#" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Grafik Total Pendapatan</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart">
                            <div class="table-responsive">
                            <!-- Sales Chart Canvas -->
                            <canvas id="revenueChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            
        </div>
        

    </section>
    
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var chartOptions = {
            //Boolean - If we should show the scale at all
            showScale: true,
            //Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines: true,
            //String - Colour of the grid lines
            scaleGridLineColor: "rgba(0,0,0,.05)",
            //Number - Width of the grid lines
            scaleGridLineWidth: 1,
            //Boolean - Whether to show horizontal lines (except X axis)
            scaleShowHorizontalLines: true,
            //Boolean - Whether to show vertical lines (except Y axis)
            scaleShowVerticalLines: true,
            //Boolean - Whether the line is curved between points
            bezierCurve: false,
            //Number - Tension of the bezier curve between points
            bezierCurveTension: 0.3,
            //Boolean - Whether to show a dot for each point
            pointDot: true,
            //Number - Radius of each point dot in pixels
            pointDotRadius: 4,
            //Number - Pixel width of point dot stroke
            pointDotStrokeWidth: 1,
            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
            pointHitDetectionRadius: 20,
            //Boolean - Whether to show a stroke for datasets
            datasetStroke: true,
            //Number - Pixel width of dataset stroke
            datasetStrokeWidth: 2,
            //Boolean - Whether to fill the dataset with a color
            datasetFill: true,
            //String - A legend template
            legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: false,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: false};
            
            var start = moment().subtract(29, 'days');
            var end = moment();
            
            function cb(start, end) {
                data(start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'));
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
            
            
            
            var revenueChart = null;
            function setRevenueChart(datasource)
            {
                if(revenueChart !== null)
                {
                    revenueChart.destroy();
                }
                var revenueChartCanvas = $("#revenueChart").get(0).getContext("2d");
                // This will get the first returned node in the jQuery collection.
                
                
                var revenueChartData = {
                    labels: datasource.label,
                    datasets: [{
                        label: "2016",
                        fillColor: "rgba(60,141,188,0.9)",
                        strokeColor: "rgba(60,141,188,0.8)",
                        pointColor: "#3b8bba",
                        pointStrokeColor: "rgba(60,141,188,1)",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(60,141,188,1)",
                        data: datasource.value
                    }]};
                    //Create the line chart
                    revenueChart = new Chart(revenueChartCanvas).Line(revenueChartData, chartOptions);
                    
            }
            
            function data(start, end)
            {
                $.ajax({
                    url: <?php echo '"'.base_url().'admin/dashboard/get_summary/"';?>+start+"/"+end ,
                    method: 'get',
                    dataType: 'json',
                    }).done(function(result){
                    $('#total_register').html(result.total_register);
                    $('#total_partner').html(result.total_partner);
                    $('#total_transaction').html(result.total_transaction);
                    $('#total_claim_reward').html(result.total_claim_reward);
                    $('#total_admin_fee_transaction').html(result.total_admin_fee_transaction);
                    $('#total_income_promote_transaction').html(result.total_income_promote_transaction);
                    $('#total_agent_commission').html(result.total_agent_commission);
                    $('#revenue').html(result.revenue);
                    $('#total_topup').html(result.total_topup);
                    $('#total_withdraw').html(result.total_withdraw);
                    
                    setRevenueChart(result.chart.revenue);
                });
            }
            
            $('#reportrange').daterangepicker({
                showCustomRangeLabel: true,
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
            
            cb(start, end);
            
        });
    </script>
    
