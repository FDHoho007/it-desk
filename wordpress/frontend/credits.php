<!doctype html>
<html lang="de">

<head>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Credits</title>
    <meta name="author" content="Fabian Dietrich">
    <meta name="description" content="Honor to those who deserve it.">
    <script src="https://www.youtube.com/iframe_api"></script>

    <style>

        @import url('https://fonts.googleapis.com/css2?family=Shadows+Into+Light&display=swap');

        body {
            margin: 0;
            background: #000;
            color: #fff;
            text-align: center;
            font-family: 'Shadows Into Light', cursive;
            cursor: none;
			user-select: none;
        }

        main {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow-y: hidden;
        }

        main > * {
            opacity: 0;
            transition: opacity 5s ease-in-out;
        }

        h1 {
            font-size: 200px;
        }

        div {
            font-size: 130px;
        }

        table {
            width: 100%;
        }

        table td {
            padding: 50px;
            width: 50%;
        }

        table td:first-child {
            text-align: right;
            font-weight: bold;
        }

        table td:last-child {
            text-align: left;
            font-size: 80px;
        }

        img {
            background: #1b1b1b;
            border-radius: 50%;
            box-shadow: 0 0 20px 20px #1b1b1b;
            height: 512px;
            margin-top: 150px;
        }
		
		#bg-music {
			display: none;
		}

    </style>

    <script>

        let fast = false;

    </script>

</head>

<body oncontextmenu="return false;" onkeydown="if(event.keyCode === 38) fast = true;"
      onkeyup="if(event.keyCode === 38) fast = false;">

<main>
    <h1 style="margin-top: 400px;">Credits</h1>

    <div>
        <h1>Ticket Client 1.0</h1>
        <table>
            <tr>
                <td>Idee</td>
                <td>Marco Dittfeld</td>
            </tr>
            <tr>
                <td>Umsetzung</td>
                <td>Marco Dittfeld</td>
            </tr>
        </table>
        <h1>Ticket Client 2.0</h1>
        <table>
            <tr>
                <td>Idee</td>
                <td>Fabian Dietrich</td>
            </tr>
            <tr>
                <td>inspiriert von</td>
                <td>Marco Dittfeld</td>
            </tr>
            <tr>
                <td></td>
                <td>Swantje Ebersberger</td>
            </tr>
            <tr>
                <td></td>
                <td>Peter Scharnagl</td>
            </tr>
            <tr>
                <td>Entwicklung</td>
                <td>Fabian Dietrich</td>
            </tr>
            <tr>
                <td>getestet von</td>
                <td>Timo Drüen</td>
            </tr>
            <tr>
                <td></td>
                <td>Swantje Ebersberger</td>
            </tr>
            <tr>
                <td></td>
                <td>Peter Scharnagl</td>
            </tr>
            <tr>
                <td>weitere Quellen der Inspiration</td>
                <td>Github</td>
            </tr>
            <tr>
                <td></td>
                <td>YouTrack</td>
            </tr>
			<tr>
				<td>Musik</td>
				<td>Romantic Flight - John Powell</td>
			</tr>
        </table>
        <img alt="Logo" src="https://it.student-gymp.de/wp-content/uploads/2020/09/itcrowd.png"><br>
        IT Crowd 2020
    </div>
    <div id="bg-music"></div>
</main>

<script>

    setTimeout(function () {
        let e = document.getElementsByTagName("h1")[0];
        e.style.opacity = "1";
        setTimeout(function () {
            let d = document.getElementsByTagName("div")[0];
            d.style.opacity = "1";
            setInterval(function () {
                e.style.marginTop = parseInt(e.style.marginTop) - 1 - (fast ? 9 : 0) + "px";
                if (parseInt(e.style.marginTop) < -1 * (e.offsetHeight + d.offsetHeight - 420))
                    location.href = "https://it.student-gymp.de/";
            }, 20);
        }, 5 * 1000);
    }, 10);

    let player;

    function onYouTubeIframeAPIReady() {
        player = new YT.Player('bg-music', {
            width: 0,
            height: 0,
            host: 'https://www.youtube-nocookie.com',
            videoId: 'ZJR8tuO-mIU',
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange
            }
        });
    }

    function onPlayerReady() {
        //player.f.style.display = "none";
        player.setVolume(10);
        player.playVideo();
    }

    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.ENDED)
            ;//player.playVideo();
    }

</script>

</body>

</html>