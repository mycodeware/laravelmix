{{-- profile menu Modal --}}

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
      <a class="dropdown-item" href="javascript:void(0);">My Account</a>
      <a class="dropdown-item" href="" data-toggle="modal" data-target="">Change Password</a>
      <a class="dropdown-item" href="javascript:void(0);">Registered Devices</a>
      <a class="dropdown-item" href="javascript:void(0);">Payment History</a>
      <a class="dropdown-item" href="javascript:void(0);">Contact Us</a>
      <a class="dropdown-item" href="{{route('logout')}}">Logout</a>
    </div>
  </div>