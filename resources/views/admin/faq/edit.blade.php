@extends('admin.layout.app')
@section('title', 'Faq Edit')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Faq Edit</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="p-4">
                        <form method="post" action="{{route('faq.faq-update')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" class="form-control" value="{{$faqs->id ?? ''}}" name="id">
                            <div class="row">
                                <div class="input-group input-group-dynamic mb-4 focused is-focused">
                                    <label class="form-label">Question</label>
                                    <input type="text" class="form-control" value="{{$faqs->question ?? ''}}" name="question" autocomplete="off">
                                </div>
                                @if($errors->has('question'))
                                    <small class="text-danger mb-2" >
                                        {{ $errors->first('question') }}
                                    </small>
                                    @endif
                            </div>
                            <div class="row">
                                <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-control" style="padding: 9px;">
                                        @foreach($categories as $cat)
                                            <option value="{{$cat->id}}" {{($cat->id == $faqs->category_id) ? 'selected' : ''}}>{{$cat->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($errors->has('category'))
                                    <small class="text-danger" >
                                        {{ $errors->first('category') }}
                                    </small>
                                @endif
                            </div>
                            <div class="row">
                                <label class="form-label">Answer</label>
                                <div class="input-group input-group-dynamic mb-4 focused is-focused">
                                    <textarea id="answer" name="answer" class="form-control validate" name="answer"  rows="5" placeholder="Answer" spellcheck="false">{{ $faqs->answer ?? ''}}</textarea>
                                </div>
                                @if($errors->has('answer'))
                                    <small class="text-danger mt-2">
                                        {{ $errors->first('answer') }}
                                    </small>
                                @endif
                            </div>
                            <div class="row pt-4">
                                <div class="col s12 m12 input-field">
                                    <button type="submit" class="btn bg-gradient-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.ckeditor').ckeditor();
    });
</script>
@endsection