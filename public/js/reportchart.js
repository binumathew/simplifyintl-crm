$(function(){
	/*creating Users chart */
		var userChart = new Chartist.Bar('#userChart', {
		  labels: uData.label,
		  series: [ uData.data
		  ]
		}, {
		  seriesBarDistance: 10,
		  reverseData: true,
		  horizontalBars: true,
		  axisY: {
		    offset: 70
		  }
		});
		userChart.on('draw', function(context) {
	      if(context.type === 'bar') {
	         context.element.attr({
	          style: 'stroke:  #0290a6'
	        });
	      }
	    });
	/*End */
	/*creating payment chart */
  	var payChart = $.MorrisCharts.createLineChart('paymentChart', payData, 'y', ['succ','suwerr','fail','refund','cancel'], ['Success', 'Success with Error','Failed','Refund','cancel'], ['#5468da','#ffbb44','#bf0a0a','#8d6e63','#ea553d']);

  	/*creating plan type chart */
  	var planChart = $.MorrisCharts.createDonutChart('plantypeChart', plantypeData, ['#ea553d', '#707070', '#afb42b']);

  	/*creating order delivery status chart */
  	orderdelChart(orderstatData,orderstatSeries);
  	function orderdelChart(label,data){
		Highcharts.chart('orderdelvChart', {
		    chart: {
		        type: 'spline'
		    },
		    credits: {
    			enabled: false
  			},
		    legend: {
		        symbolWidth: 40
		    },
		    title: {
		        text: 'Order status'
		    },
		    yAxis: {
		        title: {
		            text: 'Values'
		        }
		    },
		    xAxis: {
		        categories: label
		    },
		    series: data
		});
	}
  	// $.MorrisCharts.createBarChart('orderdelvChart', orderstatData, 'y', ['or_rec','redto_act','call_pen','actived'], ['Order Received', 'Ready To Activate','CallBack Pending','Activated'], ['#8d6e63', '#4ac18e','#009688', '#fb8c00']);

  	/*Order based on promocode */
	var promoChart =  Highcharts.chart('orderpromoChart', {
	    chart: {
	        plotBackgroundColor: null,
	        plotBorderWidth: null,
	        plotShadow: false,
	        type: 'pie'
	    },
	    credits: {
	    	enabled: false
	  	},
	    title: {
	        text: 'Order Based on Promocode'
	    },
	    tooltip: {
	        pointFormat: '{series.name}: ({point.y}) <b>{point.percentage:.1f}%</b>'
	    },
	    accessibility: {
	        point: {
	            valueSuffix: '%'
	        }
	    },
	    plotOptions: {
	        pie: {
	            allowPointSelect: true,
	            cursor: 'pointer',
	            dataLabels: {
	                enabled: true,
	                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	            }
	        }
	    },
	    series: [{
	        name: 'Order',
	        colorByPoint: true,
	        data: orderpromo
	    }]
	});
  	/* End */

  	/*Sim Log plan renewed */
 //  	var simplandata = {
	//   series: simplanrenew.data,
	// };
	// var sum = function(a, b) { return a + b };

	// new Chartist.Pie('#simple-pie', simplandata, {
	//   labelInterpolationFnc: function(value,key) {
	//     var percentage =  Math.round(value / simplandata.series.reduce(sum) * 100) + '%';
	//     return simplanrenew.label[key] + ' ' + percentage;;
	//   }
	// });
	function createDonutGraphsimplan(selector, datas, colors) {
        var data = datas;
        var options = {
            series: {
                pie: {
                    show: true,
                    innerRadius: 0.7
                }
            },
            legend : {
				show : true,
				labelFormatter : function(label, series) {
					return '<div style="font-size:14px;">&nbsp;' + label + '</div>'
				},
				labelBoxBorderColor : null,
				margin : 50,
				width : 20,
				padding : 1
			},
			grid : {
				hoverable : true,
				clickable : true
			},
			colors : colors,
			tooltip : true,
			tooltipOpts : {
				content : "%s, %p.0%"
			}
        };

        var simplot = $.plot($(selector), data, options);
        return simplot;
    }
      var simplancolors = ['#f06292', '#4ac18e', "#bcb7d8","#6accc3","#ea553d","#ecf542"];
      var simplanPlot  = createDonutGraphsimplan("#simplan-chart #simplan-chart-container", simplanrenew, simplancolors);
  	/*End Log Plan */
  	/*Porting based */
  	var portingChart = new Chartist.Bar('#portingChart', {
	  labels: portingstatus.label,
	  series: [ portingstatus.data
	  ]
	}, {
	  seriesBarDistance: 5,
	  reverseData: true,
	  horizontalBars: true,
	  axisY: {
	    offset: 70
	  },
	  plugins: [
    	Chartist.plugins.tooltip()
  		]
	});
	portingChart.on('draw', function(context) {
      if(context.type === 'bar') {
         context.element.attr({
          style: 'stroke:  #f06292'
        });
      }
    });
  	/*End Porting based */
  	/*Auto plan status based */
  	autoPlanChart(planstatus,planstatusSeries);
  	function autoPlanChart(label,data){
		var autoPlanstatus = Highcharts.chart('autoplanstatChart', {
	    chart: {
	        type: 'column'
	    },
	    title: {
	        text: 'Payment'
	    },
	    xAxis: {
	        categories: label,
	        crosshair: true
	    },
	    yAxis: {
	        min: 0,
	        title: {
	            text: 'Values'
	        }
	    },
	    credits: {
	    	enabled: false
	  	},
	    tooltip: {
	        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
	        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
	            '<td style="padding:0"><b>{point.y}</b></td></tr>',
	        footerFormat: '</table>',
	        shared: true,
	        useHTML: true
	    },
	    plotOptions: {
	        column: {
	            pointPadding: 0.2,
	            borderWidth: 0
	        }
	    },
	    series: data
		});
	}
    //$.MorrisCharts.createBarChart('autoplanstatChart', planstatus, 'y', ['InActive', 'Active'], ['InActive', 'Active'], ['#8d6e63', '#4ac18e']);
  	/*End */
  	/* Search with time period */
  	$(document).on('click', '#statisticSearch', function(){
    alertify.dismissAll();
    var timeperiod = $("#time_period").val();
    if(timeperiod != ""){
      $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{timeperiod:timeperiod},
        url:base_url + '/report-dashboard',
        success:function(data){ 
            if(data.status == 200){
              var res      = data.data;
              /* User Graph */
              var userdata = (res.user_data != undefined) ? JSON.parse(res.user_data) : [];
	            updateChartlist('userChart',userdata.label,userdata.data);
	            $(".us_cnt").html(0);
	            $.each(userdata.dyn,function(k,val){
	              $("#"+k).html(val);
	            });
	            /* End */
	            /* Payment Graph */
	            var userpay = (res.user_pay != undefined) ? JSON.parse(res.user_pay) : [];
	            updateMorris(payChart,userpay);
	            $(".up_cnt").html(0);
	            $.each(userpay,function(k,val){
		            let total  = 0;
	                let su     = (val['succ'] != undefined) ? val['succ']: 0;
	                let fail   = (val['fail'] != undefined) ? val['fail']: 0;
	                let suwerr = (val['suwerr'] != undefined) ? val['suwerr']: 0;
	                let refund = (val['refund'] != undefined) ? val['refund']: 0;
                	total  = su + fail + suwerr + refund;
	              $("#"+val['y']).html(total);
	            });
	            /* End */
	            /* AutoPlan Type */
	            var plantype = (res.plan_type != undefined) ? JSON.parse(res.plan_type) : [];
	            updateMorris(planChart,plantype);
	            $(".pl_cnt").html(0);
	            $.each(plantype,function(k,val){
	              $("#"+val['label']).html(val['value']);
	            });
	            /* End */
	            /* Order promocode */
	            var ordpro = (res.order_promo != undefined) ? JSON.parse(res.order_promo) : [];
	            updateHighChart(promoChart,ordpro);
	            /* End */
	            /* Autoplan status */
	            var planseries = (res.plan_status_series != undefined) ? JSON.parse(res.plan_status_series) : [];
	            var planlabel = (res.plan_status != undefined) ? JSON.parse(res.plan_status) : [];
	            autoPlanChart(planlabel,planseries);
	            //updateHighChart(autoPlanstatus,planseries,planlabel);
	            /* End */
	            /* Porting Based */
	            var portingbased = (res.porting_status != undefined) ? JSON.parse(res.porting_status) : [];
	            var portingval = (res.porting_vals != undefined) ? JSON.parse(res.porting_vals) : [];
	            updateChartlist('portingChart',portingbased.label,portingbased.data);
	            $(".pb_cnt").html(0);
	            $.each(portingval,function(k,val){
	              $("#"+val['label'].replace(/\s/g,'')).html(val['data']);
	            });
	            /* End */
	            /* Sim plan renew */
	            var simplanren = (res.simplan_renew != undefined) ? JSON.parse(res.simplan_renew) : [];
	            updatePlot(simplanPlot,simplanren);
	            $(".sm_cnt").html(0);
	            $.each(simplanren,function(k,val){
	              $("#"+val['label'].replace(/[^A-Z0-9]/ig, "")).html(val['data']);
	            });
	            /* End */
	            /* Order delivery status chart */
	            var orderlabel = (res.order_status != undefined) ? JSON.parse(res.order_status) : [];
	            var orderdata  = (res.order_status_series != undefined) ? JSON.parse(res.order_status_series) : [];
	            orderdelChart(orderlabel,orderdata);
	            /* End */
            }else{
            }
        }
      });
    }else{ alertify.error("Please choose statistics period");}
  	});
  	function updateChartlist(chart, label, data) {
  		document.getElementById(chart).__chartist__.update({ labels: label,series: [ data] });
	}
	function updateMorris(chart,data){
		chart.setData(data);
	}
	function updateHighChart(chart,data,label){
		chart.series[0].setData(data);
		chart.redraw();
	}
	function updatePlot(chart,data){
		chart.setData(data);
		chart.draw();
	}

  	function removeData(chart) {
      chart.data.labels.pop();
      chart.data.datasets.forEach((dataset) => {
          dataset.data.pop();
      });
      chart.update();
  	}
  	/*End */
});