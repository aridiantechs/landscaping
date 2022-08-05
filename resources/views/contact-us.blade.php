<!DOCTYPE html>
<!-- saved from url=(0054)https://colorlib.com/etc/cf/ContactFrom_v12/index.html -->
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Contact Us</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/png" href="https://colorlib.com/etc/cf/ContactFrom_v12/images/icons/favicon.ico">

  {{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/bootstrap.min.css') }}"> --}}

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

  <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/animate.css') }}">

  <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/hamburgers.min.css') }}">

  <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/select2.min.css') }}">

  <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/util.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/contact_us/main.css') }}">

  <meta name="robots" content="noindex, follow">
  <script defer="" referrerpolicy="origin" src="{{ asset('js/contact_us/s.js.download') }}"></script>
</head>

<body>
  <div class="bg-contact100" style="background-image: url(&#39;{{ asset('css/contact_us/bg-01.jpg') }}&#39;);">
    <div class="container-contact100">
      <div class="wrap-contact100">
        <div class="contact100-pic js-tilt" data-tilt=""
          style="transform: perspective(300px) rotateX(0deg) rotateY(0deg); will-change: transform;">
          <img src="{{ asset('css/contact_us/web_icon.png') }}" alt="IMG">
        </div>
        <form class="contact100-form validate-form" method="post" action="{{route('contact-us.store')}}" data-form-title="CONTACT US">
          @csrf
          <span class="contact100-form-title">
            Contact Us
          </span>
          <input type="hidden" data-form-email="true">
          <div class="wrap-input100">
            <input class="input100" type="text" name="name"  required="" placeholder="Name*" data-form-field="Name">
            <span class="focus-input100"></span>
            <span class="symbol-input100">
              <i class="fa fa-user" aria-hidden="true"></i>
            </span>
          </div>
          @if($errors->has('name'))
            <p class="text-danger pl-4 pb-2">{{$errors->first('name')}}</p>
          @endif
          <div class="wrap-input100">
            <input class="input100" type="email" name="email" required="" placeholder="Email*">
            <span class="focus-input100"></span>
            <span class="symbol-input100">
              <i class="fa fa-envelope" aria-hidden="true"></i>
            </span>
          </div>
          @if($errors->has('email'))
            <p class="text-danger pl-4 pb-2">{{$errors->first('email')}}</p>
          @endif
          <div class="wrap-input100">
            <input class="input100" type="tel" name="phone" required=""  placeholder="Phone*" data-form-field="Phone">
            <span class="focus-input100"></span>
            <span class="symbol-input100">
              <i class="fa fa-phone" aria-hidden="true"></i>
            </span>
          </div>
          @if($errors->has('phone'))
            <p class="text-danger pl-4 pb-2">{{$errors->first('phone')}}</p>
          @endif
          <div class="wrap-input100">
            <textarea class="input100" name="message" placeholder="Message" rows="7"
            data-form-field="Message"></textarea>
            <span class="focus-input100"></span>
          </div>
          @if($errors->has('message'))
            <p class="text-danger pl-4 pb-2">{{$errors->first('message')}}</p>
          @endif
          <div class="container-contact100-form-btn">
            <button type="submit" class="contact100-form-btn">
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/contact_us/jquery-3.2.1.min.js.download') }}"></script>

  <script src="{{ asset('js/contact_us/popper.js.download') }}"></script>
  <script src="{{ asset('js/contact_us/bootstrap.min.js.download') }}"></script>

  <script src="{{ asset('js/contact_us/select2.min.js.download') }}"></script>

  <script src="{{ asset('js/contact_us/tilt.jquery.min.js.download') }}"></script>
  <script>
    $('.js-tilt').tilt({
			scale: 1.1
		})
  </script>

  <script src="{{ asset('js/contact_us/main.js.download') }}"></script>

  <script async="" src="{{ asset('js/contact_us/js') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-23581568-13');
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
    integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
    crossorigin="anonymous"></script>
  <script>
    $(document).ready(function () {
        @if(session()->has('success'))
            toastr.success("{{session()->get('success')}}");
        @endif
        @if(session()->has('error'))
            toastr.error("{{session()->get('error')}}");
        @endif

    });
  </script>


</body>

</html>