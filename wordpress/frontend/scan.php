<?php
function custom_page_title($title = "")
{
    return $title != null ? $title : "Gerätebarcode scannen";
}

function custom_page_subtitle()
{
    return "";
}

function custom_page()
{
    ?>
    <script src="<?php echo(home_url()); ?>/wp-content/plugins/it-desk/wordpress/frontend/public/quagga.min.js"></script>
    <style>
        #scanner {
            width: 75%;
            margin: 30px auto;
        }

        #scanner video {
            display: block;
            margin: auto;
        }

        #scanner canvas {
            display: none;
        }
    </style>

    <div class="main main-raised">
        <div class="container section section-text">
            <div id="scanner"></div>
            <div style="text-align: center">Bitte stellen sie sicher, dass ihr Gerät über eine Kamera verfügt und diese
                für die Website verfügbar ist.
            </div>
			<br>
			<form onsubmit="location.href = '/device/' + document.getElementById('tdi').value; return false;" style="text-align: center;">
				<input type="text" id="tdi" style="display: inline-block; width: auto;" placeholder="Barcode" required>
				<button type="submit">Gerät ansehen</button>
			</form>
        </div>
    </div>

    <script>
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner')
            },
            decoder: {
                readers: ["code_128_reader"]
            }
        }, function (err) {
            if (err) {
                console.log(err);
                return
            }
            console.log("Initialization finished. Ready to start");
            Quagga.start();
        });
        Quagga.onDetected(function (result) {
            location.href = "<?php echo(home_url()); ?>/device/" + result.codeResult.code;
        });
    </script>
<?php } ?>