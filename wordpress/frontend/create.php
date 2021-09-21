<?php

function custom_page_title($title = "")
{
    return $title != "" ? $title : "Ticket erstellen";
}

function custom_page_subtitle()
{
    return "";
}

function custom_page()
{
    if (isset($_POST["Device"]) && ($device = ITDesk::getInstance()->getDevice($_POST["Device"])) != null && isset($_POST["Issue"]) && ($issue = ITDesk::getInstance()->getIssue($_POST["Issue"])) != null && isset($_POST["Shortname"]) && isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"])) {
        $ticket = new Ticket(null, 0, 3, $device, $issue, is_user_logged_in() ? get_current_user_id() : - 1, - 1, $_POST["Shortname"], isset($_POST["AdminOnly"]), []);
        $ticket->save();
        $ticket->callEvent(CreateEvent::class);
        if (isset($_POST["Message"]))
            $ticket->callEvent(MessageEvent::class, htmlspecialchars($_POST["Message"])); ?>

        <div class="main main-raised">
            <div class="container section section-text text-center">
                Das von Ihnen gemeldete Problem zu Gerät <?php echo($device->getId()); ?> wurde vermerkt (Ticket
                #<?php echo($ticket->getId()); ?>).<br>
                Wir bemühen uns darum dieses möglichst schnell zu beheben.<br>
                <br>
                <?php echo(is_user_logged_in() ? "Sie können den Zustand und Informationen zu Ihrem Ticket jederzeit <a href='" . home_url() . "/ticket/" . $ticket->getId() . "'>hier</a> einsehen." : "Da Sie nicht angemeldet sind, können sie den Ticketstatus nicht verfolgen."); ?>
                <br>
                <br>
                <a href="<?php echo(home_url()); ?>/create">Weiteres Problem melden</a>
            </div>
        </div>

    <?php } else { ?>

        <style>

            form {
                width: 50%;
                margin: auto;
            }

            select {
                width: 100%;
            }
			
			<?php
			
				  if(is_user_logged_in()) echo("#loginnotice { display: none; }");
				  
			?>

        </style>

        <script>

            let data = [];
            let issues = [];

            function clearSelect(e) {
                let length = e.options.length;
                for (let i = 1; i < length; i++)
                    e.options[1].remove();
            }

            function updateView(e = null, set = null) {
                let floor = document.getElementById("floor");
                let room = document.getElementById("room");
                let type = document.getElementById("type");
                let device = document.getElementById("device");
                let issue = document.getElementById("issue");
                if (e == null || set != null) {
                    clearSelect(floor);
                    for (const f in data) {
                        let option = document.createElement("option");
                        option.innerText = f;
						option.selected = set != null && set.hasOwnProperty("floor") && set["floor"] == f;
                        floor.appendChild(option);
                    }
                }
                if (e === floor && floor.value !== "null" && floor.value in data || set != null) {
                    clearSelect(room);
                    for (const r in data[floor.value]) {
                        let option = document.createElement("option");
                        option.innerText = r;
						option.selected = set != null && set.hasOwnProperty("room") && set["room"] == r;
                        room.appendChild(option);
                    }
                }
                if (floor.value === "null") {
                    document.getElementById("section1").style.display = "none";
                    room.value = "null";
                } else
                    document.getElementById("section1").style.display = null;
                if (e === room && room.value !== "null" && room.value in data[floor.value] || set != null) {
                    clearSelect(type);
                    for (const t in data[floor.value][room.value]) {
                        let option = document.createElement("option");
                        option.innerText = t;
						option.selected = set != null && set.hasOwnProperty("type") && set["type"] == t;
                        type.appendChild(option);
                    }
                }
                if (room.value === "null") {
                    document.getElementById("section2").style.display = "none";
                    type.value = "null";
                } else
                    document.getElementById("section2").style.display = null;
                if (e === type && type.value !== "null" && type.value in data[floor.value][room.value] || set != null) {
                    clearSelect(device);
                    for (const d in data[floor.value][room.value][type.value]) {
                        let option = document.createElement("option");
                        option.value = data[floor.value][room.value][type.value][d]["id"];
                        option.innerText = data[floor.value][room.value][type.value][d]["model"];
						option.selected = set != null && set.hasOwnProperty("device") && set["device"] == data[floor.value][room.value][type.value][d]["id"];
                        device.appendChild(option);
                    }
                    if (device.options.length === 2 && set == null)
                        device.options[1].selected = true;
                    clearSelect(issue);
                    for (const i in issues)
                        if (issues[i]["availability"].includes(type.value)) {
                            let option = document.createElement("option");
                            option.value = i;
                            option.innerText = issues[i]["title"];
							option.selected = set != null && set.hasOwnProperty("issue") && set["issue"] == i;
                            issue.appendChild(option);
                        }
                }
                if (type.value === "null") {
                    document.getElementById("section3").style.display = "none";
                    device.value = "null";
                } else
                    document.getElementById("section3").style.display = null;
                if (device.value === "null") {
                    document.getElementById("section4").style.display = "none";
                    issue.value = "null";
                } else
                    document.getElementById("section4").style.display = null;
                if (issue.value === "null" || data[floor.value][room.value][type.value][device.selectedIndex - 1]["tickets"].includes(parseInt(issue.value))) {
                    document.getElementById("section5").style.display = "none";
                } else
                    document.getElementById("section5").style.display = null;
                if (document.getElementById("shortname").value === "null") {
                    document.getElementById("section6").style.display = "none";
                } else
                    document.getElementById("section6").style.display = null;
                document.getElementById("section7").style.display = issue.value === "null" || !data[floor.value][room.value][type.value][device.selectedIndex - 1]["tickets"].includes(parseInt(issue.value)) ? "none" : null;
            }
			
			function redirectTo(url) {
				let device = document.getElementById("device").value;
				let issue = document.getElementById("issue").value;
				let shortname = document.getElementById("shortname").value;
				let message = document.getElementById("message").value;
				location.href = url + "?redirect_to=" + encodeURIComponent("/create?" + (device === "null" ? "" : "device=" + device + "&") + (device === "null" || issue === "null" ? "" : "issue=" + issue + "&") + (shortname === "" ? "" : "shortname=" + shortname + "&") + (document.getElementById("adminOnly").checked ? "adminOnly&" : "") + (message === "" ? "" : "message=" + message));
			}
			
			<?php
			
				$data = [];
				$device = isset($_GET["device"]) ? ITDesk::getInstance()->getDevice(urldecode($_GET["device"])) : null;
				if($device != null) {
					$data["floor"] = $device->getLocation()->getFloor();
					$data["room"] = $device->getLocation()->getId();
					$data["type"] = Constants::TYPES[$device->getModel()->getType()];
					$data["device"] = $device->getId();
				}
				$issue = isset($_GET["issue"]) ? ITDesk::getInstance()->getIssue($_GET["issue"]) : null;
				if($issue != null && in_array($device->getModel()->getType(), $issue->getAvailability()))
					$data["issue"] = $issue->getId();
		
				echo("let set = " . json_encode($data) . ";");
					
			?>

        </script>

        <div class="main main-raised">
            <div class="container section section-text">
                <form method="post">
                    <?php wp_nonce_field(); ?>
                    <label for="floor">Stockwerk:</label><br>
                    <select id="floor" onchange="updateView(this);">
                        <option value="null">--- Bitte auswählen ---</option>
                    </select><br>
                    <br>
                    <div id="section1" style="display: none;">
                        <label for="room">Raum:</label><br>
                        <select id="room" onchange="updateView(this);">
                            <option value="null">--- Bitte auswählen ---</option>
                        </select><br>
                        <br>
                        <div id="section2" style="display: none;">
                            <label for="type">Gerätetyp:</label><br>
                            <select id="type" onchange="updateView(this);">
                                <option value="null">--- Bitte auswählen ---</option>
                            </select><br>
                            <br>
                            <div id="section3" style="display: none;">
                                <label for="device">Gerät:</label><br>
                                <select id="device" name="Device" onchange="updateView(this);">
                                    <option value="null">--- Bitte auswählen ---</option>
                                </select><br>
                                <br>
                                <div id="section4" style="display: none;">
                                    <label for="issue">Problem:</label><br>
                                    <select id="issue" name="Issue" onchange="updateView(this);">
                                        <option value="null">--- Bitte auswählen ---</option>
                                    </select><br>
                                    <br>
                                    <div id="section5" style="display: none;">
                                        <label for="shortname">Kürzel:</label><br>
                                        <input type="text" id="shortname" name="Shortname" onkeyup="updateView(this);"<?php if(isset($_GET["shortname"])) echo(" value=\"" . urldecode($_GET["shortname"]) . "\""); ?>
                                               required><br>
										<div id="loginnotice">
											Sie möchten den Fortschirtt ihres Ticktes verfolgen? Melden sie sich <a href="/ticketsystem" onclick="redirectTo('/ticketsystem'); return false;">hier</a> an oder erstellen sie sich <a href="/register" onclick="redirectTo('/register'); return false;">hier</a> kostenlos ein Konto.
										</div>
										<div id="section6" style="display: none;">
                                            <input type="checkbox" id="adminOnly" name="AdminOnly"<?php if(isset($_GET["adminOnly"])) echo(" checked"); ?>> <label
                                                    for="adminOnly">Nur
                                                für
                                                die
                                                Systemadministration sichtbar</label><br>
                                            <br>
                                            <label for="message">Nachricht (optional):</label><br>
                                            <textarea id="message" name="Message" rows="5"><?php if(isset($_GET["message"])) echo(urldecode($_GET["message"])); ?></textarea><br>
                                            <br>
                                            <button type="submit">Ticket erstellen</button>
                                        </div>
                                    </div>
                                    <div id="section7" style="display: none;">
                                        Vielen Dank für dein Engagement! Jemand war aber bereits schneller als du
                                        und hat das Problem bereits gemeldet. Wir versuchen uns schnellstmöglich darum
                                        zu kümmern.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    data = JSON.parse(this.responseText);
                    issues = data["issues"];
                    data = data["data"];
                    updateView(null, set);
                }
            };
            xhttp.open("GET", "<?php echo(home_url() . "/wp-json/" . Constants::API_NAMESPACE . Constants::API_CREATE . (is_user_logged_in() ? "?_wpnonce=" . wp_create_nonce("wp_rest") : "")); ?>", true);
            xhttp.send();

        </script>

    <?php }
} ?>