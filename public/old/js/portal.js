 new WOW().init();
$(document).ready(function() {
  $('a[href="#"]').click(function (e) { e.preventDefault(); });	
$(".leftnav, .header, .Cwrapper").addClass("Open");
$(".navclick").click(function () {
	if ($(".leftnav").hasClass("Open")) {
       $(".leftnav, .header, .Cwrapper").removeClass("Open");
     }else{
	   $(".leftnav, .header, .Cwrapper").addClass("Open");
		 }
	 
});	

if ($(window).width() < 1200) {
   $(".leftnav, .header, .Cwrapper").removeClass("Open");
}
else {
   $(".leftnav, .header, .Cwrapper").addClass("Open");
}

var winwidth = 0;
 $(window).resize(function(){
    if(winwidth != $(window).width()){
       if($(window).width() < 1200){
	     $(".leftnav, .header, .Cwrapper").removeClass("Open");
		}else{
		 $(".leftnav, .header, .Cwrapper").addClass("Open");
		}	
    winwidth = $(window).width();
   }
  });


	
$(".leftnav ul > li.drop").click(function(){
		$(".leftnav ul > li.drop").removeClass('open');
		$(".leftnav ul > li .bigdrop").slideUp();
		
		  if ($(this).find("ul").is(":visible")) {
                    $(this).removeClass('open')
                    $(this).find(".bigdrop").slideUp();
                }
                else {
                    $(this).addClass('open')
                    $(this).find(".bigdrop").slideDown();
                }
			

		});
       $(".leftnav ul > li.drop > .bigdrop").click(function(e) {
                e.stopPropagation();
            });	


$(".plansecs .c35 > span,.plansecs .c24 > span").click(function(){
	$(this).toggleClass("On");
	$(this).parent().parent().find(".honmob").slideToggle();
	
	});

$(".addmore > a").click(function(){$(".overlaydark").show();$(".ADevicebg").show();});	
$("em#ADclose").click(function(){$(".ADevicebg,.overlaydark").hide();});

$("#userclick").click(function(){
	$("#udrop").slideToggle(200);
	
	});
$("#moreclicks").click(function(){
	$(this).toggleClass("L");
	$("#morepoint").slideToggle();
});
$(".mailsmsbg .mshcheck").click(function(){
	$(this).parent().toggleClass("active");
});

$("ul#CreCon > li").click(function(){
	$("ul#CreCon > li").removeClass("active");
	$(this).addClass("active");
});
	
$("#firstTab").click(function(){
	$(".tabContent").hide();
	$("#ConDetails").show();
});
$("#secondTab").click(function(){
	$(".tabContent").hide();
	$("#AdvSettings").show();
});	
$("#thirdTab").click(function(){
	$(".tabContent").hide();
	$("#AddNoti").show();
});	
$("#fourthTab").click(function(){
	$(".tabContent").hide();
	
	$("#ScheduleCon").show();
});	
$(".Stab").click(function(){
   var nameid     = $('#nameid').val(); 
   var dtp_input1 = $('#dtp_input1').val(); 
   var checkednumbers ='';
   $("input[name='checkednumbers[]']:checked").each(function(i){
        checkednumbers = $(this).val();
   });
  
   if(nameid!=''&&dtp_input1!=""&&checkednumbers!=""){
    $(".tabContent").hide();
	$("ul#CreCon > li").removeClass("active");
	$("#secondTab").addClass("active");
	$("#AdvSettings").show();
    $('#error_participants').hide();
    $('#now_error').hide();
    $('#input-error').hide();
   }
   else{
       if(checkednumbers=='')
       {
           $('#error_participants').show();
           $('#error_participants').html("Please Select Participants");
       }
        if(dtp_input1=='')
       {
            $('#now_error').show();
           $('#now_error').html("Please Select time for Conference");
       }
        if(nameid=='')
       {
            $('#input-error').show();
           $('#input-error').html("Please enter Name for the conference");
       }
       
   }
   

});	

$('#nameid').blur(function(){
    var value = $(this).val(); 
    if(value!=''){
    $('#input-error').hide();
    }else{
         $('#input-error').show();
    }
    
});



$("input[name='checkednumbers[]']").change(function(){
    var value = $(this).val(); 
    var checkednumbers ='';
     $("input[name='checkednumbers[]']:checked").each(function(i){
        checkednumbers = $(this).val();
   });
   if(checkednumbers!=''){
        $('#error_participants').hide();
   }
   else{
        $('#error_participants').show();
   }
    
});

$(".Stab1").click(function(){
   
    
	$(".tabContent").hide();
	$("ul#CreCon > li").removeClass("active");
	$("#secondTab").addClass("active");
	$("#AdvSettings").show();
});
$(".Ftab").click(function(){
	$(".tabContent").hide();
	$("ul#CreCon > li").removeClass("active");
	$("#firstTab").addClass("active");
	$("#ConDetails").show();
});
$(".Ttab").click(function(){
	$(".tabContent").hide();
	$("ul#CreCon > li").removeClass("active");
	$("#thirdTab").addClass("active");
	$("#AddNoti").show();
});	
$(".Ltab").click(function(){
	$(".tabContent").hide();
	$("ul#CreCon > li").removeClass("active");
	$("#fourthTab").addClass("active");
var txt=$('input:text[name=nameconf]').val();
var txt2=$('#dtp_input1').val();
var txt3 =txt2 ;
txt3 = webility.toLocalTime(txt3);
console.log(newDate); 
var val = [];
const monthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
var date    = new Date(txt3);
var yr      = date.getFullYear();
var month   = date.getMonth() < 10 ? '0' + date.getMonth() : date.getMonth();
var day     = date.getDate()  < 10 ? '0' + date.getDate()  : date.getDate();
    var hr = date.getHours();
var min = date.getMinutes();
if (min < 10) {
    min = "0" + min;
}
var ampm = "AM";
if( hr > 12 ) {
    hr -= 12;
    ampm = "PM";
}


var newDate  = monthNames[date.getMonth()]+" "+day+", "+yr+" "+ hr+":"+min+" "+ampm;
 $("#nameofconference").html(txt);
 $("#dateofconference").html(newDate);
    var ulcontent ="<ul>"; 
    $("input[name='checkednumbers[]']:checked").each(function(i){
     var name = $(this).attr('data-id');
     var id   = $(this).val();  
      ulcontent += "<li>"+name+"<em class='fa fa-times-circle checkbox_delete' data-id='"+id+"'></em></li>";
    });
    ulcontent+="</ul>";
    $("#added_paticipants").html(ulcontent);
	$("#ScheduleCon").show();
	$('.checkbox_delete').click(function(){
	  var id = $(this).attr('data-id');
	  var check = "#E"+id;
	  $(check). prop("checked", false);
	  $(this).parent('li').remove();
	   var checkednumbers='';
    $("input[name='checkednumbers[]']:checked").each(function(i){
        checkednumbers = $(this).val();
   });
    
    if(checkednumbers==""){
        $('#added_error').removeClass('hide');
        $('#submit_button').attr("disabled", "disabled");
    }
	    
	});
});	

 $("input[name='checkednumbers[]']").change(function(){
     //alert($(this).val());
      var checkednumbers='';
    $("input[name='checkednumbers[]']:checked").each(function(i){
        checkednumbers = $(this).val();
   });
   if(checkednumbers!=''){
       $('#added_error').addClass('hide');
        $('#submit_button').prop("disabled", false);
   }
 });
$('#submit_button').click(function(){
    	
    var checkednumbers='';
    $("input[name='checkednumbers[]']:checked").each(function(i){
        checkednumbers = $(this).val();
   });
    
    if(checkednumbers==""){
        
    }
    
    
});
	
});
 