@extends('template.template')

@section('title', 'Sentimen Analisis')


@section('css')
    <link rel="stylesheet" href="{{asset('bower_components')}}/datatables.net-bs/css/dataTables.bootstrap.min.css">
@endsection

@section('content')
    <!-- Main content -->
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Testing Data Baru</h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form action="{{ url('/knn') }}" method="post" id="formTest">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="box-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Tweet</label>
                    <input type="text" name="data" class="form-control" id="testing" placeholder="Masukan Tweet..." value="{{ !empty($test)?$test : null  }}" {{ !empty($test)? "disabled" : ""  }}>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Class</label>
                    <input type="text" name="class" class="form-control" id="class" value="{{ !empty($class)?$class : NULL  }}" disabled>
                </div>
            </div>
            <!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-success">Testing</button>
                <button type="reset" class="btn btn-danger" id="reset">Reset</button>
            </div>
        </form>
    </div>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Training KNN</h3>
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
                @foreach($data as $t)
                    <tr>
                        <td>{{ $no  }}</td>
                        <td>{{ $t->data  }}</td>
                        <td>{{ $t->class }}</td>
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
                });

                function Reset() {
                    $('#testing').prop('disabled', false);
                    $('#reset').closest('form').trigger("reset");
                };

                $("#reset").click(function() {
//                    $('#testing').prop('disabled', false);
                    $(this).closest('form').find("input[type=text]").val("");
//                    $(this).closest('form').trigger("reset");
                });

            </script>
@endsection