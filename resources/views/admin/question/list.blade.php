@extends('admin.layout.app')
@section('title', 'Question List')
@section('content')
<link rel='stylesheet' href='https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.css'>
<style type="text/css">
    .bootstrap-tagsinput .tag {
        margin-right: 5px;
        margin-bottom: 5px;
        color: white !important;
        background-color: #db2164;
        padding: .2em .6em .3em;
        font-size: 100%;
        font-weight: 700;
        vertical-align: baseline;
        border-radius: .25em;
        display: inline-block;
    }

    .bootstrap-tagsinput {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }

    .bootstrap-tagsinput input {
        padding: 0.2rem 0.5rem !important;
    }
</style>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pb-3 " style="height:50px;padding-top: 0.8rem !important">
                        <h6 class="text-white text-capitalize ps-3">Questions table</h6>
                    </div>
                </div>

                <div class="custom-margin-auto">
                    <button type="button" class="btn bg-gradient-primary mt-2 custom-button-class" data-bs-toggle="modal" data-bs-target="#addModal">
                        Add Question
                    </button>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="question_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Question</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $key=>$question)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{$question->question}}</td>
                                    <td>
                                        <a href="" class="edit-button" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{$question->id}}" data-value="{{$question->question}}">
                                            <i class="material-icons opacity-10">edit</i>
                                        </a>
                                        <a href="{{route('questions.question.delete',['id' => $question->id])}}">
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
                            <h5 class="modal-title font-weight-normal" id="addModalLabel">Add Question</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="addForm" action="{{ route('questions.question.store') }}" method="POST">
                                @csrf
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Question</label>
                                    <input type="text" class="form-control" name="question" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <div class="input-group input-group-outline my-3">
                                    <input type="text" class="form-control tagsinput" name="option" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" data-role="tagsinput" placeholder="Options">
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
                            <h5 class="modal-title font-weight-normal" id="editModalLabel">Update Question</h5>
                        </div>
                        <div class="modal-body">
                            <form role="form text-left" id="editForm" action="{{ route('questions.question.update') }}" method="POST">
                                @csrf
                                <input type="hidden" class="form-control editId" name="id">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Question</label>
                                    <input type="text" class="form-control editInput" name="question" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off">
                                </div>
                                <div class="input-group input-group-outline my-3">
                                    <input type="text" class="form-control tagsinput" name="option" onfocus="focused(this)" onfocusout="defocused(this)" required autocomplete="off" data-role="tagsinput" placeholder="Options">
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
<script src="{{ asset('assets/js/plugins/bootstrap-tagsinput.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#question_list_table').DataTable();
       
        $(document).on('click', '.edit-button', function() {
            var id = $(this).data('id');
            var question = $(this).data('value');

            $.ajax({
                url: "{{ route('questions.question.subQuestionList', ['id' => '__id__']) }}".replace('__id__', id),
                method: 'GET',
                success: function(response) {
                    var tags = response.tags.map(function(tag) {
                        return tag.option;
                    });
                    var tagsInput = $('#editModal').find('.tagsinput');
                    tagsInput.tagsinput('removeAll');
                    $.each(tags, function(index, value) {
                        tagsInput.tagsinput('add', value);
                    });
                },
                error: function() {
                    console.log('Error occurred during AJAX request');
                }
            });
            $('#editModal').find('.editId').val(id);
            $('#editModal').find('.editInput').val(question);
        });

    });
</script>


</script>
<!-- <script>
    $(function() {
        $('input').on('change', function(event) {
            var $element = $(event.target);
            var $container = $element.closest('.example');

            if (!$element.data('tagsinput'))
                return;

            var val = $element.val();
            if (val === null)
                val = "null";
            var items = $element.tagsinput('items');

            $('code', $('pre.val', $container)).html(($.isArray(val) ? JSON.stringify(val) : "\"" + val.replace('"', '\\"') + "\""));
            $('code', $('pre.items', $container)).html(JSON.stringify($element.tagsinput('items')));


        }).trigger('change');
    });
</script> -->
@endsection