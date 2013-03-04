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
		<ul>
			<li><strong>/me</strong> [<em>name</em>] [<em>age</em>] [<em>sex</em>] [<em>location</em>]</li>
			<li><strong>/join</strong> [<em>channel</em>]</li>
			<li><strong>/leave</strong> [<em>channel</em>]</li>
			<li><strong>/chat</strong> [<em>channel</em>] [<em>message</em>]</li>
			<li><strong>/tell</strong> [<em>name</em>] [<em>message</em>]</li>
			<li><strong>/whois</strong> [<em>name</em>]</li>
			<li>[<em>message</em>]</li>
		</ul>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script>
			var username, oldname;
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
				$(window).on("beforeunload", function() {
					cancelLongPoll(true);
					deleteMe();
					return null;
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
					var message = ExcessiveRebuildString(1, dchat);
					sendChat(channel, message);
				} else if (command.startsWith("/tell")) {
					var arg = command.substr("/tell".length + 1);
					var dtell = arg.split(" ");
					var recipient = dtell[0];
					var message = ExcessiveRebuildString(1, dchat);
					sendTell(recipient, message);
				} else if (command.startsWith("/whois")) {
					var arg = command.substr("/whois".length + 1);
					whoisUser(arg);
				} else if (!command.startsWith("/")) {
					sendChat(null, command);
				} else {
					updateChatBox("Unknown command: "+command);
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
							updateChatBox("Error, "+res.err, "SERVER");
						} else {
							username = name;
							updateChatBox("Welcome, "+name, "SERVER");
							if (!polling) {
								polling = true;
								receiveMessages();
							}
						}
					}
				});
			}

			function deleteMe() {
				console.log("deleteMe");
				if (!username) {
					console.log("user is not identified - nothing to delete");
					return;
				}
				$.ajax({
					url: "delete.php",
					type: "POST",
					async: false,
					data: {
						"name": username
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("delete.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("delete.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("Error, "+res.err, "SERVER");
						} else {
							username = null;
							oldname = null;
							polling = false;
							updateChatBox("Bye bye!");
						}
					}
				});
			}

			function whoisUser(name) {
				console.log("whoisUser "+name);
				if (!name) {
					updateChatBox("You must enter a name to use whois");
					return;
				}
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
							updateChatBox("Error, "+res.err, "SERVER");
						} else {
							updateChatBox("Name: "+res.name+"\nAge: "+res.age+"\nSex: "+res.sex+"\nLocation: "+res.location);
						}
					}
				});
			}

			function sendChat(channel, message) {
				console.log("sendChat channel:"+channel+", message:"+message);
				if (!username) {
					updateChatBox("You must first use the /me command to identify yourself");
					return;
				}
				if (!message) {
					return;
				}
				var data = {
					"name": username,
					"message": message
				};
				if (channel) {
					data.channel = channel;
				}
				$.ajax({
					url: "chat.php",
					type: "POST",
					data: data,
					error: function(xhr, textStatus, errorThrown) {
						console.log("chat.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("chat.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("Error, "+res.err, "SERVER");
						}
					}
				});
			}

			function sendTell(recipient, message) {
				console.log("sendTell recipient:"+recipient+", message:"+message);
				if (!username) {
					updateChatBox("You must first use the /me command to identify yourself");
					return;
				}
				if (!recipient) {
					updateChatBox("You must specify a recipient to whisper to");
					return;
				}
				if (!message) {
					updateChatBox("You must enter a message to send to the recipient");
					return;
				}
				if (recipient == username) {
					updateChatBox("Don't talk to yourself");
					return;
				}
				$.ajax({
					url: "tell.php",
					type: "POST",
					data: {
						"name": username,
						"recipient": recipient,
						"message": message
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("tell.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("tell.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("Error, "+res.err, "SERVER");
						} else {
							updateChatBox(message, username, recipient);
						}
					}
				});
			}

			function joinChannel(channel) {
				console.log("joinChannel "+channel);
				if (!username) {
					updateChatBox("You must first use the /me command to identify yourself");
					return;
				}
				if (!channel) {
					updateChatBox("You need to specify a channel name to join");
					return;
				}
				$.ajax({
					url: "join.php",
					type: "POST",
					data: {
						"name": username,
						"channel": channel
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("join.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("join.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("Error, "+res.err, "SERVER");
						} else {
							updateChatBox(username+" has joined channel: "+channel, "SERVER");
						}
					}
				});
			}

			function leaveChannel(channel) {
				console.log("leaveChannel "+channel);
				if (!username) {
					updateChatBox("You must first use the /me command to identify yourself");
					return;
				}
				if (!channel) {
					updateChatBox("You need to specify a channel name to leave");
					return;
				}
				$.ajax({
					url: "leave.php",
					type: "POST",
					data: {
						"name": username,
						"channel": channel
					},
					error: function(xhr, textStatus, errorThrown) {
						console.log("leave.php ERROR");
      					console.log(xhr.toString());
      					console.log(textStatus);
      					console.log(errorThrown);
					},
					success: function(res) {
						console.log("leave.php success "+JSON.stringify(res));
						if (res.err) {
							updateChatBox("SERVER", null, "Error, "+res.err);
						}
					}
				});
			}

			function receiveMessages() {
				console.log("receiveMessages");
				if (!polling) {
					return;
				}
				$.ajax({
					url: "receive.php",
					type: "POST",
					data: {
						"name": username
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
							updateChatBox(res.message, res.name, res.channel);
						}
						setTimeout(receiveMessages, 1);
					}
				});
			}

			// VIEW UPDATES

			function updateChatBox(message, user, channel) {
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
				box.scrollTop(box[0].scrollHeight);
			}

			/**
			 * Rebuilds a string after it has been split on spaces (and adds
			 * spaces back in). Why is this a function? Because I can.


						 `""==,,__  
			        `"==..__"=..__ _    _..-==""_
			             .-,`"=/ /\ \""/_)==""``
			            ( (    | | | \/ |
			             \ '.  |  \;  \ /
			              |  \ |   |   ||
			         ,-._.'  |_|   |   ||
			        .\_/\     -'   ;   Y
			       |  `  |        /    |-.
			       '. __/_    _.-'     /'
			   jgs        `'-.._____.-'

			
			 *
			 * @param int startIndex The index of the array to begin rebuilding 
			 *						 the string with.
			 * @param string[] theStringArray The source array to rebuild from.
			 *
			 * @return string A beautiful string.
			 */
			function ExcessiveRebuildString(startIndex, theStringArray)
			{
				var str = "";

				for (var i = startIndex; i < theStringArray.length - 1; i++)
					str += theStringArray[i] + " ";

				str += theStringArray[theStringArray.length - 1];

				return str;
			}
		</script>
	</body>
</html>
