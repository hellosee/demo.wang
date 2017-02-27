<!DOCTYPE html>
<html>
<style type = "text/css">
    #CanvasBG
    {
        position: absolute;
        top: 0px;
        left: 0px;
        margin: 0px;
        border: 0px;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    #CanvasUI
    {
        position: absolute;
        top: 0px;
        left: 0px;
        margin: 0px;
        border: 0px;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    #Log
    {
        position: absolute;
        top: 0px;
        left: 0px;
        background-color: #ff0000;
        font-size: 300%;
        z-index: 2;
    }
</style>

<head>
    <meta charset="utf-8" />
    <script>
        var AutoSpinNormal = "";
        var AutoSpinPressed = "";
    </script>
    <script src="<?php echo SITE_URL; ?>/products_unzip/<?php echo $data['prourl']; ?>/H5Player/Core.js" type="text/javascript" ></script>
    <title><?php echo $data['name']; ?></title>
</head>

<body>
<p id = "Log"></p>

<img id = "Thumbnail" src = "<?php echo SITE_URL; ?>/products_unzip/<?php echo $data['prourl']; ?>/Thumbnail.jpg" style = "width:0px; height:0px; overflow:hidden;"></img>

<canvas id="CanvasBG">非常抱歉！您的浏览器不支持Html5体验，请使用其它浏览器尝试。</canvas>
<canvas id="CanvasUI" ontouchstart="TouchDown(event)" ontouchmove="TouchMove(event)" ontouchend="TouchUp(event)"onmousedown="MouseDown(event)" onmousemove="MouseMove(event)" onmouseup="MouseUp(event)" onmousewheel="MouseWheel(event)"></canvas>
<script type="text/javascript">
    var bgR = 254;
    var bgG = 253;
    var bgB = 254;
    var initBehavior = [];
    initBehavior[0] = "Rotate360";
    var initSpinDist = 0;
    var initRotatePhotoAmount = 0;
    var lODFlag = 224;
    window.onload = function(e)
    {
        Initialize();
        LoadPhotos("<?php echo SITE_URL; ?>/products_unzip/<?php echo $data['prourl']; ?>/H5Src/", 1, 48, 3000, 4500);
    }
</script>
</body>
</html>