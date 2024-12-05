@extends('admin.layout.app')
@section('title', 'Coin List')
@section('content')
<style>.error{color:red}</style>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pb-3 " style="height:50px;padding-top: 0.8rem !important">
                        <h6 class="text-white text-capitalize ps-3">Coin table</h6>
                    </div>
                </div>

                <div class="custom-margin-auto">
                    <button type="button" class="btn bg-gradient-primary mt-2 custom-button-class" data-bs-toggle="modal" data-bs-target="#addModal1">
                        Add Coin
                    </button>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="coin_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Coin</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coins as $key=>$coin)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{$coin->coins}}</td>
                                    <td>{{$coin->price}}</td>
                                    <td>
                                        <a href="" data-bs-toggle="modal" data-bs-target="#editModal1" data-id="{{$coin->id}}" data-value="{{$coin->coins}}" data-price="{{$coin->price}}" data-google="{{$coin->google_plan_id}}" data-apple="{{$coin->apple_plan_id}}">
                                            <i class="material-icons opacity-10">edit</i>
                                        </a>
                                        <a href="{{route('coin.delete',['id' => $coin->id])}}">
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
            <div class="modal fade" id="addModal1" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal" id="addModalLabel">Add Coin</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="addForm1"  action="{{ route('coin.store') }}" method="POST">
                                @csrf
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Coin</label>
                                    <input type="number" class="form-control" name="coins" id="coins" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <p id="coins-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control" name="price" id="price" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <p id="price-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Google Plan Id</label>
                                    <input type="text" class="form-control" name="google_plan_id" id="google-plan-id" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <p id="google-plan-id-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Apple Plan Id</label>
                                    <input type="text" class="form-control" name="apple_plan_id" id="apple-plan-id" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <p id="apple-plan-id-error"></p>
                                <!-- <div class="add-error-message1 text-danger"></div> -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" form="addForm1" class="btn bg-gradient-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="editModal1" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal" id="editModalLabel">Update Coin</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="editForm1" action="{{ route('coin.update') }}" method="POST">
                                @csrf
                                <input type="hidden" class="form-control editId" name="id">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Coin</label>
                                    <input type="number" class="form-control editInput" name="coins" id="edit-coins" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <p id="edit-coins-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control editInput price" name="price" id="edit-price" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" step="0.01">
                                </div>
                                <p id="edit-price-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Google Plan Id</label>
                                    <input type="text" class="form-control editInput google_plan_id" name="google_plan_id" id="edit-google-plan-id" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <p id="edit-google-plan-id-error"></p>
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Apple Plan Id</label>
                                    <input type="text" class="form-control editInput apple_plan_id" name="apple_plan_id" id="edit-apple-plan-id" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <p id="edit-apple-plan-id-error"></p>
                                <!-- <div class="edit-error-message1 text-danger"></div> -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" form="editForm1" class="btn bg-gradient-primary">Update</button>
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
        $('#coin_list_table').DataTable();

        // Validation rules, messages, and errorPlacement
        const commonValidationOptions = {
            rules: {
                coins: {
                    required: true,
                },
                price: {
                    required: true,
                },
                google_plan_id: {
                    required: true,
                }, 
                apple_plan_id: {
                    required: true,
                }
            },
            messages: {
                coins: {
                    required: "Please enter your coins",
                },
                price: {
                    required: "Please enter your price",
                },
                google_plan_id: {
                    required: "Please enter your google plan id",
                }, 
                apple_plan_id: {
                    required: "Please enter your apple plan id",
                }
            },
            errorPlacement: function(error, element) {
                var elementId = element.attr('id'); 
                error.appendTo("#" + elementId + "-error");
            }
        };

        $('#editForm1').validate(commonValidationOptions);
        $('#addForm1').validate(commonValidationOptions);

        $('#addModal1').on('show.bs.modal', function(event) {
            $("#addForm1").validate().resetForm();
        });

        $('#editModal1').on('show.bs.modal', function(event) {
            $("#editForm1").validate().resetForm();
            var button = $(event.relatedTarget);
            var age = button.data('value');
            var input = $('.editInput');
            input.val(age);

            var price = button.data('price');
            var inputId = $('.editInput.price');
            inputId.val(price);
            
            var google_plan_id = button.data('google');
            var inputGoogleId = $('.editInput.google_plan_id');
            inputGoogleId.val(google_plan_id);
           
            var apple_plan_id = button.data('apple');
            var inputAppleId = $('.editInput.apple_plan_id');
            inputAppleId.val(apple_plan_id);

            var id = button.data('id');
            var inputId = $('.editId');
            inputId.val(id);
            $('.modal-body #editForm1 .input-group').addClass('focused is-focused is-filled');
        });
    });
</script>
@endsection