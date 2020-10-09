var started;
var ended;
function getDate(y,dated){
	started=dated.start;
	ended=dated.end;
	//alert(start);
}
(function ($) {
	function sortByValue(jsObj){
    var sortedArray = [];
    for(var i in jsObj)
    {
        // Push each JSON Object entry in array by [value, key]
        sortedArray.push([jsObj[i], i]);
    }
    return sortedArray.sort();
}

 function findusers() {

        var selectedcourse = $("#id_course").val();  
        var selecteduser = $("#id_user").val();  

		$.getJSON("./load_user.php?value="+selectedcourse, function( data ) {
			
			var items = [];
			var sortedbyValueJSONArray= sortByValue(data);
			$.each( sortedbyValueJSONArray , function( key,val ) {
					var keey = val[1];
					var value = val[0];
					if(selecteduser == keey)
							items.push( "<option value='" + keey + "' selected>" + value + "</option>" );
					else
							items.push( "<option value='" + keey + "'>" + value + "</option>" );

			});
			$("select[name='user']").html( items.join( "" ) );
		});   
             
    }
    
    function cb(start, end) {
		     var inputF = document.getElementById("startdate");
		     var inputend = document.getElementById("enddate"); 
		     if(inputF=='[object HTMLInputElement]'){
				inputF.value = start; 
                inputend.value = end; 

			 }
		 
       $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    
    $( document ).ready(function() {
		
		findusers();
        $("#id_course").change(function(){
             findusers();
        }); 
        
		var start;
		var end;
		if(started!='-'){
		    start = moment(started).format('YYYY-MM-DD');
		    end = moment(ended).format('YYYY-MM-DD');
		}else{
			start = moment().subtract(29, 'days');
			end = moment();
		}
		
			console.log(start);
			console.log(end);
		$('#reportrange').daterangepicker({
			startDate: start,
			endDate: end,
			locale: {
				format: 'YYYY-MM-DD'
				},
			ranges: {
			   'Today': [moment(), moment()],
			   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			   'This Month': [moment().startOf('month'), moment().endOf('month')],
			   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			}
		}, cb);

		cb(start, end);    
        
    });
}(jQuery));
