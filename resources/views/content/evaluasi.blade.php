@extends('template.template')

@section('title', 'Sentimen Analisis')


@section('css')
    <link rel="stylesheet" href="{{asset('bower_components')}}/datatables.net-bs/css/dataTables.bootstrap.min.css">
@endsection

@section('content')
    <!-- Main content -->
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Confusion Matrix</h3>
        </div>
        <table class="table table-responsive" width="100%">
            <tr>
                <th>&nbsp;</th>
                <th>Prediksi Positif</th>
                <th>Predifsi Negatif</th>
            </tr>
            <tr>
                <th>Actual Positif</th>
                <th>{{ $confusion['TP']  }}</th>
                <th>{{ $confusion['FN']  }}</th>
            </tr>
            <tr>
                <th>Actual Negatif</th>
                <th>{{ $confusion['FP']  }}</th>
                <th>{{ $confusion['TN']  }}</th>
            </tr>
            @php
                $accur = (($confusion['TP']+$confusion['TN'])/($confusion['TP']+$confusion['TN']+$confusion['FP']+$confusion['FN'])) * 100;
                $prec = ($confusion['TP']) /($confusion['TP']+$confusion['FP']) * 100;
                $recall = ($confusion['TP']) /($confusion['FN']+$confusion['TP']) * 100;
            @endphp

        </table>
        <table class="table table-bordered">
            <tr>
                <th colspan="2">
                    <h4>Evaluasi</h4>
                </th>
            </tr>
            <tr>
                <th>Akurasi</th>
                <th>{{ number_format((float)$accur, 2, '.', '') }} %</th>
            </tr>
            <tr>
                <th>Precision</th>
                <th>{{ number_format((float)$prec, 2, '.', '')  }} %</th>
            </tr>
            <tr>
                <th>Recall</th>
                <th>{{ number_format((float)$recall, 2, '.', '')  }} %</th>
            </tr>
        </table>

        <!-- /.box-header -->
    </div>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Evaluasi</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Data</th>
                    <th>Actual</th>
                    <th>Prediksi</th>
                </tr>
                </thead>
                <tbody>
                @php $no=1; $index=0; $p=0; $n=0; @endphp
                @foreach($data as $t)
                    <tr>
                        <td>{{ $no  }}</td>
                        <td>{{ $t->data  }}</td>
                        <td>{{ $t->class }}</td>
                        @php if($t->class == "positif") $p++; else $n++; @endphp
                        <td>{{ $prediksi[$index] }}</td>
                        @php $no++; $index++; @endphp
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
        <br>
        <br>
        <br>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Confusion Matrix</h3>
            </div>
            <div class="box-body">
                <div class="chart">
                    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Dataset</h3>
            </div>
            <div class="box-body">
                <div class="chart">
                    <div id="pieContainer" style="height: 370px; width: 100%;"></div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
        
        <script src="{{asset('bower_components')}}/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="{{asset('bower_components')}}/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
        {{--<script src="{{asset('bower_components')}}/chart.js/Chart.js"></script>--}}
        <script src="{{asset('bower_components')}}/chart.js/canvasjs.min.js"></script>
        {{--<script src="{{asset('bower_components')}}/fastclick/lib/fastclick.js"></script>--}}


        <script>
            window.onload = function () {

                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    theme: "light2", // "light1", "light2", "dark1", "dark2"
                    title:{
                        text: "Evaluasi"
                    },
                    axisY: {
                        title: "Nilai"
                    },
                    data: [{
                        type: "column",
                        showInLegend: true,
                        legendMarkerColor: "grey",
                        legendText: "Evaluasi",
                        dataPoints: [
                            { y: {{  $accur }}, label: "Akurasi" },
                            { y: {{ $prec  }},  label: "Precision" },
                            { y: {{ $recall  }},  label: "Recall" },
                        ]
                    }]
                });
                chart.render();
                var pie = new CanvasJS.Chart("pieContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Pengelompokan Data"
                    },
                    data: [{
                        type: "pie",
                        startAngle: 240,
                        indexLabel: "{label} {y}",
                        dataPoints: [
                            {y: {{$p}}, label: "Positif"},
                            {y: {{$n}}, label: "Negatif"}
                        ]
                    }]
                });
                pie.render();
            }
            $(function () {
                $('#example1').DataTable()
                $('#example2').DataTable({
                    'paging'      : true,
                    'lengthChange': false,
                    'searching'   : false,
                    'ordering'    : true,
                    'info'        : true,
                    'autoWidth'   : false
                })

            });

        </script>

        

@endsection

        