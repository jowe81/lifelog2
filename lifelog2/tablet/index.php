<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="styles.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="tablet_ll.js"></script>
		<title>LL PCT</title>
	</head>
	<body>
	
	<ul class="nav nav-pills">
	  <li class="active"><a data-toggle="pill" href="#home" id="home-link">Home/Temperature</a></li>
	  <li><a data-toggle="pill" href="#menu1" class="subpage">Lights</a></li>
	  <li><a data-toggle="pill" href="#menu2" class="subpage">Devices</a></li>
	  <li><a data-toggle="pill" href="#menu3" class="subpage">Timers</a></li>
	</ul>
	
	<div class="container">
			
		<div class="tab-content">
		  <div id="home" class="tab-pane fade in active">
			<div class="row">
				<div class="col-sm-3 temp text-center">
					<div class="temp-name">Outside</div>
					<div class="temp-val" id="temp1"></div>
					<div class="row">
						<div class="col-sm-12 mailboxlock-sw" id="mailbox-sw-frontpage">
							<span>mailbox ready</span>							
							<div class="mailboxNote"></div>
						</div>
					</div>
				</div>
				<div class="col-sm-3 temp text-center">
					<div class="temp-name">Living room</div>
					<div class="temp-val" id="temp2"></div>
					<div class="row">
						<div class="col-sm-3 temp-down">&darr;</div>
						<div class="col-sm-6 d-temp">N/A</div>
						<div class="col-sm-3 temp-up">&uarr;</div>
					</div>
				</div>
				<div class="col-sm-3 temp text-center">
					<div class="temp-name">Bathroom</div>
					<div class="temp-val" id="temp3"></div>
					<div class="row">
						<div class="col-sm-3 temp-down">&darr;</div>
						<div class="col-sm-6 d-temp">N/A</div>
						<div class="col-sm-3 temp-up">&uarr;</div>
					</div>
				</div>
				<div class="col-sm-3 temp text-center">
					<div class="temp-name">Bedroom</div>
					<div class="temp-val" id="temp4"></div>
					<div class="row">
						<div class="col-sm-3 temp-down" id="d-temp4-down">&darr;</div>
						<div class="col-sm-6 d-temp" id="d-temp4">N/A</div>
						<div class="col-sm-3 temp-up" id="d-temp4-up">&uarr;</div>
					</div>
				</div>
			</div>
			<div class="spacer">
			</div>
			<div class="row">
				<div class="col-sm-4">
					<div id="status">
					</div>
				</div>
				<div class="col-sm-4">
					<div id="emails">
					</div>
				</div>
				<div class="col-sm-4">
					<div id="doorbell">
					</div>
					<div id="alarms">
					</div>
				</div>
			</div>
			<div class="spacer">
			</div>
			<div class="row">
				<div class="col-sm-4">
					<div id="clock">
					</div>
				</div>
				<div class="col-sm-3">
					<div class="button frontbutton" id="toggle_away">away</div>
				</div>
				<div class="col-sm-3">
					<div class="button frontbutton" id="toggle_sleep">sleep</div>
				</div>
				<div class="col-sm-2">
					<div class="button frontbutton" id="toggle_busy">busy</div>
				</div>
			</div>
		  </div>
		  <div id="menu1" class="tab-pane fade">
		  	<div class="row">
		  		<div class="col-sm-3">
					<div class="button preset-sw" id="ps-8">full on</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button preset-sw" id="ps-7">blackout</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button preset-sw" id="ps-6">all aleds</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button preset-sw" id="ps-5">desk work</div>
		  		</div>
		  	</div>
		  	<div class="spacer"></div>
		  	<div class="spacer"></div>
		  	<div class="row">
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-18">living room</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-22">ktchn table</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-24">counter</div>
		  		</div>
		  	</div>
		  	<div class="spacer"></div>
		  	<div class="row">
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-6">bedrm left</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-11">bedrm right</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-7">bedrm rear</div>
		  		</div>
		  	</div>
		  	<div class="spacer"></div>
		  	<div class="row">
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-5">piano light</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw" id="ch-0">outside</div>
		  		</div>
		  		<div class="col-sm-4">
					<div class="button smallbutton channel-sw mailboxlock-sw" id="ch-999">mailbox<div class="mailboxNote" id="mailboxNote-page2"></div></div>
		  		</div>
		  	</div>
		  </div>
		  <div id="menu2" class="tab-pane fade">
		  	<h2>Misc</h2>
		  	<div class="row">
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-2">main display</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-9">bedroom <br>fan</div>
		  		</div>
		  		<div class="col-sm-3">
					
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-20">webcam<br>&nbsp;</div>
		  		</div>
		  	</div>
		  	<div class="spacer"></div>
		  	<h2>AV</h2>
		  	<div class="row">
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-4">X-AIR<br>mixer</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-3">B2031A<br>speakers</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-8">home theater</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton channel-sw" id="ch-12">living rm<br>audio</div>
		  		</div>
		  	</div>
		  </div>
		  <div id="menu3" class="tab-pane fade">
		  	<h2>Timers</h2>
		  	<div class="row">
		  		<div class="col-sm-3">
					<div class="button smallbutton set-timer" id="tm-3">3:00</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton set-timer" id="tm-10">10:00</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton set-timer" id="tm-30">30:00</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton set-timer" id="tm-60">60:00</div>
		  		</div>
		  	</div>
		  	<div class="row">
		  		<div class="col-sm-4"></div>
		  		<div class="col-sm-8" id="alarms2-container">
		  			<div id="alarms2"></div>
		  		</div>
		  	</div>
		  	<h2>Alarms</h2>
		  	<div class="row">
		  		<div class="col-sm-2">
					<div class="button smallbutton set-alarm" id="al-2">8:00<br>am</div>
		  		</div>
		  		<div class="col-sm-2">
					<div class="button smallbutton set-alarm" id="al-3">9:00<br>am</div>
		  		</div>
		  		<div class="col-sm-2">
					<div class="button smallbutton set-timer timer-ctrl" id="tm-0">Clear<br>all</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton nudge-timer timer-ctrl" id="nudge-down">Nudge<br>down</div>
		  		</div>
		  		<div class="col-sm-3">
					<div class="button smallbutton nudge-timer timer-ctrl" id="nudge-up">Nudge<br>up</div>
		  		</div>
		  	</div>
		  </div>
		</div>
		
	</div>
	<script>
		var tempobj={};
		var nodeurl="http://192.168.90.240:8002/"; //"http://192.168.1.200:8002/";
		var lldata={};
		var llurl="ajax.php";
		var back_to_home_delay=120000; //return to home screen after this delay
		var back_to_home_timer;
		
		function updateUI(data){
			if (data){
				lldata=data;
			}
			if (tempobj.temp1){
				//Got data
				for (var i=1;i<5;i++){
					t=tempobj.temp[i-1];
					
					if (t==Math.floor(t)){
						t=t+".0";
					}
					style="color:lime;";
					if (t<20){
						style="color:#7777FF;";
					} else if ((t>25) && (t<=28)){
						style="color:#FF5500;";
					} else if (t>28){
						style="color:#FF0000;";
					}
					$("#temp"+i).html('<span style="'+style+'">'+t+'<small></small></span>');
				}
				$("#d-temp4").html(tempobj.tempsettings.temp);
				if (tempobj.tempsettings.hold){
					$("#d-temp4").addClass("highlight-bg");
				} else {
					$("#d-temp4").removeClass("highlight-bg");
				}

				//Clock
				$("#clock").html(tempobj.time);

				//Emails
				var e="";
				if (lldata.unread_emails){
					if (lldata.unread_emails.count>0){
						for(var i=0;i<Math.min(3,lldata.unread_emails.count);i++){
							e=e+"<div><p class='eh-sender'>"+lldata.unread_emails.headers[i].sender+"<span class='eh-age'>"+lldata.unread_emails.headers[i].age+" ago</span></p>";
							e=e+"<p class='eh-subject'>"+lldata.unread_emails.headers[i].subject+"</p></div>";
						}
					} else {
						//e="No unread emails";
					}
				}
				$("#emails").html(e);

				
				//Doorbell
				e="";
				var db_limit=2;
				if (lldata.doorbell){
					if (lldata.doorbell.since_last_ring<300){
						e="Doorbell rang "+lldata.doorbell.since_last_ringH+" ago";
						if (lldata.doorbell.answered==-1){
							e=e+"<br><span class='db-tap-to-answer'>Tap to answer</span>";
						} else {
							e=e+"<br><span class='db-answered'>You answered</span>";
						}
						$("#doorbell").show();
						db_limit=1;							
					} else {
						$("#doorbell").hide();
					}
				}
				$("#doorbell").html(e);		

				//Alarms
				e="";
				var statusclass;
				if (lldata.scheduled_alarms){
					if (lldata.scheduled_alarms.count>0){
						for (var i=0;i<Math.min(2,lldata.scheduled_alarms.count,db_limit);i++){
							statusclass="";
							if ((lldata.scheduled_alarms.alarms[i].remaining<60) && (lldata.scheduled_alarms.alarms[i].remaining>0)){
								statusclass="al-almost-expired";
								e=e+"<div><p class='al-due'>"+lldata.scheduled_alarms.alarms[i].time+" <span class='al-remaining "+statusclass+"'>"+lldata.scheduled_alarms.alarms[i].remainingH+"</span></p></div>";
							} else if (lldata.scheduled_alarms.alarms[i].remaining<=0){
								statusclass="al-expired";
								e=e+"<div><p class='al-due'>"+lldata.scheduled_alarms.alarms[i].time+" <span class='al-remaining "+statusclass+"'>exp "+lldata.scheduled_alarms.alarms[i].remainingH+"</span></p></div>";
							} else {
								e=e+"<div><p class='al-due'>"+lldata.scheduled_alarms.alarms[i].time+" <span class='al-remaining "+statusclass+"'>"+lldata.scheduled_alarms.alarms[i].remainingH+"</span></p></div>";
							}
						}
					}
				}
				$("#alarms").html(e);
				$("#alarms2").html(e);


				//Statusbtns
				if (lldata.flags.sleep_wake){
					$("#toggle_sleep").addClass("busy-bg");					
				} else {
					$("#toggle_sleep").removeClass("busy-bg");
				}
				if (lldata.flags.out_in){
					$("#toggle_away").addClass("busy-bg");	
					//Deactivate temperature hold when away
					if (tempobj.tempsettings.hold){
						toggle_hold_temperature();
					}				
				} else {
					$("#toggle_away").removeClass("busy-bg");
				}
				if (lldata.flags.busy_available){
					$("#toggle_busy").addClass("busy-bg");					
				} else {
					$("#toggle_busy").removeClass("busy-bg");
				}

				if (lldata.channels){
					for (var i=0;i<lldata.channels.length;i++){
						//console.log(lldata.channels[i]);
						if (lldata.channels[i]){
							$("#ch-"+(i+1)).addClass("channel-on");
						} else {
							$("#ch-"+(i+1)).removeClass("channel-on");
						}
					}
				}

				//Mailbox lights status
				if (mailboxESPdata.lights_on=="true"){
					$("#ch-0").addClass("channel-on");
				} else {
					$("#ch-0").removeClass("channel-on");
				}
				//Mailbox doorlock status
				if (mailboxESPdata.door_locked=="true"){
					$(".mailboxlock-sw").removeClass("mailbox-unlocked").addClass("mailbox-locked");
					$("#mailbox-sw-frontpage span").text("mailbox");
					if (mailboxESPdata.item_deposited=="false"){
						//Door locked but nothing inside
						$(".mailboxNote").text("no item");
					} else{
						$(".mailboxNote").text("item present");
					}						
				} else {
					$(".mailboxlock-sw").removeClass("mailbox-locked").addClass("mailbox-unlocked");
					$(".mailboxNote").text("");
					$("#mailbox-sw-frontpage span").text("mailbox ready");
				}
					

				//Status, channel-meta
				e="";
				if (lldata.channel_meta){
					var extraclass;
					extraclass=getEnergyClass(lldata.channel_meta.lights_energy);
					e="<span class='status-info-line'>"+lldata.channel_meta.lights_on+" lights <span class='status-wattage "+extraclass+"'>"+lldata.channel_meta.lights_energy+"W</span></span>";
					extraclass=getEnergyClass(lldata.channel_meta.air_energy);
					e+="<span class='status-info-line'>"+lldata.channel_meta.air_on+" air/heating <span class='status-wattage "+extraclass+"'>"+lldata.channel_meta.air_energy+"W</span></span>";
					extraclass=getEnergyClass(lldata.channel_meta.other_energy);
					e+="<span class='status-info-line'>"+lldata.channel_meta.other_on+" other devices <span class='status-wattage "+extraclass+"'>"+lldata.channel_meta.other_energy+"W</span></span>";
				}
				$("#status").html(e);
				if (lldata.weather){
					$("#weather").html(lldata.weather.sky_condition);
				}
				
			}
		}
		
		function getTemp(noNewTimeout){
			$.get(llurl,function(data){
				lldata=data;
				//console.log(data);
				$.get(nodeurl,function(data){
					tempobj=data;
					updateUI();
				});
			});
			if (!noNewTimeout)
				setTimeout(getTemp,5000);
		}

		function setHomepageTimer(){
			clearTimeout(back_to_home_timer);
			back_to_home_timer=setTimeout(function(){
				console.log("timeout reached");
				$("#home-link").mouseenter().click();
			},back_to_home_delay);
		}
		
		$(document).ready(function(){

			$(".subpage").click(function(){
				setHomepageTimer();
			});

			$("#emails").click(function(){
				unflag_emails();
			});
			
			$("#alarms,#alarms2").click(function(){
				cancel_alarms();
			});

			$("#doorbell").click(function(){
				answer_door();
			});

			$("#toggle_away").click(function(){
				toggle_status("out_in");
			});

			$("#toggle_sleep").click(function(){
				toggle_status("sleep_wake");
			});
			
			$("#toggle_busy").click(function(){
				toggle_status("busy_available");
			});

			$("#mailbox-sw-frontpage").click(function(){
				toggleMailboxDoorlock();
			});

			$("#d-temp4").click(function(){
				toggle_hold_temperature();
			});

			$("#d-temp4-down").click(function(){
				dtemp_down();
			});

			$("#d-temp4-up").click(function(){
				dtemp_up();
			});

			$(".preset-sw").click(function(){
				var ps=$(this).attr("id").substr(3);
				pct_preset(ps);
			});

			$(".channel-sw").click(function(){
				var ch=$(this).attr("id").substr(3);
				if (ch>0){
					if (ch!=999){
						pct_channel(ch);
					} else {
						toggleMailboxDoorlock();
					}
				} else {					
					toggleMailboxLights();
				}
			});
			
			$(".set-timer").click(function(){
				var minutes=$(this).attr("id").substr(3);
				set_timer(minutes);
			});

			$(".set-alarm").click(function(){
				var a_id=$(this).attr("id").substr(3);
				set_alarm(a_id);
			});			

			$("#nudge-down").click(function(){
				nudge(true);
				t=$(this);
				t.addClass("clicked");
				setTimeout(function(){ t.removeClass("clicked"); },200);
			});

			$("#nudge-up").click(function(){
				nudge();
				t=$(this);
				t.addClass("clicked");
				setTimeout(function(){ t.removeClass("clicked"); },200);
			});

			//////
			mailboxESPdata={};
			function getMailboxESPdata(noNewTimeout){
				$.get("http://192.168.1.25/read",function(d,err){
					if (err=="success"){
						mailboxESPdata=d;
						updateUI();
					} else {
						console.log("Unable to read from ESP32 at 192.168.1.25: "+err);
					}
				});
				if (!noNewTimeout)
					setTimeout(getMailboxESPdata,10000);					
			}

			function executeMailboxCommand(cmd,attempt){
				if (!attempt) {attempt=0;}
				if (attempt<5){
    				$.get("http://192.168.1.25/"+cmd,function(d,err){
    					if (err=="success"){
    						//got data
    						mailboxESPdata=d;
    						updateUI();
        					console.log("Attempt ",attempt," successful (",cmd,")"); 
    					} else {
        					console.log("Attempt ",attempt," failed (",cmd,"), retrying");
        					executeMailboxCommand(cmd,attempt+1); 
    					}
    				});
				} else {
					console.log("Giving up (",cmd,")");					
				}					
			}				

			
			function toggleMailboxLights(){
				newState="on";
				if (mailboxESPdata.lights_on=="true"){
					newState="off";
				}
				executeMailboxCommand("write?lights="+newState);
			}

			function toggleMailboxDoorlock(){
				newState="lock";
				if (mailboxESPdata.door_locked=="true"){
					newState="unlock";
				}
				executeMailboxCommand("write?door="+newState);
			}
			
		

			getMailboxESPdata();
			getTemp();
		});
	</script>
	</body>
</html>

