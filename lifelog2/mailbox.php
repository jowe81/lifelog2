<!doctype html>
<html>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<head>	
		<title>Johannes' ESP32 Mailbox</title>
		<script src="jquery-3.1.1.min.js"></script>
	</head>
	<body>         
		<div id="head">
			JoWe's ESP32 Mailbox
		</div>
	
        <div>You are connected from: <?php echo $_SERVER["REMOTE_ADDR"]; ?></div>
        	
		<style>
		
		  body {
		      box-sizing:border-box;
		      text-align:center;
		      font-family:Arial,sans-serif;
		      background-color:#777;
		      color:white;
		      margin:0px;
		      padding:0px;
		  }
		  
		  div {
		      box-sizing:border-box;
		  }
		  
		  #head {
		      width:100%;
		      height:45px;
		      background-color:#444;
		      color:white;
		      margin:0px;
		      font-size:180%;
		      padding:5px;
		      margin-bottom:10px;
		  }
		  
		  .button {
		      width:80%;
		      min-width:200px;
		      min-height:80px;
		      background-color:#EEE;
		      border:2px solid rgba(255,255,255,.5);
		      border-radius:5px;
		      padding:10px;
		      text-align:center;
		      cursor:pointer;
		      position:relative;
		      margin:40px auto;
		      color:black;		      
		  }
		  
		  .current {
		      font-size:150%;
		  }
		  
		  .switchto {
		      font-size:80%;
		  }
		  
		  .bgGreen {
		      background-color:#AAFFAA;
		  }
		  
		  .bgOrange {
		      background-color:#FFDDAA;
		  }
		  
		  .bgRed {
		      background-color:#FF0000;
		      color:white;		
		  }
		  
		  .note{
		      position:absolute;
		      bottom:30px;
		      left:0px;
		      width:100%;
		      text-align:center;
		      background-color:rgba(255,100,100,0.8);
		  }
		  .switchto {
		      position:absolute;
		      bottom:10px;
		      left:0px;
		      width:100%;
		      text-align:center;
		      background-color:rgba(200,200,200,0.2);
		  }
		  
		  .errormessage{
		      color:red;
		      font-weight:bold;
		      padding:5px;
		      margin:20px 0px;
		      font-size:130%;
		      background-color:#EEE;
		  }
		  
		  
		  .data-container {
		      width:80%;
		      border-radius:5px;
		      background-color:#333;
		      padding:10px 0px;
		      margin:0px auto;
		  }
		  
		  .data-line {
		      width:100%;
		      height:20px;
		      margin:0px;
		  }
		  
		  .data-item {
		      float:right;		  
		      text-align:left;
		      width:50%;
		      padding-left:5px;		      
		  }
		  .data-label {
		      color:#AAA;
		      float:left;
		      text-align:right;
		      width:50%;
		      padding-right:5px;
		  }
		</style>

        <?php
        if ( substr($_SERVER["REMOTE_ADDR"],0,7)!="192.168" ){
            ?>
            <div class="errormessage">Error: for security reasons, only intranet connections are allowed to the mailbox system</div>
            </body></html>
            <?php 
            die;    
        }
        ?>
		
		<div id="b_doorlock" class="button" style="min-height:100px;">		
			<div class="current"></div>
			<div class="note"></div>
			<div class="switchto">(loading...)</div>
		</div>
		<div id="b_lights" class="button">
			<div class="current"></div>
			<div class="switchto">(loading...)</div>
		</div>
		<div class="data-container" id="mailboxdata">
			<div class="data-line">
				<div class="data-label">Door status:</div><div class="data-item" id="data-door_state"></div>
			</div>
			<div class="data-line">
				<div class="data-label">Mailbox contents:</div><div class="data-item" id="data-contents"></div>
			</div>
			<div class="data-line">
				<div class="data-label">Auto-lock:</div><div class="data-item" id="data-auto_lock"></div>
			</div>
			<div class="data-line">
				<div class="data-label">Uptime (m,days):</div><div class="data-item" id="data-uptime"></div>
			</div>
		</div>
		<script>
			esp_ip="192.168.1.25";
			esp_data={};
		
			$(document).ready(function(){

				
				function getESPdata(){
					$.get("http://"+esp_ip+"/read",function(d,err){
						if (err=="success"){
							updateUI(d);
						} else {
							console.log("Unable to read from ESP32 at 192.168.1.25: "+err);
						}
					});
				}



				
				function updateUI(d){
					esp_data=d;
					bLights=$("#b_lights");
					if (esp_data.lights_on=="true"){
						bLights.removeClass("bgOrange").addClass("bgGreen");
						bLights.find(".current").text("Lights are on");
						bLights.find(".switchto").text("Tap to turn lights off");
					} else {
						bLights.removeClass("bgGreen").addClass("bgOrange");
						bLights.find(".current").text("Lights are off");
						bLights.find(".switchto").text("Tap to turn lights on");
					}
					bDoorlock=$("#b_doorlock");
					if (esp_data.door_open=="true"){
						//Door is open
						bDoorlock.removeClass("bgGreen bgOrange").addClass("bgRed");
						bDoorlock.find(".current").text("Please close door!");
						bDoorlock.find(".switchto").text("Door is open");
												
						$("#data-door_state").text("door is open");	
					} else {
						//Door is closed
						bDoorlock=$("#b_doorlock");
						if (esp_data.door_locked=="true"){
							//Door is closed and locked
							bDoorlock.removeClass("bgGreen bgRed").addClass("bgOrange");
							bDoorlock.find(".current").text("Door is locked");
							if (esp_data.item_deposited=="true"){
								bDoorlock.find(".note").text("Item(s) present!");
							} else {
								bDoorlock.find(".note").text("No items detected");
							}
							bDoorlock.find(".switchto").text("Tap to unlock mailbox door");
						} else {
							//Door is closed and not locked
							bDoorlock.removeClass("bgOrange bgRed").addClass("bgGreen");
							if (esp_data.item_deposited=="true"){
								bDoorlock.find(".current").text("Retrieve your item(s)!");
								bDoorlock.find(".note").text("Door is unlocked");
							} else {
								bDoorlock.find(".current").html("Mailbox is<br>waiting for deposit");								
								bDoorlock.find(".note").text("");
							}													
							bDoorlock.find(".switchto").text("Tap to lock mailbox door");
						}
						$("#data-door_state").text("door is closed");	
					}
					if (esp_data.item_deposited=="true"){
						$("#data-contents").text("item(s) present");	
					} else {
						$("#data-contents").text("none detected");	
					}
					if (esp_data.defer_auto_lock=="true"){
						$("#data-auto_lock").text("deferred");	
					} else {
						$("#data-auto_lock").text("ready");	
					}
					days=Math.floor(esp_data.uptime/1000/(24*60*60));
					minutes=Math.floor(esp_data.uptime/1000/60);
					$("#data-uptime").text(minutes+", "+days+"");
					
				}

				function executeCommand(cmd){
    				$.get("http://"+esp_ip+"/"+cmd,function(d,err){
    					if (err=="success"){
    						updateUI(d);
    					}
    				});	
				}				

				
				function toggleLights(){
					newState="on";
					if (esp_data.lights_on=="true"){
						newState="off";
					}
					executeCommand("write?lights="+newState);
				}

				function toggleDoorlock(){
					newState="lock";
					if (esp_data.door_locked=="true"){
						newState="unlock";
					}
					executeCommand("write?door="+newState);
				}

				$("#b_lights").click(function(){
					toggleLights();
				});

				$("#b_doorlock").click(function(){
					toggleDoorlock();
				});

				getESPdata();
				setInterval(getESPdata,2000);
				
			});
		</script>
	</body>
</html>