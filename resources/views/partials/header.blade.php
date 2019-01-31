<div class="header-inner containerFull bg-dark">
  <nav class="navbar navbar-expand-lg">
    <a class="brand" href="/"><img class="img-fluid" src="{{ asset('images/channel-fight.png') }}" alt="logo"></a>
    

    <div class="header-right-wrapper">
      <ul class="main-navigation p-0 m-0">
        <li>
          <a href="javascript:void(0);">Categories <img src="{{ asset('images/ico-angle-down.svg') }}" alt=""></a>
          <div class="sub-menu">
            <div class="col-md-6">
              <ol>
                <li><a href="javascript:void(0);">Kung Fu</a></li>
                <li><a href="javascript:void(0);">Deadly Revenge</a></li>
                <li><a href="javascript:void(0);">Hitman &amp; Assassin</a></li>
                <li><a href="javascript:void(0);">Asin Gangster action</a></li>
                <li><a href="javascript:void(0);">The age of war</a></li>
                <li><a href="javascript:void(0);">Trailers</a></li>
              </ol>
            </div>
            <div class="col-md-6">
                <ol>
                  <li><a href="javascript:void(0);">Samurai &amp; Wuxia</a></li>
                  <li><a href="javascript:void(0);">Modern Warfare</a></li>
                  <li><a href="javascript:void(0);">Action Comedy</a></li>
                  <li><a href="javascript:void(0);">Compelling Drama</a></li>
                  <li><a href="javascript:void(0);">Jean-Claude Van Damme</a></li>
                </ol>
              </div>
          </div>
        </li>
        @if (Auth::check()==false)
          <li>
            <a href="{{url('library')}}">Library</a>
          </li>
          <li>
            <a href="{{url('watchlist')}}">Watchlist</a>
          </li>
          <li>
            <a href="{{url('social')}}">Social</a>
          </li>
        @endif
      </ul>
      <div class="header-right-inner ml-auto">

        <form class="frm-search-box form-inline">
          <input class="search-box" type="search" placeholder="Search" aria-label="Search">
        </form>
        @if (Auth::check()==false)
          <button class="btn btn-outline btn-sm px-4" data-toggle="modal" data-target="#modal_login">Login</button>
          <button class="btn btn-outline btn-sm ml-3 px-4" data-toggle="modal" data-target="#modal_registration">Join</button>
        @else
          <button class="txt-link btn-cart mr-3" data-toggle="modal" data-target="">Cart <i class="ico ico-cart"></i></button>
          <div class="my-account-dropdown-outer dropdown">
            <button
              class="dropdown-toggle"
              type="button"
              id="my_account_dropdown"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
                <img
                  src="https://placeimg.com/150/150/people"
                  class="img-fluid"
                />
            </button>
            <div class="dropdown-menu" aria-labelledby="my_account_dropdown">
              <a class="dropdown-item" href="{{route('memberProfile')}}">My Account</a>
             
      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_change-password">Change Password</a>

              <a class="dropdown-item" href="javascript:void(0);">Registered Devices</a>
              <a class="dropdown-item" href="javascript:void(0);">Payment History</a>
              <a class="dropdown-item" href="javascript:void(0);">Contact Us</a>
              <a class="dropdown-item" href="{{route('logout')}}">Logout</a>
            </div>
          </div>
          
        @endif
      </div>
    </div>
  </nav>
</div>
@include('partials.loginModal')
@include('partials.profileDetailsModal')
@include('partials.registrationModal')
@include('partials.resetPasswordModal')
@include('partials.changePasswordModal')
 {{--@include('partials.forgotPasswordModal')--}}
