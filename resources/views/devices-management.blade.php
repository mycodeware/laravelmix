@extends('main')
@section('content')
<div class="full-list">
    <h5 class="page-titles mb-4">Devices Management</h5>
    <p class="page-desc">List of Devices added in your Account. You can add max. 5 Devices in your Account.</p>
    <div class="devices-list">
        <div class="device mb-4">
            <div class="device-ico-wrapper">
            <img src="{{ asset("images/ico-laptop.svg") }}" alt="">
            </div>
            <div class="device-info">
                <h5>Desktop / Laptop</h5>
                <p class="mb-0">Linked on 10/11/2018</p>
            </div>
            <button class="btn txt-link">Remove</button>
        </div>
        <div class="device mb-4">
            <div class="device-ico-wrapper">
            <img src="{{ asset("images/ico-laptop.svg") }}" alt="">
            </div>
            <div class="device-info">
                <h5>Desktop / Laptop</h5>
                <p class="mb-0">Linked on 10/11/2018</p>
            </div>
            <button class="btn txt-link">Remove</button>
        </div>
        <div class="device mb-4">
            <div class="device-ico-wrapper">
            <img src="{{ asset("images/ico-laptop.svg") }}" alt="">
            </div>
            <div class="device-info">
                <h5>Desktop / Laptop</h5>
                <p class="mb-0">Linked on 10/11/2018</p>
            </div>
            <button class="btn txt-link">Remove</button>
        </div>
        <div class="device mb-4">
            <div class="device-ico-wrapper">
            <img src="{{ asset("images/ico-laptop.svg") }}" alt="">
            </div>
            <div class="device-info">
                <h5>Desktop / Laptop</h5>
                <p class="mb-0">Linked on 10/11/2018</p>
            </div>
            <button class="btn txt-link">Remove</button>
        </div>
    </div>
    
</div>
@stop