<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    
</head>
<body style="background-color:rgb(0, 255, 60) ">
    <section>
          <div class="row mt-5">
            <div class="col-sm-12 col-md-12">
              <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6 mx-auto">
                  <div>
                    <h2 class="text-center text-white mb-3">CONTACT US</h2>
                  </div>
                  <form method="post" action="{{route('contact-us.store')}}" data-form-title="CONTACT US">
                    @csrf
                    <input type="hidden" data-form-email="true">
                    <div class="form-group pl-2 pr-2">
                      <input type="text" class="form-control" name="name" required="" placeholder="Name*" data-form-field="Name">
                      
                        @if($errors->has('name'))
                            <span class="text-danger">{{$errors->first('name')}}</span>
                        @endif
                    </div>
                    <div class="form-group pl-2 pr-2">
                      <input type="email" class="form-control" name="email" required="" placeholder="Email*" data-form-field="Email">
                      @if($errors->has('email'))
                            <span class="text-danger">{{$errors->first('email')}}</span>
                        @endif
                    </div>
                    <div class="form-group pl-2 pr-2">
                      <input type="tel" class="form-control" name="phone" placeholder="Phone" data-form-field="Phone">
                      @if($errors->has('phone'))
                            <span class="text-danger">{{$errors->first('phone')}}</span>
                        @endif
                    </div>
                    <div class="form-group pl-2 pr-2">
                      <textarea class="form-control" name="message" placeholder="Message" rows="7" data-form-field="Message"></textarea>
                      @if($errors->has('message'))
                            <span class="text-danger">{{$errors->first('message')}}</span>
                        @endif
                    </div>
                    <div class="form-group pl-2 pr-2">
                      <button type="submit" class="btn btn-lg btn-danger">submit</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
      </section>
    
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous"></script>
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