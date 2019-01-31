@extends('main')
@section('content')
<div class="full-list">
    
    <h5 class="page-titles">Account Details</h5>
    <div class="account-details-wrapper">
        <div class="account-details bg-white">
            <div class="user-pic">
                <img src="https://placeimg.com/150/150/people" class="img-fluid"/>
            </div>
            <div class="user-info">
                <div class="form-row">
                        
                    <div class="form-group col-md-6">
                        <label for="user_name">Name</label>
                        <input class="form-control" type="text" name="user_name" id="user_name" value="{{$userData['name']}}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="user_email">Email</label>
                        <input class="form-control" type="email" name="user_email" id="user_email" value="{{$userData['userEmail']}}" readonly>
                    </div>
                </div>
                    <div class="form-group">
                    <label for="user_password">Password</label>
                    <input class="form-control" type="password" name="user_password" id="user_password" value="*****" readonly>
                </div>
            </div>
            <button class="btn btn-solid" data-toggle="modal" data-target="#modal_change-password">Change Password</button>
            

             
        </div>
    </div>
    
</div>
@stop