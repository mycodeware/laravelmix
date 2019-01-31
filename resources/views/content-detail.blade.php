@extends('main')
@section('header')
    @parent
    <div class="play-movie-banner">
        <img class="img-fluid" src="{{ asset("images/banner-1.jpg") }}" alt="">
        <div class="btn-play-trailer-wrapper">
            <button class="btn btn-solid ">
                <img src="{{ asset("images/ico-play.svg") }}" alt=""> 
                <span>Play Trailer</span>
            </button>
        </div>
    </div>
@endsection
@section('content')
<div class="movie-details">
    <div class="movie-title-action-wrapper">
        <h2>Ip Man</h2>
        <div class="movie-actions">
            <button class="btn btn-solid-white">Rent £10.00</button>
            <button class="btn btn-solid">BUY £10.00</button>
            <button class="btn btn-outline">Save <span class="ico-add">+</span></button>
        </div>
    </div>
    <div class="movie-short-detail">
        <div class="rating-wrapper">
            <div class="star-rating">
                <fieldset class="rating">
                    <input type="radio" id="star5" name="rating" value="5" /><label class="full" for="star5" title="Awesome - 5 stars"></label>
                    <input type="radio" id="star4half" name="rating" value="4 and a half" /><label class="half" for="star4half" title="Pretty good - 4.5 stars"></label>
                    <input type="radio" id="star4" name="rating" value="4" /><label class="full" for="star4" title="Pretty good - 4 stars"></label>
                    <input type="radio" id="star3half" name="rating" value="3 and a half" /><label class="half" for="star3half" title="Meh - 3.5 stars"></label>
                    <input type="radio" id="star3" name="rating" value="3" /><label class="full" for="star3" title="Meh - 3 stars"></label>
                    <input type="radio" id="star2half" name="rating" value="2 and a half" /><label class="half" for="star2half" title="Kinda bad - 2.5 stars"></label>
                    <input type="radio" id="star2" name="rating" value="2" /><label class="full" for="star2" title="Kinda bad - 2 stars"></label>
                    <input type="radio" id="star1half" name="rating" value="1 and a half" /><label class="half" for="star1half" title="Meh - 1.5 stars"></label>
                    <input type="radio" id="star1" name="rating" value="1" /><label class="full" for="star1" title="Sucks big time - 1 star"></label>
                    <input type="radio" id="starhalf" name="rating" value="half" /><label class="half" for="starhalf" title="Sucks big time - 0.5 stars"></label>
                </fieldset>
                {{-- <span class="counter">(52) </span> --}}
            </div>
            {{-- <div class="outof"><span class="small-outof">5/25</span></div> --}}
        </div>
        <span>|</span>
        <p>2015</p>
        <span>|</span>
        <p>01h 44min</p>
        <span>|</span>
        <p>Martial Arts, Drama</p>
    </div>
    <p class="movie-desc">In this explosive third installment of the blockbuster martial arts series, when a band of brutal gangsters led by a crooked property developer make a play to take over the city, Master Ip is forced to take a stand. Fists will fly as some of the most incredible fight scenes ever filmed play out on the big screen in this soon-to-be classic of the genre.</p>
    <ul class="movie-list-details">
        <li>Languages: English</li>
        <li>Subtitles: English</li>
        <li>Casts: Donnie Yen, Zhang Jin, Lynn Hung, Patrick Tam, Karena Ng, Kent Cheng Bryan Leung, Louis Cheung, Danny Chan, Mike Tyson, Tats Lau</li>
        <li>Directors: Wilson Yip</li>
    </ul>
    <div class="comments-section">
        <div class="number-of-comments">31,961 Comments</div>
        <div class="add-comment-wrap">
            <div class="comment-user-ico">Sr</div>
            <textarea name="add_comment" id="add_comment" rows="1"></textarea>
        </div>
        <ul class="comments-list">
            <li>
                <div class="comment-user-ico">rd</div>
                <strong>Robert Downey</strong>
                <span>2 Months ago</span>
                <span>Nice Movie</span>
            </li>
            <li>
                <div class="comment-user-ico">rd</div>
                <strong>Chris Hems-worth</strong>
                <span>4 Months ago</span>
                <span>I love this too much</span>
            </li>
            <li>
                <div class="comment-user-ico">rd</div>
                <strong>Benedict Cumberbatch</strong>
                <span>4 Months ago</span>
                <span>I love this too much</span>
            </li>
        </ul>
    </div>
    <div class="card-slider mt-5">
            <div class="card-slider-inner">
                <h5 class="title-slider">
                    <a href="/category">
                        RECOMMENDED CONTENT<i class="fa fas fa-angle-right"></i>
                    </a>
                </h5>
                <div class="multi-item-carousel owl-carousel owl-theme">
                    <div class="item">
                        <a href="/content-detail">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                    <div class="item">
                        <a href="javascript:void(0)">
                            <img src="https://static.channelfight.com/Channel_Fight_Prod/267/296/the_grandmaster_prt.jpg" alt="" width="100%">
                            
                            <div class="card-overlay">
                                <p>The Grandmaster</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    
</div>
@stop