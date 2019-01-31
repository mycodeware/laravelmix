@extends('main') 
@section('header') 
@parent
<div class="terms">
    <div class="termsImg2">
        <img
            class="terms-background img-fluid"
            src="https://www.channelfight.com/wp-content/uploads/2017/02/Raid-2.jpg"
            style="height:350px;"
        />
        <!--remove height once dynamic proper height image added-->
    </div>
</div>
@endsection 

@section('content') 
<div class="movieList">
        <div class="row">
            <div class="col-3"><h3 class="movieTitle">Gangster Movies</h3></div>
            <div class="buttonRent col-9">
                <button type="button" class="btn btn-primary first-btn">
                    Rent $9.00
                </button>
                <button type="button" class="btn btn-primary second-btn">
                    Buy $9.00
                </button>
                <button type="button" class="btn btn-primary third-btn">
                    Save +
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-3"><p class="flims">Bundle | 4 Flims</p></div>
            <div class="col-9">
                <div class="icons text-right">
                    <img
                        src="{{ asset('images/facebook-social.png') }}"
                        class="fbWidth"
                        alt="facebook"
                    />
                    <img
                        src="{{ asset('images/twitter-social.png') }}"
                        class="twitWidth"
                        alt="twitter"
                    />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p class="flims">channel fight brings you the best Ganster movie</p>
            </div>
        </div>
        <div class="row movieListImg">
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
            <div class="col-2">
                <img
                    src="https://images-na.ssl-images-amazon.com/images/I/81UbgSBGUAL._SY741_.jpg"
                    class="img-fluid"
                    alt=""
                />
            </div>
        </div>
    </div>
@stop
