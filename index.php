<!DOCTYPE html>
<html>
	<head>
		<title>Redis Chat Server</title>
	</head>
	<body>
		<h2>Welcome to RChat!</h2>
		<textarea id="chatbox" rows="10" cols="100" readonly></textarea>
		<br>
		<input id="chatbar" type="text" size="130">
		<button id="sendbtn">Send</button>
		<h3>Commands</h3>
		<p>/me [name] [age] [sex] [location]</p>
		<p>/join [channel]</p>
		<p>/leave [channel]</p>
		<p>/chat [channel] [message]</p>
		<p>/tell [name] [message]<p>
		<p>/whois [name]</p>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script>

			var username, oldname;
			var channels = [];
			var polling = false;

			if (!String.prototype.startsWith) {
				Object.defineProperty(String.prototype, 'startsWith', {
					enumerable: false,
					configurable: false,
					writable: false,
					value: function (searchString, position) {
						position = position || 0;
						return this.indexOf(searchString, position) === position;
					}
				});
			}

			$(function() {
				$("#sendbtn").click(function() {
					console.log("sendbtn clicked");
					parseCommand($("#chatbar").val());
					$("#chatbar").val("");
				});
				$(document).keypress(function(e) {
    				if(e.which == 13) {
    					console.log("enter pressed");
						parseCommand($("#chatbar").val());
						$("#chatbar").val("");
				    }
				});
				$(window).unload(function() {
					cancelLongPoll(true);
					//deleteMe();
				});
			});

			// COMMAND PARSING

			function parseCommand(command) {
				console.log("parseCommand "+command);
				if (command.startsWith("/me")) {
					var arg = command.substr("/me".length + 1);
					var duser = arg.split(" ");
					var name = duser[0];
					var age = duser[1];
					var sex = duser[2];
					var location = duser[3];
					createMe(name, age, sex, location);
				} else if (command.startsWith("/join")) {
					var arg = command.substr("/join".length + 1);
					joinChannel(arg);
				} else if (command.startsWith("/leave")) {
					var arg = command.substr("/leave".length + 1);
					leaveChannel(arg);
				} else if (command.startsWith("/chat")) {
					var arg = command.substr("/chat".length + 1);
					var dchat = arg.split(" ");
					var channel = dchat[0];
					var message = dchat[1];
					sendChat(channel, message);
				} else if (command.startsWith("/tell")) {
					var arg = command.substr("/tell".length + 1);
				} else if (command.startsWith("/whois")) {
					var arg = command.substr("/whois".length + 1);
					whoisUser(arg);
				} else {
					updateChatBox(null, null, "Unknown command: "+command);
				}
			}

			// COMMAND HANDLING

			function createMe(name, age, sex, location) {
				console.log("createMe name:"+name+", age:"+age+", sex:"+sex+", location:"+location);
				oldname = username;
				$.ajax({
					url: "me.php",
					type: "POST",
					data: {
						"name": name,
						"oldname": oldname,
						"age": age,
						"sex": sex,
						"location": location
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("me.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("me.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("SERVER", null, "Error, "+res.err);
						} else {
							username = name;
							updateChatBox("SERVER", null, "Welcome, "+name);
							if (!polling) {
								polling = true;
								receiveMessages();
							}
						}
					}
				});
			}

			function whoisUser(name) {
				console.log("whoisUser "+name);
				$.ajax({
					url: "whois.php",
					type: "POST",
					data: {
						"name": name
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("whois.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("whois.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("SERVER", null, "Error, "+res.err);
						} else {
							updateChatBox(null, null, "Name: "+res.name+"\nAge: "+res.age+"\nSex: "+res.sex+"\nLocation: "+res.location);
						}
					}
				});
			}

			function sendChat(channel, message) {
				console.log("sendChat channel:"+channel+", message:"+message);
				$.ajax({
					url: "chat.php",
					type: "POST",
					data: {
						"name": username,
						"channel": channel,
						"message": message
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("chat.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("chat.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("SERVER", null, "Error, "+res.err);
						}
					}
				});
			}

			function sendTell(user, message) {
				console.log("sendTell user:"+user+", message:"+message);	
			}

			function joinChannel(channel) {
				console.log("joinChannel "+channel);
				if (channels.indexOf(channel) < 0) {
					channels.push(channel);
					updateChatBox(null, null, "Joined channel "+channel);
					cancelLongPoll();
				}
			}

			function leaveChannel(channel) {
				console.log("leaveChannel "+channel);
				var index = channels.indexOf(channel);
				if (index >= 0) {
					channels.splice(index, 1);
					updateChatBox(null, null, "Left channel "+channel);
					cancelLongPoll();
				}
			}

			function receiveMessages() {
				console.log("receiveMessages");
				if (!polling) {
					return;
				}
				console.log("beginning poll...");
				$.ajax({
					url: "receive.php",
					type: "POST",
					data: {
						"name": username,
						"channels": channels.join(",")
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("receive.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
      					setTimeout(receiveMessages, 1);
					},
					success: function(res) {
						console.log("receive.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("SERVER", null, "Error, "+res.err);
						} else if (res.status != "CANCEL") {
							updateChatBox(res.name, res.channel, res.message);
						}
						setTimeout(receiveMessages, 1);
					}
				});
			}

			function cancelLongPoll(noRestart) {
				if (noRestart) {
					polling = false;
				}
				$.ajax({
					url: "cancel.php",
					type: "POST",
					data: {
						"name": username
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("cancel.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("cancel.php success "+JSON.stringify(res));
					}
				});
			}

			// VIEW UPDATES

			function updateChatBox(user, channel, message) {
				console.log("updateChatBox user:"+user+", channel:"+channel+", message:"+message);
				var text = "";
				if (user) {
					text += "["+user+"]";
				}
				if (channel) {
					text += "("+channel+")";
				}
				if (text.length > 0) {
					text += " ";
				}
				text += message;
				var box = $("#chatbox");
				box.val(box.val() + text + "\n");
			}
		</script>
	</body>
</html>
