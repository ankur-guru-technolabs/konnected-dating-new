@extends('admin.layout.app')
@section('title', 'Subscription Edit')
@section('content')
<style>
    .dropdown:not(.dropdown-hover) .dropdown-menu.show {
  margin-top: 0 !important;
}
.onlyread
{
    cursor: not-allowed !important;
}
</style>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Subscription Edit</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="p-4">
                        <form method="post" action="{{route('subscription.subscription-update')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" class="form-control" value="{{$subscription->id ?? ''}}" name="id">
                            <input type="hidden" class="form-control" value="{{$subscription->plan_type ?? ''}}" name="plan_type">
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control onlyread" value="{{$subscription->title ?? ''}}" name="title" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('title'))
                                        <small class="text-danger" >
                                            {{ $errors->first('title') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Description</label>
                                        <input type="text" class="form-control onlyread" value="{{$subscription->description ?? ''}}" name="description" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('description'))
                                        <small class="text-danger" >
                                            {{ $errors->first('description') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Month</label>
                                        <input type="number" class="form-control onlyread" value="{{$subscription->month ?? ''}}" name="month" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('month'))
                                        <small class="text-danger" >
                                            {{ $errors->first('month') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Plan Duration (in days)</label>
                                        <input type="number" class="form-control onlyread" value="{{$subscription->plan_duration ?? ''}}" name="plan_duration" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('plan_duration'))
                                        <small class="text-danger" >
                                            {{ $errors->first('plan_duration') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Google Plan Id </label>
                                        <input type="text" class="form-control onlyread" value="{{$subscription->google_plan_id ?? ''}}" name="google_plan_id" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('google_plan_id'))
                                        <small class="text-danger" >
                                            {{ $errors->first('google_plan_id') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Apple Plan Id </label>
                                        <input type="text" class="form-control onlyread" value="{{$subscription->apple_plan_id ?? ''}}" name="apple_plan_id" autocomplete="off" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('apple_plan_id'))
                                        <small class="text-danger" >
                                            {{ $errors->first('apple_plan_id') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="mb-2 focused is-focused"> 
                                        <select class="selectpicker form-control" multiple title="Choose filters" name="search_filters[]">
                                            <option value="gender" {{ in_array('gender', $subscription->allowed_subscription) ? 'selected' : '' }}>Gender</option>
                                            <option value="height" {{ in_array('height', $subscription->allowed_subscription) ? 'selected' : '' }}>Height</option>
                                            <option value="age" {{ in_array('age', $subscription->allowed_subscription) ? 'selected' : '' }}>Age</option>
                                            <option value="ethnticity" {{ in_array('ethnticity', $subscription->allowed_subscription) ? 'selected' : '' }}>Ethnticity</option>
                                            <option value="faith" {{ in_array('faith', $subscription->allowed_subscription) ? 'selected' : '' }}>Faith</option>
                                            <option value="education" {{ in_array('education', $subscription->allowed_subscription) ? 'selected' : '' }}>Education</option>
                                            <option value="industry" {{ in_array('industry', $subscription->allowed_subscription) ? 'selected' : '' }}>Industry</option>
                                            <option value="salary" {{ in_array('salary', $subscription->allowed_subscription) ? 'selected' : '' }}>Salary</option>
                                            <option value="body_type" {{ in_array('body_type', $subscription->allowed_subscription) ? 'selected' : '' }}>Body Type</option>
                                            <option value="children" {{ in_array('children', $subscription->allowed_subscription) ? 'selected' : '' }}>Children</option>
                                            <option value="hobbies" {{ in_array('hobbies', $subscription->allowed_subscription) ? 'selected' : '' }}>Hobbies</option>
                                            <option value="location" {{ in_array('location', $subscription->allowed_subscription) ? 'selected' : '' }}>Location</option>
                                        </select>
                                    </div>
                                    @if($errors->has('search_filters'))
                                        <small class="text-danger" >
                                            {{ $errors->first('search_filters') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Like Per Day</label>
                                        <input type="number" class="form-control" value="{{$subscription->like_per_day ?? ''}}" name="like_per_day" autocomplete="off">
                                    </div>
                                    @if($errors->has('like_per_day'))
                                        <small class="text-danger" >
                                            {{ $errors->first('like_per_day') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Video Call</label>
                                        <select name="video_call" class="form-control">
                                            <option value="yes" {{$subscription->video_call == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->video_call == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('video_call'))
                                        <small class="text-danger" >
                                            {{ $errors->first('video_call') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Who Likes Me</label>
                                        <select name="who_like_me" class="form-control">
                                            <option value="yes" {{$subscription->who_like_me == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->who_like_me == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('who_like_me'))
                                        <small class="text-danger" >
                                            {{ $errors->first('who_like_me') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Who Views Me</label>
                                        <select name="who_view_me" class="form-control">
                                            <option value="yes" {{$subscription->who_view_me == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->who_view_me == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('who_view_me'))
                                        <small class="text-danger" >
                                            {{ $errors->first('who_view_me') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Profile Badge</label>
                                        <select name="profile_badge" class="form-control">
                                            <option value="yes" {{$subscription->profile_badge == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->profile_badge == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('profile_badge'))
                                        <small class="text-danger" >
                                            {{ $errors->first('profile_badge') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Read Receipt</label>
                                        <select name="read_receipt" class="form-control">
                                            <option value="yes" {{$subscription->read_receipt == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->read_receipt == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('read_receipt'))
                                        <small class="text-danger" >
                                            {{ $errors->first('read_receipt') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Travel Mode</label>
                                        <select name="travel_mode" class="form-control">
                                            <option value="yes" {{$subscription->travel_mode == 'yes' ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$subscription->travel_mode == 'no' ? 'selected' : ''}}>No</option>
                                        </select>
                                    </div>
                                    @if($errors->has('travel_mode'))
                                        <small class="text-danger" >
                                            {{ $errors->first('travel_mode') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Undo Profile Per Day</label>
                                        <input type="number" class="form-control" value="{{$subscription->undo_profile ?? ''}}" name="undo_profile" autocomplete="off">
                                    </div>
                                    @if($errors->has('undo_profile'))
                                        <small class="text-danger" >
                                            {{ $errors->first('undo_profile') }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-6 pt-3">
                                    <div class="input-group input-group-dynamic mb-2 focused is-focused">
                                        <label class="form-label">Price</label>
                                        <input type="number" class="form-control onlyread" value="{{$subscription->price ?? ''}}" name="price" autocomplete="off"  step="any" readonly="readonly" data-bs-toggle="tooltip" data-bs-placement="top" title="Not editable" data-container="body" data-animation="true">
                                    </div>
                                    @if($errors->has('price'))
                                        <small class="text-danger" >
                                            {{ $errors->first('price') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                           
                           
                            <div class="row pt-4">
                                <div class="col s12 m12 input-field">
                                    <button type="submit" class="btn bg-gradient-primary">Update</button>
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

@endsection