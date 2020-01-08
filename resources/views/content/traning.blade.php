@extends('template.template')

@section('title', 'AdminLTE')

@section('css')
    <link rel="stylesheet" href="{{asset('bower_components')}}/datatables.net-bs/css/dataTables.bootstrap.min.css">
@endsection

@section('content')
    <!-- Main content -->
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Training</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Data</th>
                        <th>Class</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $no=1 @endphp
                    @foreach($train as $t)
                        <tr>
                            <td>{{ $no  }}</td>
                            <td>{{ $t->data  }}</td>
                            <td>{{ $t-> class }}</td>
                            @php $no++ @endphp
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
@stop

@section('script')
    <script src="{{asset('bower_components')}}/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="{{asset('bower_components')}}/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
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
        })
    </script>
@endsection