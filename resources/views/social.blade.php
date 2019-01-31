@extends('main')
@section('content')
    <div id="social">
        <div class="row">
            <!--feeds-tab-->
            <div class="col-12 col-md-3">
                <ul class="feedsNav">
                    <h6 class="feedHeader">FEEDS</h6>
                      <hr class="celebLine">
                    <li class="barFeed"><p><i class="fa fa-bars" aria-hidden="true"></i><a href="#">My Feeds</a></p></li>
                    <li class="barFeed"><p><i class="fa fa-bars" aria-hidden="true"></i><a href="#">All Feeds</a></p></li>
                    <li class="barFeed"><p><i class="fa fa-bars" aria-hidden="true"></i><a href="#">Customise My Feeds</a></p></li>
                </ul>
            </div>
            <!--feeds images-->
            <div class="col-12 col-md-6">
                <div class="feedsMain mb-3">
                     <div class="feedsDesc">
                            <p class="imgText"><img src="http://cdn.akc.org/content/hero/lab_puppy_hero.jpg" alt="Avatar" class="avatar"><span  class="pl-2 blueColor">Channel fight </span> <br><span class="pl-2">july20 ,2018</span>
                                <img class="img-fluid social-icon" src="{{ asset('images/facebook-social.png') }}" alt="logo">
                            </p>
                            <h6>Incredible MMI fighter!</h6>
                            <img class="feedMainImg" src="https://4.bp.blogspot.com/-LKJr0Zlpo_Q/W-MmRYtfZeI/AAAAAAAABBU/D55JzBnGJz8_fC1_yP-JlR5Epb8t2HDEQCLcBGAs/s1600/JACKIE%2BQuotes%2B1.png" alt="newsfeed">
                                 <ul class="shares">
                                    <li><i class="fa fa-thumbs-o-up  mr-2" aria-hidden="true"></i>like</li>
                                    <li><i class="fa fa-comment-o mr-2" aria-hidden="true"></i>comment</li>
                                    <li><i class="fa fa-share-square-o mr-2" aria-hidden="true"></i>share</li>
                                    <li class="float-right shareNum">1.k shares</li>
                                    <li  class="float-right views">2.5 views</li>
                                 </ul>
                 </div>
                 </div> <!--feedsmain-->
                   <div class="feedsMain mb-3">
                     <div class="feedsDesc">
                            <p class="imgText"><img src="http://cdn.akc.org/content/hero/lab_puppy_hero.jpg" alt="Avatar" class="avatar"><span  class="pl-2 blueColor">Channel fight </span> <br><span class="pl-2">july20 ,2018</span>
                                <img class="img-fluid social-icon" src="{{ asset('images/facebook-social.png') }}" alt="logo">
                            </p>
                            <h6>Incredible MMI fighter!</h6>
                            <img class="feedMainImg" src="https://4.bp.blogspot.com/-LKJr0Zlpo_Q/W-MmRYtfZeI/AAAAAAAABBU/D55JzBnGJz8_fC1_yP-JlR5Epb8t2HDEQCLcBGAs/s1600/JACKIE%2BQuotes%2B1.png" alt="newsfeed">
                                 <ul class="shares">
                                    <li><i class="fa fa-thumbs-o-up  mr-2" aria-hidden="true"></i>like</li>
                                    <li><i class="fa fa-comment-o mr-2" aria-hidden="true"></i>comment</li>
                                    <li><i class="fa fa-share-square-o mr-2" aria-hidden="true"></i>share</li>
                                    <li class="float-right shareNum">1.k shares</li>
                                    <li  class="float-right views">2.5 views</li>
                                 </ul>
                 </div>
                 </div>
            </div>
            <!--celebrities to follow section-->
            <div class="col-12 col-md-3">
                <div class="celebFollow">
                    <h6 class="celebHeader">CELEBRITESS TO FOLLOW</h6>
                    <hr class="celebLine">
                    <!--search celebrities-->
                        <div class="input-group mb-3 round-input">
                                 <input type="text" class="form-control form-control2" placeholder="Search" aria-label="Recipient's username" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                       </div>
                       <!--list of celebrity-->
                       <div class="OuterCeleBox">
                                <p class="celebBox">
                                    <img src="http://cdn.akc.org/content/hero/lab_puppy_hero.jpg" alt="Avatar" class="avatar">
                                    <span  class="pl-2 blueColor celebName">Jackie Chan </span>
                                    <i class="fa fas fa-check social-icon"></i>
                                 </p>
                       </div>
                           <div class="OuterCeleBox">
                                <p class="celebBox">
                                    <img src="http://cdn.akc.org/content/hero/lab_puppy_hero.jpg" alt="Avatar" class="avatar">
                                    <span  class="pl-2 blueColor celebName">Jackie Chan </span>
                                    <i class="fa fas fa-check social-icon"></i>
                                 </p>
                       </div>

                           <div class="OuterCeleBox">
                                <p class="celebBox">
                                    <img src="http://cdn.akc.org/content/hero/lab_puppy_hero.jpg" alt="Avatar" class="avatar">
                                    <span  class="pl-2 blueColor celebName">Jackie Chan </span>
                                    <i class="fa fas fa-check social-icon"></i>
                                 </p>
                       </div>

                </div>
            </div> <!--celebrities to follow section end -->
        </div>
    </div>
@stop
