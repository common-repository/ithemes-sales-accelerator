/**
* Handles dashboard charts
**/

jQuery( document ).ready( function() {
	
	/*
	 * Initialize variables
	*/
	var newData = [], names = [], bgcolor = [];
	var ts, line_gradient_color, ts_chart;
	
	/*
	 * Function to create chart
	*/
	createChart = function ( name, data, borderSize ) {

		data.forEach( function( element ) {
			newData.push( element.value );
		} );
				
		ts = jQuery( '.box #' + name ).get( 0 ).getContext( '2d' );
		line_gradient_color = ts.createLinearGradient( 500, 0, 100, 0 );
		line_gradient_color.addColorStop( 0, '#43BAEE' );
		line_gradient_color.addColorStop( 0.3, '#43BAEE' );
		line_gradient_color.addColorStop( 0.5, '#43BAEE' );
		line_gradient_color.addColorStop( 1, '#3689E0' );
		ts = jQuery( '.box #' + name).get( 0 ).getContext( '2d' );
		ts_chart = new Chart( ts, {
		    // The type of chart we want to create
		    type: 'line',
		
		    // The data for our dataset
		    data: {
		        labels: newData,
		        datasets: [{
		            data: newData,
		            fill: false,
		            borderWidth: borderSize,
		            pointRadius: 0,
		            borderColor:               line_gradient_color,
					pointBorderColor:          line_gradient_color,
					pointBackgroundColor:      line_gradient_color,
					pointHoverBackgroundColor: line_gradient_color,
					pointHoverBorderColor:     line_gradient_color,
		            borderCapStyle: 'round',
		            borderJoinStyle: 'round'
		        }],
		       
		    },
		
		    // Configuration options go here
		    options: {
				scales: {
	                    xAxes: [{
	                        display: false
	                    }],
	                    yAxes: [{
	                        display: false
	                    }]
	            },
	            legend:{display:false},
	            tooltips: {enabled: false},
	            hover: {mode: false},
	            layout: {
		        	padding: {
		                left: 10,
		                right: 10,
		                top: 10,
		                bottom: 10
		            }
		        },
		        responsive: true,
				maintainAspectRatio: false,
				
		    }
		} )
		
		newData = [];
	}
	
	/*
	 * Function to create chart with two axis
	*/
	Chart.defaults.global.defaultFontFamily = "'Nunito', sans-serif";
	createChartAxis = function ( name, data, borderSize ) {
		
		var elementsN = 0;
		var bgcolors = Array( '#54C2EE', '#4ECE98', '#F15C46', '#132E47', '#FFBA00', '#3689E0' );
		var currency_position = it_rst_js_data.currency_position;
		var currency_symbol   = it_rst_js_data.currency_symbol;
		
		if ( name == 'stocks_by_warehouse' ) {
			currency_symbol = '';
		}
		
		data.forEach( function( element ) {
			element.forEach( function( item ) {
				elementsN = elementsN + 1;
				names.push(item.name);
				var el = bgcolors[Math.floor( Math.random() * bgcolors.length )];
				var index = bgcolors.indexOf( el );
				bgcolor.push( el )
				bgcolors.splice( index, 1 );
			} );
		} );
		
		data.forEach( function( element ) {
			element.forEach( function( item ) {
				newData.push( item.total );
			} );
		} );
		
		if ( elementsN > 6 ){
			bgcolor = '#4ECE98';
		}
		
        var horizontalBarChartData = {
            labels: names,
            datasets: [{
                label: 'Value',
                backgroundColor: bgcolor,
                borderColor: bgcolor,
                borderWidth: 1,
                data: newData
            }]
        };
        
        var ctx = jQuery( '.chart #' + name ).get( 0 ).getContext( '2d' );
        var config = {
            type: 'bar',
            data: horizontalBarChartData,
            options: {
                // Elements options apply to all of the options unless overridden in a dataset
                // In this case, we are setting the border of each horizontal bar to be 2px wide
                elements: {
                    rectangle: {
                        borderWidth: 2,
                    }
                },
                title: { display: false },
                responsive: true,
				maintainAspectRatio: false,
				hover: {mode: false},
				legend: {display:false},
				tooltips: {
		            callbacks: {
		                label:function(tooltipItem, data){
			                switch ( currency_position ) {
					            case 'left' :
							      return currency_symbol + tooltipItem.yLabel;
							    break;
							    case 'right' :
							      return tooltipItem.yLabel + currency_symbol;
							    break;
							    case 'left_space' :
							      return currency_symbol + ' ' + tooltipItem.yLabel;
							    break;
							    case 'right_space' :
							      return tooltipItem.yLabel + ' ' + currency_symbol;
							    break;
							    default:
							   	  return currency_symbol + tooltipItem.yLabel;
							    break;
							}
		                }
		            }
				}
            }
        };
        
        var ctx = jQuery( '.chart #' + name ).get( 0 ).getContext( '2d' );
        window.myHorizontalBar = new Chart(ctx, config);
        
        names = [];
        newData = [];
		bgcolor = [];
	}
	
	/*
	 * Function to create Donut chart
	*/ 
	createChartDonut = function ( name, data ) {

	    var config = {
	        type: 'doughnut',
	        data: {
	            datasets: [{
	                data: [data.value1,data.value2],
	                backgroundColor: ['#4ECE98','#54C2EE'],
	                label: ''
	            }],
	            labels: [data.label1,data.label2]
	        },
	        options: {
	            responsive: true,
	            maintainAspectRatio: false,
	            hover: {mode: false},
	            legend: {display:false},
	            title: {display: false},
	            animation: {
	                animateScale: true,
	                animateRotate: true
	            }
	        }
	    };
	    
	    var ctx = jQuery( '.chart #' + name ).get(0).getContext( '2d' );
        window.myDoughnut = new Chart( ctx, config );
	}
	
});