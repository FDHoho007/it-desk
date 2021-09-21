<style>

    .sb-box {
        display: inline-block;
        border: 1px solid #bbb;
        min-width: 250px;
        border-radius: 3px;
        background: #eee;
        margin: 10px;
        vertical-align: top;
    }

    .sb-title {
        border-bottom: 1px solid #bbb;
        padding: 10px;
        font-weight: bold;
    }

    .sb-body {
        min-height: 40px;
    }

    .sb-body.limit-height {
        max-height: 500px;
        overflow-y: auto;
    }

    .sb-entry {
        min-width: 218px;
        padding: 5px;
        margin: 10px;
        border: 1px solid #aaa;
        background: #fff;
        cursor: move;
    }

    .sb-entry a {
        text-decoration: none;
    }

    .grey {
        color: #999;
    }

    .sb-entry img {
        float: left;
        margin-right: 5px;
    }

</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>

<script>

    let data = [];
	let json = [];
    let dragging = false;
    let dragOver = null;

    function dragElement(elmnt) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        elmnt.onmousedown = function (e) {
            e = e || window.event;
            e.preventDefault();
            pos3 = e.clientX;
            pos4 = e.clientY;
            elmnt.style.position = "absolute";
            document.onmouseup = function (e) {
                document.onmouseup = null;
                document.onmousemove = null;
                elmnt.style.position = "";
                elmnt.style.top = "";
                elmnt.style.left = "";
                if (dragOver != null) {
                    dragOver.style.minHeight = "";
                    dragOver.appendChild(elmnt);
                    let state = parseInt(dragOver.id.substring(6, 7));
                    updateTicket(parseInt(elmnt.id.substring(7)), state, state === 0 ? parseInt(dragOver.id.substring(8, 9)) : 3);
                }
                setTimeout(function () {
                    dragging = false;
                }, 0);
            };
            document.onmousemove = function (e) {
                dragging = true;
                e = e || window.event;
                e.preventDefault();
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                elmnt.style.top = (elmnt.offsetTop - pos2 - 10) + "px";
                elmnt.style.left = (elmnt.offsetLeft - pos1 - 10) + "px";
                let over = document.elementsFromPoint(pos3, pos4);
                let c = null;
                for (let e of over)
                    if (e.classList.contains("sb-body") && e.id !== "") {
                        c = e;
                        break;
                    }
                if (c != null) {
                    if (dragOver != null && c !== dragOver)
                        dragOver.style.minHeight = "";
                    if ((dragOver == null || c !== dragOver) && c.id !== "board-1-other")
                        c.style.minHeight = (c.offsetHeight === 40 ? (c.offsetHeight + 46) : (c.offsetHeight + 73)) + "px";
                } else if (dragOver != null)
                    dragOver.style.minHeight = "";
                dragOver = c != null && c.id === "board-1-other" ? null : c;
            };
        };
    }

    function updateTicket(id, state, level) {
        let xhttp = new XMLHttpRequest();
        xhttp.open("GET", "<?php echo(home_url() . "/wp-json/" . Constants::API_NAMESPACE . Constants::API_TICKET); ?>?id=" + id + "&state=" + state + "&level=" + level + "&_wpnonce=<?php echo(wp_create_nonce("wp_rest")); ?>", true);
        xhttp.send();
    }

    function checkA(event) {
        if (dragging)
            event.preventDefault();
        return !dragging;
    }

    function updateBoard() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.state === 403)
                location.reload();
            else if (this.readyState === 4 && this.status === 200) {
                json = JSON.parse(this.responseText);
                let tickets = [];
                for (let ticket of json) {
                    tickets.push(ticket["id"]);
                    if (ticket["id"] in data) {

                    } else {
                        let div = document.createElement("div");
                        div.id = "ticket-" + ticket["id"];
                        div.classList.add("sb-entry");
                        let img = document.createElement("img");
                        img.src = ticket["img"];
                        img.height = 55;
                        div.appendChild(img);
                        let info = document.createElement("div");
                        info.classList.add("info");
                        let span = document.createElement("span");
                        span.classList.add("grey");
                        span.innerText = ticket["author"];
                        info.appendChild(span);
                        let a = document.createElement("a");
                        a.innerText = " #" + ticket["id"];
                        a.href = "<?php echo(home_url()); ?>/ticket/" + ticket["id"];
                        a.onclick = checkA;
                        a.style.fontWeight = "bold";
                        info.appendChild(a);
                        info.appendChild(document.createElement("br"));
                        a = document.createElement("a");
                        a.innerText = ticket["issue"];
                        a.href = "<?php echo(home_url()); ?>/ticket/" + ticket["id"];
                        a.onclick = checkA;
                        info.appendChild(a);
                        info.appendChild(document.createElement("br"));
                        a = document.createElement("a");
                        a.href = "<?php echo(home_url()); ?>/device/" + ticket["device"]["id"];
                        a.onclick = checkA;
                        a.classList.add("grey");
                        a.innerText = ticket["device"]["room"] + "/" + ticket["device"]["type"];
                        info.appendChild(a);
                        div.appendChild(info);
                        data[ticket["id"]] = ticket["status"];
                        let parent = null;

                        if (parent == null)
                            if (ticket["status"] === 0)
                                parent = document.getElementById("board-" + ticket["status"] + "-" + ticket["level"]);
                            else if (ticket["status"] === 1)
                                parent = document.getElementById("board-" + ticket["status"] + "-" + (ticket["me"] ? "me" : "other"));
                            else if (ticket["status"] === 2)
                                parent = document.getElementById("board-" + ticket["status"]);
                        if (parent != null) {
                            parent.appendChild(div);
                            //updateBox(parent);
                        }
                        dragElement(div);
                    }
                }
                for (let ticket in data) {
                    if (!tickets.includes(parseInt(ticket))) {
                        document.getElementById("ticket-" + ticket).remove();
                        delete data[ticket];
                    }
                }
            }
        };
        xhttp.open("GET", "<?php echo(home_url() . "/wp-json/" . Constants::API_NAMESPACE . Constants::API_TICKETS . (is_user_logged_in() ? "?_wpnonce=" . wp_create_nonce("wp_rest") : "")); ?>", true);
        xhttp.send();
    }
	
	var generateData = function() {
	  var result = [];
	  for (var i = 0; i < json.length; i++)
		result.push({
			ID: "#"+json[i]["id"],
			Zustand: json[i]["statusText"],
			Gerät: json[i]["device"]["room"] + "/" + json[i]["device"]["type"] + " (" + json[i]["device"]["id"] + ")",
			Problem: json[i]["issue"],
			Melder: json[i]["author"],
			Kürzel: json[i]["shortName"],
			Bearbeiter: json[i]["operator"]
		});
	  return result;
	};

	function createHeaders(keys) {
	  var result = [];
	  for (var i = 0; i < keys.length; i += 1) {
		result.push({
		  id: keys[i],
		  name: keys[i],
		  prompt: keys[i],
		  width: 65,
		  align: "center",
		  padding: 0
		});
	  }
	  return result;
	}

	const headers = createHeaders([
	  "ID",
	  "Zustand",
	  "Gerät",
	  "Problem",
	  "Melder",
	  "Kürzel",
	  "Bearbeiter"
	]);

	function createPDF() {
		var doc = new jspdf.jsPDF({ putOnlyUsedFonts: true, orientation: "landscape" });
		doc.table(1, 1, generateData(), headers, { autoSize: true });
		doc.save("tickets.pdf");
	}
	
</script>

<div class="wrap">

    <h1 style="margin-bottom: 15px;" class="wp-heading-inline">Tickets</h1>
    <a href="#" onclick="createPDF();" class="page-title-action">PDF Erstellen</a><br><br>

    <div class="sb-box">
        <div class="sb-title">Offen</div>
        <div class="sb-body">
            <div class="sb-box">
                <div class="sb-title">First-Level-Support</div>
                <div class="sb-body limit-height" id="board-0-1">
                </div>
            </div>
            <div class="sb-box">
                <div class="sb-title">Second-Level-Support</div>
                <div class="sb-body limit-height" id="board-0-2">
                </div>
            </div>
            <div class="sb-box">
                <div class="sb-title">Third-Level-Support</div>
                <div class="sb-body limit-height" id="board-0-3">
                </div>
            </div>
        </div>
    </div>

    <div class="sb-box">
        <div class="sb-title">In Bearbeitung</div>
        <div class="sb-body">
            <div class="sb-box">
                <div class="sb-title">Meine Tickets</div>
                <div class="sb-body limit-height" id="board-1-me">
                </div>
            </div>
            <div class="sb-box">
                <div class="sb-title">Fremde Tickets</div>
                <div class="sb-body limit-height" id="board-1-other">
                </div>
            </div>
        </div>
    </div>

</div>

<script>

    updateBoard();
    setInterval(updateBoard, 3000);

</script>