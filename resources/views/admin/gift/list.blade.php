@extends('admin.layout.app')
@section('title', 'Gift List')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pb-3 " style="height:50px;padding-top: 0.8rem !important">
                        <h6 class="text-white text-capitalize ps-3">Gift table</h6>
                    </div>
                </div>

                <div class="custom-margin-auto">
                    <button type="button" class="btn bg-gradient-primary mt-2 custom-button-class" data-bs-toggle="modal" data-bs-target="#addModal">
                        Add Gift
                    </button>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="gift_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Image</th>
                                    <th>Coin</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gifts as $key=>$gift)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td><img src="{{$gift->gift_image}}" style="height: 50px;width: 50px;margin-left: 10px;"></img></td>
                                    <td>{{$gift->coin}}</td>
                                    <td>
                                        <a href="" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{$gift->id}}" data-value="{{$gift->image}}" data-coin="{{$gift->coin}}">
                                            <i class="material-icons opacity-10">edit</i>
                                        </a>
                                        <a href="{{route('gift.delete',['id' => $gift->id])}}">
                                            <i class="material-icons opacity-10">delete</i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal" id="addModalLabel">Add Gift</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="addForm"  action="{{ route('gift.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group input-group-outline my-3">
                                    <input type="file" class="form-control1" name="image" onfocus="focused(this)" accept="image/*" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Coin</label>
                                    <input type="number" class="form-control" name="coin" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <div class="add-error-message text-danger"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal" id="editModalLabel">Update Gift</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="editForm" action="{{ route('gift.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" class="form-control editId" name="id">
                                <div class="input-group input-group-outline my-3">
                                    <input type="file" class="form-control1" name="image" onfocus="focused(this)" accept="image/*" onfocusout="defocused(this)" autocomplete="off">
                                    <span class="image-name"></span>
                                    <input type="hidden" class="editInput" name="image" required readonly>
                                    <!-- <input type="hidden" name="image"> -->
                                </div>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Coin</label>
                                    <input type="number" class="form-control editInput coin" name="coin" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <div class="edit-error-message text-danger"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-primary">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('#gift_list_table').DataTable();
        var addInputFields = $('#addForm input[type="number"]');
        var editInputFields = $('#editForm input[type="number"]');
        var addErrorMessage = $('.add-error-message');
        var editErrorMessage = $('.edit-error-message');
        
        addInputFields.on('input', function() {
            addErrorMessage.text(''); 
        });
        
        editInputFields.on('input', function() {
            editErrorMessage.text(''); 
        });
        
        var addFileFields = $('#addForm input[type="file"]');
        var editFileFields = $('#editForm input[type="file"]');
      
        addFileFields.on('change', function() {
            addErrorMessage.text(''); 
        });
        
        editFileFields.on('change', function() {
            editErrorMessage.text(''); 
        });

        $('#editModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var age = button.data('value');
            var input = $('.editInput');
            input.val(age);

            var span = $('.image-name');
            span.text(age);

            var coin = button.data('coin');
            var inputId = $('.editInput.coin');
            inputId.val(coin);

            var id = button.data('id');
            var inputId = $('.editId');
            inputId.val(id);
            $('.modal-body #editForm .input-group').addClass('focused is-focused is-filled');
        });
    });
</script>
@endsection