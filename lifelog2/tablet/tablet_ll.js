/**
 * 
 */

	function getEnergyClass(wattage){
		if ((wattage>=100) && (wattage<300)){
			return "status-orange";
		}
		if (wattage>=300){
			return "status-red";
		}
		return "";
	}

	function toggle_status(flagname){
		$.get("ajax.php?a=toggle_status&p="+flagname,function(data){
			updateUI(data);
			//getTemp(true);
		});
	}

	function pct_preset(preset_id){
		$.get("ajax.php?a=recall_preset&p="+preset_id,function(result){
		});
	}
	
	function pct_channel(channel_id){
		$.get("ajax.php?a=toggle_channel&p="+channel_id,function(data){
			updateUI(data);
		});
	}

	function answerdoor(line1,line2){
		$.get("http://192.168.1.200:8100/open",function(result){
		});
	}
	
	function set_alarm_preset(presetnumber){
	}
	
	function set_timer(minutes){
		$.get("ajax.php?a=set_timer&p="+minutes,function(result){
		});		
	}
	
	function set_alarm(a_id){
		$.get("ajax.php?a=set_alarm&p="+a_id,function(result){
		});		
	}

	function nudge(down){
		if (down){
			down='down';	
		} else {
			down='';
		}
		$.get("ajax.php?a=nudge&p="+down,function(result){
		});
	}
	
	function unflag_emails(){
		$.get("ajax.php?a=unflag_emails",function(){
			getTemp(true);
		});
	}
	
	function cancel_alarms(){
		$.get("ajax.php?a=cancel_alarms",function(){
			getTemp(true);
		});
	}
	
	function answer_door(){
		$.get("ajax.php?a=answer_door",function(){
			getTemp(true);
		});
	}

	function toggle_hold_temperature(){
		$.get(nodeurl+"toggle-hold",function(data){
			tempobj=data;
			updateUI();
		});
	}
	
	function dtemp_down(){
		$.get(nodeurl+"dtemp-down",function(data){
			tempobj=data;
			updateUI();
		});		
	}
	
	function dtemp_up(){
		$.get(nodeurl+"dtemp-up",function(data){
			tempobj=data;
			updateUI();
		});		
	}