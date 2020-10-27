<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <link rel="shortcut icon" href="{{{ asset('images/favicon.ico') }}}">
</head>
<body>
  <div class="Cwrapper">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="Cleftpart">
            <h1>Add Contacts</h1>
            <!--<span>Home  |  My Avoo  |  Import contact</span>-->
          </div>
          <div class="Unavbg">
            <div class="row">
              <div class="col-md-12">
                <ul class="Ulinenav clearfix">
                  <li><a href="logout">Logout</a></li>                  
                </ul>
              </div>
            </div>
          </div>
          <form action="import-sim" method="POST" name="import_contacts" id="import_contacts" enctype='multipart/form-data'>
            {{ csrf_field() }}
            <div class="Paymentsbg ctablebg clearfix">
              <div class="row">
                
                <div class="col-md-12">
                  <div class="addCformbg">
                    <h2>Import Contacts</h2>
                    <div class="row">
                      <input type="file" required name="file" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
             <?php  /* <div class="col-md-12"><div class="popTbox"><i>Browse Excel File</i>
              <div class="uploadbg">
              <input class="upload-path" disabled />
              <label class="upload">
              <input type="file" required name="file" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/><span>BROWSE</span></label>
              </div>
              </div></div>
              <?php */ ?>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="addbtnsbg"><a href="#" class="cancelbtn">CANCEL</a><button class="greenbtn" type="submit">SUBMIT</button></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

</div>
</div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $(".Payresptopmenu").click(function () {
      $(".paymenu > ul").stop().slideToggle(300);
    });

    $(window).resize(function () {
      var w = $(window).width();
      if ($(window).width() > 992) {
        $(".paymenu > ul").removeAttr("style");
      }
      else {
        $(".paymenu > ul").stop().slideToggle(300);
      }
    });
    if ($(window).width() <= 992) {
      $(".paymenu > ul a.active").parent("li").remove();
    }
    
    $('.upload input[type="file"]').on('change', function() {
      console.log($(this).val());
      $('.upload-path').val(this.value.replace('C:\\fakepath\\', ''));

    });	
  });	
</script> 
</body>
</html>
