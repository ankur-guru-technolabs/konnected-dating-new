@extends('admin.layout.app')
@section('title', 'Faq List')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Faq Table</h6>
                    </div>
                </div>
                <div class="custom-margin-auto">
                    <a href="{{route('faq.faq-add')}}">
                        <button type="button" class="btn bg-gradient-primary mt-2 custom-button-class">
                            Add FAQ
                        </button>
                    </a>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0 mx-3">
                        <table id="faq_list_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($faqs as $key=>$faq)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{$faq->question}}</td>
                                        <td>{{$faq->category->name ?? "-"}}</td>
                                        <td>
                                            <a href="{{route('faq.faq-edit',['id' => $faq->id])}}">
                                                <i class="material-icons opacity-10">edit</i>
                                            </a>
                                            <a href="{{route('faq.faq-delete',['id' => $faq->id])}}">
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
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('#faq_list_table').DataTable();
    });
</script>
@endsection