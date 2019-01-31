@extends('main')
@section('content')
<div class="payment">
    <div class="row">
        <!--list of items start-->
        <div class="col-6">
            <h5 class="page-titles">LIST OF ITEMS</h5>
            <div class="listBox">
            <p class="textList clearfix"><img class="float-left listImage" src="https://images-na.ssl-images-amazon.com/images/I/71WibLqQZTL._SY741_.jpg" class="img-fluid"> <span class="ml-3 mt-3 ipMan">IP Man 3 </span> <br> <span class="ml-3">Buy $10 </span></p>
           </div>
             <div class="listBox clearfix">
            <p class="textList"><img class="float-left listImage" src="https://images-na.ssl-images-amazon.com/images/I/71WibLqQZTL._SY741_.jpg" class="img-fluid"> <span class="ml-3 mt-3 ipMan">IP Man 3 </span> <br> <span class="ml-3">Buy $10 </span></p>
           </div>

        </div>   <!--list of items end-->
        <div class="col-6">
            <h5 class="page-titles">CHOOSE PAYMENT METHOD BELOW</h5>
                  <div class="paymentCont payment-method-yesflix">

                        <div class="paymentWrap">
                            <div class="btn-group paymentBtnGroup btn-group-justified" data-toggle="buttons">
                                <!--first method-->
                                <label class="btn paymentMethod active">
                                    <div class="method visa">
                                <img  src="{{ asset('images/credit-card.png') }}" class="img-fluid" alt="profile">
                                  <p class="pay">pay with credit card</p>
                                    </div>
                                        <input type="radio" name="options" id="stripeRadio" value="Stripe" checked>
                                             <div class="check-payment"><img  src="{{ asset('images/ic-check.svg') }}" class="img-fluid" alt="profile"></div>
                                </label>

                                <!--second method-->
                                <label class="btn paymentMethod ml-3">
                                    <div class="method master-card pay2">
                                          <img  src="{{ asset('images/paypal-yes.png') }}" class="img-fluid" alt="profile">
                                            <p class="pay pay4">pay with paypal</p>
                                    </div>
                                <input type="radio" name="options" id="stripeRadio" value="Stripe" checked>
                                             <div class="check-payment"><img  src="{{ asset('images/ic-check.svg') }}" class="img-fluid" alt="profile"></div>
                                </label>



                            </div>
                        </div>

                    </div>

    </div> <!--row-->
         </div>
    </div>
</div>

@endsection
@section('content')


@stop
