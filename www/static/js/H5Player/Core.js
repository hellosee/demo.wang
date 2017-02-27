var runningMode="Product";var logMode="OneLine";var log;var canvasBG;var canvasUI;var contextBG;var contextUI;var controlPool;var autoSpinBtn;var touchDownCtl;var uiAnimationHwnd;var fingerDown;var canSlide;var isBgColorSet;var MainPhotoLayers;var bufImages;var isUltraResNeededList;var maxLayerIdx;var maxSplitCount;var MainPhotos;var MainPhotosTiny;var colCount;var rowCount;var startIdxX;var startIdxY;var currentIdxX;var currentIdxY;var lastIdxX;var lastIdxY;var isUltraResNeeded;var photoWidth;var photoHeight;var offsetX;var offsetY;var renderWidth;var renderHeight;var lastRW;var lastRH;var startWidth;var startHeight;var zoomCenterX;var zoomCenterY;var aspectRatioPhoto;var aspectRatioCanvas;var autoSpinSpeed;var autoSpinSpdCoe;var autoSpinDist;var lastMillisec;var startX0;var startY0;var startX1;var startY1;var dragSpeedX;var dragSpeedY;var dragDistX;var dragDistY;var inertiaX;var inertiaY;var inertiaDistX;var inertiaDistY;var startSpdCoeX;var speedCoeX;var startDist;var scaleCoe;var lastScaleCoe;var maxScaleCoe;var scaleOrg;var centerX;var centerY;var bgRed;var bgGreen;var bgBlue;var touchUpX;var touchUpY;var clickCount;var firstTouchX;var firstTouchY;var autoZoom;function Log(a){if("Development"==runningMode){if("OneLine"==logMode){log.innerHTML=a}else{if("Cascade"==logMode){log.innerHTML+="<br/>"+a}}}}function Initialize(){if("Development"==runningMode){log=document.getElementById("Log")}canvasBG=document.getElementById("CanvasBG");contextBG=canvasBG.getContext("2d");canvasUI=document.getElementById("CanvasUI");contextUI=canvasUI.getContext("2d");loadingProgBar=null;autoSpinBtn=null;uiAnimationHwnd=null;fingerDown=false;canSlide=true;MainPhotoLayers=[];MainPhotos=null;MainPhotosTiny=null;dragSpeedX=0;dragSpeedY=0;dragDistX=0;dragDistY=0;inertiaX=dragSpeedX;inertiaY=dragSpeedY;inertiaDistX=0;inertiaDistY=0;startSpdCoeX=3;scaleCoe=1;lastScaleCoe=scaleCoe;bgRed=bgR;bgGreen=bgG;bgBlue=bgB;isBgColorSet=false;touchUpX=-1;touchUpY=-1;touchDownCtl=null;clickCount=0;autoZoom=0;controlPool=ControlPool.BuildNew()}var _srcDirUrl;function LoadPhotos(a,f,n,p,o){_srcDirUrl=a;colCount=n;rowCount=f;startIdxX=0;startIdxY=0;currentIdxX=startIdxX;currentIdxY=startIdxY;lastIdxX=-1;lastIdxY=-1;isUltraResNeeded=true;photoWidth=p;photoHeight=o;centerX=0;centerY=0;autoSpinSpeed=0;autoSpinSpdCoe=1.5;autoSpinDist=0;lastMillisec=0;var d=360/colCount;startSpdCoeX*=d;FullScreen();DrawLoadingProgressBar();DrawAutoSpinBtn();bufImages=[];isUltraResNeededList=[];var c=4;maxLayerIdx=-1;maxSplitCount=0;for(var g=0;g<8;g++){if(lODFlag>>(7-g)&1){MainPhotoLayers[g]=[];for(var e=0;e<colCount;e++){MainPhotoLayers[g][e]=[];var b;if(g<c){b=1}else{b=2<<(g-c)}maxLayerIdx=g;maxSplitCount=b;var k=0;for(var l=0;l<b;l++){for(var m=0;m<b;m++,k++){MainPhotoLayers[g][e][k]=g.toString()+"/0_"+e.toString()+"/"+l.toString()+"_"+m.toString()+".jpg"}}}}}for(var g=0;g<maxSplitCount*maxSplitCount;g++){bufImages[g]=new Image();isUltraResNeededList[g]=false}MainPhotosTiny=[];var h=0;for(var e=0;e<rowCount;e++){MainPhotosTiny[e]=[];for(var g=0;g<colCount;g++){MainPhotosTiny[e][g]=new Image();MainPhotosTiny[e][g].src=a+MainPhotoLayers[0][e*colCount+g][0];if(MainPhotosTiny[e][g].complete){h++;loadingProgBar.SetValue(h/(colCount*rowCount)*100);if(10==h){ProceedWithInitBehavior()}}else{MainPhotosTiny[e][g].onload=function(){h++;loadingProgBar.SetValue(h/(colCount*rowCount)*100);if(10==h){ProceedWithInitBehavior()}}}}}MainPhotos=[];for(var e=0;e<rowCount;e++){MainPhotos[e]=[];for(var g=0;g<colCount;g++){MainPhotos[e][g]=new Image();MainPhotos[e][g].src=a+MainPhotoLayers[1][e*colCount+g][0]}}window.onorientationchange=OrientationChange;window.requestAnimationFrame=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||window.msRequestAnimationFrame;requestAnimationFrame(RenderLoop)}function FullScreen(){var b=document.documentElement.clientWidth;var e=document.documentElement.clientHeight;Log(b+", "+e);canvasBG.width=b;canvasBG.height=e;canvasUI.width=b;canvasUI.height=e;aspectRatioCanvas=canvasBG.width/canvasBG.height;aspectRatioPhoto=photoWidth/photoHeight;if(aspectRatioCanvas>aspectRatioPhoto){renderHeight=canvasBG.height;renderWidth=(renderHeight*aspectRatioPhoto+0.5)|0;offsetX=(((canvasBG.width-renderWidth)>>1)+0.5)|0;offsetY=0;scaleOrg=photoHeight/canvasBG.height}else{if(aspectRatioCanvas<aspectRatioPhoto){renderWidth=canvasBG.width;renderHeight=(renderWidth/aspectRatioPhoto+0.5)|0;offsetX=0;offsetY=(((canvasBG.height-renderHeight)>>1)+0.5)|0;scaleOrg=photoWidth/canvasBG.width}else{renderWidth=canvasBG.width;renderHeight=canvasBG.height;offsetX=0;offsetY=0;scaleOrg=photoWidth/canvasBG.width}}startWidth=renderWidth;startHeight=renderHeight;lastRW=renderWidth;lastRH=renderHeight;lastScaleCoe=1;maxScaleCoe=4;maxScaleCoe*=scaleOrg;dragSpeedX=0;dragSpeedY=0;dragDistX=0;dragDistY=0;inertiaX=dragSpeedX;inertiaY=dragSpeedY;inertiaDistX=0;inertiaDistY=0;var f=canvasUI.width>canvasUI.height?canvasUI.height:canvasUI.width;speedCoeX=startSpdCoeX*f/768;lastIdxX=-1;lastIdxY=-1;if(loadingProgBar){loadingProgBar.MoveTo(canvasUI.width>>2,20,canvasUI.width>>1,canvasUI.height>>4,1)}if(autoSpinBtn){var c=canvasUI.width>canvasUI.height?canvasUI.height:canvasUI.width;var d=c/5;var a=d;autoSpinBtn.MoveTo((canvasUI.width-d)>>1,canvasUI.height-a*1.2,d,a,1)}}function ProceedWithInitBehavior(){var a=0;while(null!=initBehavior[a]){switch(initBehavior[a]){case"Rotate360":initRotatePhotoAmount=colCount;break}a++}}function SwitchPhotoIdx(a,d){var c=currentIdxX;var b=currentIdxY;if(0<a){c++}else{if(0>a){c--}}if(0<d){b++}else{if(0>d){b--}}if(0>c){c+=colCount}else{if(colCount<=c){c=c-colCount}}if(0>b){b=0;d=0}else{if(rowCount<=b){b=rowCount-1;d=0}}if(MainPhotosTiny[b][c].complete){currentIdxX=c;currentIdxY=b;isUltraResNeeded=true;return true}else{Log("Photo is unavailable.");return false}}function Render(b,a){ClearCanvasGhost();if(MainPhotos[a][b].complete){contextBG.drawImage(MainPhotos[a][b],offsetX,offsetY,renderWidth,renderHeight);return 0}else{if(MainPhotosTiny[a][b].complete){contextBG.drawImage(MainPhotosTiny[a][b],offsetX,offsetY,renderWidth,renderHeight);return 1}else{return 2}}}function Render3(b,e,d,c,a){contextBG.drawImage(b,e,d,c,a)}function ValidateRenderRectSize(){var a=true;if(aspectRatioCanvas<=aspectRatioPhoto){if(renderWidth<canvasBG.width){renderWidth=canvasBG.width;renderHeight=renderWidth/aspectRatioPhoto;lastScaleCoe=1;a=false}}else{if(renderHeight<canvasBG.height){renderHeight=canvasBG.height;renderWidth=renderHeight*aspectRatioPhoto;lastScaleCoe=1;a=false}}return a}function ValidateRenderRectPos(){if(aspectRatioPhoto>=aspectRatioCanvas){if(0<offsetX){offsetX=0}else{if(offsetX+renderWidth<canvasBG.width){offsetX=canvasBG.width-renderWidth}}if(renderHeight<canvasBG.height){offsetY=(canvasBG.height-renderHeight)>>1}else{if(0<offsetY){offsetY=0}else{if(offsetY+renderHeight<canvasBG.height){offsetY=canvasBG.height-renderHeight}}}}else{if(0<offsetY){offsetY=0}else{if(offsetY+renderHeight<canvasBG.height){offsetY=canvasBG.height-renderHeight}}if(renderWidth<canvasBG.width){offsetX=(canvasBG.width-renderWidth)>>1}else{if(0<offsetX){offsetX=0}else{if(offsetX+renderWidth<canvasBG.width){offsetX=canvasBG.width-renderWidth}}}}}function Zoom(c){lastScaleCoe*=c;if(maxScaleCoe<lastScaleCoe){lastScaleCoe=maxScaleCoe}else{lastRW=renderWidth;lastRH=renderHeight;renderWidth*=c;renderHeight*=c;if(!ValidateRenderRectSize()){ValidateRenderRectPos();Render(currentIdxX,currentIdxY);return}var b=(renderWidth-lastRW)*zoomCenterX/startWidth;var a=(renderHeight-lastRH)*zoomCenterY/startHeight;offsetX-=b;offsetY-=a;ValidateRenderRectPos()}}function ClearCanvasGhost(){if(renderWidth<canvasBG.width){contextBG.fillStyle="rgb("+bgRed+", "+bgGreen+", "+bgBlue+")";var a=canvasBG.width-renderWidth>>1;contextBG.fillRect(0,0,a,canvasBG.height);contextBG.fillRect(renderWidth+a,0,a,canvasBG.height)}if(renderHeight<canvasBG.height){contextBG.fillStyle="rgb("+bgRed+", "+bgGreen+", "+bgBlue+")";var b=canvasBG.height-renderHeight>>1;contextBG.fillRect(0,0,canvasBG.width,b);contextBG.fillRect(0,renderHeight+b,canvasBG.width,b)}}function RenderLoop(){if(0<initRotatePhotoAmount){if(fingerDown||0!=autoSpinSpeed){initRotatePhotoAmount=0}else{initSpinDist+=5*autoSpinSpdCoe;if(speedCoeX<Math.abs(initSpinDist)){if(SwitchPhotoIdx(1,0)){initRotatePhotoAmount--;initSpinDist=0}}}}if(!fingerDown&&0!=autoSpinSpeed){var g=new Date();var a=g.getMilliseconds();var e=(a-lastMillisec+1000)%1000;if(speedCoeX*(11-Math.abs(autoSpinSpeed))<e){SwitchPhotoIdx(autoSpinSpeed);lastMillisec=a}}if(!fingerDown&&(0!=inertiaX||0!=inertiaY)){var l=0.9;if(0<inertiaX){inertiaX*=l;if(0.1>inertiaX){inertiaX=0}}else{if(0>inertiaX){inertiaX*=l;if(-0.1<inertiaX){inertiaX=0}}}if(0<inertiaY){inertiaY*=l;if(0.1>inertiaY){inertiaY=0}}else{if(0>inertiaY){inertiaY*=l;if(-0.1<inertiaY){inertiaY=0}}}Log(inertiaX);if(1>=lastScaleCoe){inertiaDistX+=inertiaX;inertiaDistY+=inertiaY;if(speedCoeX<Math.abs(inertiaDistX)){SwitchPhotoIdx(inertiaDistX,0);inertiaDistX=0}if(speedCoeX<Math.abs(inertiaDistY)){SwitchPhotoIdx(0,inertiaDistY);inertiaDistY=0}}else{offsetX+=inertiaX;offsetY+=inertiaY;ValidateRenderRectPos()}}if(1==autoZoom){Zoom(1.07);if(renderWidth>3*canvasBG.width){autoZoom=0}}else{if(-1==autoZoom){Zoom(0.93);if(1>=lastScaleCoe){autoZoom=0}}}var f=0;if(1>=lastScaleCoe){if(lastIdxX!=currentIdxX||lastIdxY!=currentIdxY){f=Render(currentIdxX,currentIdxY)}}else{if(isUltraResNeeded){for(var b=0;b<maxSplitCount*maxSplitCount;b++){isUltraResNeededList[b]=true}isUltraResNeeded=false}if(!fingerDown&&0!=autoSpinSpeed){Render(currentIdxX,currentIdxY)}else{for(var b=0;b<maxSplitCount*maxSplitCount;b++){if(isUltraResNeededList[b]){bufImages[b].src=_srcDirUrl+MainPhotoLayers[2][currentIdxY*colCount+currentIdxX][b];isUltraResNeededList[b]=false}}var c=0;var k=renderWidth/maxSplitCount;var d=renderHeight/maxSplitCount;for(var h=0;h<maxSplitCount;h++){for(var j=0;j<maxSplitCount;j++){if(bufImages[h*maxSplitCount+j].complete){c++}}}ClearCanvasGhost();if(c<maxSplitCount*maxSplitCount){Render(currentIdxX,currentIdxY)}for(var h=0;h<maxSplitCount;h++){for(var j=0;j<maxSplitCount;j++){if(bufImages[h*maxSplitCount+j].complete){Render3(bufImages[h*maxSplitCount+j],offsetX+k*j,offsetY+d*h,k,d)}}}}}if(0==f){lastIdxX=currentIdxX;lastIdxY=currentIdxY}controlPool.HandleLoop();requestAnimationFrame(RenderLoop)}function OrientationChange(){switch(window.orientation){case 0:case -90:case 90:case 180:setTimeout("FullScreen()",300);break}}function TouchDown(a){a=a||windows.event;a.preventDefault();fingerDown=true;clearTimeout(uiAnimationHwnd);autoSpinBtn.MoveTo((canvasUI.width-autoSpinBtn.bBox.width)>>1,canvasUI.height-autoSpinBtn.bBox.width*1.2,autoSpinBtn.bBox.width,autoSpinBtn.bBox.height,10);if(1==a.touches.length){startX0=a.touches[0].pageX;startY0=a.touches[0].pageY;touchUpX=startX0;touchUpY=startY0;touchDownCtl=controlPool.CheckTarget(startX0,startY0);if(!touchDownCtl){iDistX=0;iDistY=0;inertiaX=0;inertiaY=0;clickCount++;if(1==clickCount){firstTouchX=startX0;firstTouchY=startY0;setTimeout(function(){clickCount=0},400)}else{if(2==clickCount){var b=Math.sqrt(Math.pow(startX0-firstTouchX,2)+Math.pow(startY0-firstTouchY,2));if(50>b){zoomCenterX=startX0-offsetX;zoomCenterY=startY0-offsetY;startWidth=renderWidth;startHeight=renderHeight;if(1.2>=lastScaleCoe){autoZoom=1}else{autoZoom=-1}}clickCount=0}}}}else{if(2==a.touches.length){clickCount=0;startX0=a.touches[0].pageX;startY0=a.touches[0].pageY;startX1=a.touches[1].pageX;startY1=a.touches[1].pageY;startDist=Math.pow((Math.pow((startX1-startX0),2)+Math.pow((startY1-startY0),2)),0.5);centerX=(startX0+startX1)>>1;centerY=(startY0+startY1)>>1;zoomCenterX=centerX-offsetX;zoomCenterY=centerY-offsetY;startWidth=renderWidth;startHeight=renderHeight}}}function TouchMove(a){a=a||window.event;a.preventDefault();if(1==a.touches.length){if(canSlide){var h=a.touches[0].pageX;var f=a.touches[0].pageY;touchUpX=h;touchUpY=f;if(1==clickCount){var e=Math.sqrt(Math.pow(h-firstTouchX,2),Math.pow(f-firstTouchY,2));if(40<e){clickCount=0}}var k=h-startX0;var j=f-startY0;dragSpeedX=k;dragSpeedY=j;dragDistX+=dragSpeedX;dragDistY+=dragSpeedY;if(1==lastScaleCoe){if(speedCoeX<Math.abs(dragDistX)){if(0<dragDistX){autoSpinSpeed=Math.abs(autoSpinSpeed)}else{autoSpinSpeed=-Math.abs(autoSpinSpeed)}SwitchPhotoIdx(dragDistX,0);dragDistX=0}if(speedCoeX<Math.abs(dragDistY)){SwitchPhotoIdx(0,dragDistY);dragDistY=0}Log(currentIdxY+", "+currentIdxX+". "+MainPhotosTiny[currentIdxY][currentIdxX].complete)}else{offsetX+=k;offsetY+=j;ValidateRenderRectPos()}startX0=h;startY0=f}}else{if(2==a.touches.length){var c=a.touches[0].pageX;var i=a.touches[0].pageY;var b=a.touches[1].pageX;var g=a.touches[1].pageY;var e=Math.sqrt(Math.pow((b-c),2)+Math.pow((g-i),2));var d=e/startDist;startDist=e;Zoom(d);Log("Scale: "+lastScaleCoe)}}}function TouchUp(a){a=a||window.event;Log(a.touches.length+" touch(es).");if(2>a.touches.length){scaleCoe=lastScaleCoe;if(1==a.touches.length){Log("UnSlidable");startX0=a.touches[0].pageX;startY0=a.touches[0].pageY;canSlide=false;setTimeout(function(){canSlide=true},200)}else{if(0==a.touches.length){var b=controlPool.CheckTarget(touchUpX,touchUpY);if(b&&touchDownCtl==b){b.Click()}if(canSlide){if("Pressed"==autoSpinBtn.status&&1>=lastScaleCoe){inertiaX=0;inertiaY=0}else{inertiaX=dragSpeedX;inertiaY=dragSpeedY}dragSpeedX=0;dragSpeedY=0}else{canSlide=true}fingerDown=false}}}uiAnimationHwnd=setTimeout(function(){autoSpinBtn.MoveTo((canvasUI.width-autoSpinBtn.bBox.width)>>1,canvasUI.height,autoSpinBtn.bBox.width,autoSpinBtn.bBox.height,10)},2000)}function MouseDown(a){a=a||windows.event;a.preventDefault();fingerDown=true;clearTimeout(uiAnimationHwnd);autoSpinBtn.MoveTo((canvasUI.width-autoSpinBtn.bBox.width)>>1,canvasUI.height-autoSpinBtn.bBox.width*1.2,autoSpinBtn.bBox.width,autoSpinBtn.bBox.height,10);startX0=a.clientX;startY0=a.clientY;touchUpX=startX0;touchUpY=startY0;touchDownCtl=controlPool.CheckTarget(startX0,startY0);if(!touchDownCtl){iDistX=0;iDistY=0;inertiaX=0;inertiaY=0;clickCount++;if(1==clickCount){firstTouchX=startX0;firstTouchY=startY0;setTimeout(function(){clickCount=0},400)}else{if(2==clickCount){var b=Math.sqrt(Math.pow(startX0-firstTouchX,2)+Math.pow(startY0-firstTouchY,2));if(50>b){zoomCenterX=startX0-offsetX;zoomCenterY=startY0-offsetY;startWidth=renderWidth;startHeight=renderHeight;if(1.2>=lastScaleCoe){autoZoom=1}else{autoZoom=-1}}clickCount=0}}}}function MouseMove(c){startWidth=renderWidth;startHeight=renderHeight;centerX=c.clientX;centerY=c.clientY;zoomCenterX=centerX-offsetX;zoomCenterY=centerY-offsetY;if(!fingerDown){return}c=c||window.event;c.preventDefault();if(canSlide){var b=c.clientX;var f=c.clientY;touchUpX=b;touchUpY=f;if(1==clickCount){var d=Math.sqrt(Math.pow(b-firstTouchX,2),Math.pow(f-firstTouchY,2));if(40<d){clickCount=0}}var a=b-startX0;var e=f-startY0;dragSpeedX=a;dragSpeedY=e;dragDistX+=dragSpeedX;dragDistY+=dragSpeedY;if(1>=lastScaleCoe){Log(speedCoeX+" : "+dragDistX);if(speedCoeX<Math.abs(dragDistX)){if(0<dragDistX){autoSpinSpeed=Math.abs(autoSpinSpeed)}else{autoSpinSpeed=-Math.abs(autoSpinSpeed)}SwitchPhotoIdx(dragDistX,0);dragDistX=0}if(speedCoeX<Math.abs(dragDistY)){SwitchPhotoIdx(0,dragDistY);dragDistY=0}}else{offsetX+=a;offsetY+=e;ValidateRenderRectPos()}startX0=b;startY0=f}}function MouseUp(a){a=a||window.event;scaleCoe=lastScaleCoe;var b=controlPool.CheckTarget(touchUpX,touchUpY);if(b&&touchDownCtl==b){b.Click()}if(canSlide){if("Pressed"==autoSpinBtn.status&&1>=lastScaleCoe){inertiaX=0;inertiaY=0}else{inertiaX=dragSpeedX;inertiaY=dragSpeedY}dragSpeedX=0;dragSpeedY=0}else{canSlide=true}fingerDown=false;uiAnimationHwnd=setTimeout(function(){autoSpinBtn.MoveTo((canvasUI.width-autoSpinBtn.bBox.width)>>1,canvasUI.height,autoSpinBtn.bBox.width,autoSpinBtn.bBox.height,10)},2000)}function MouseWheel(a){a=a||window.event;a.preventDefault();if(0<a.wheelDelta){Zoom(1.15)}else{if(0>a.wheelDelta){Zoom(0.85)}}Log("Scale: "+lastScaleCoe)}function DrawLoadingProgressBar(){var x = (canvasUI.width >> 1) - 200;var y = 10;var width = 400;var height = 30;if (0 > x){x = 0;width = canvasUI.width;}loadingProgBar = ProgressBar.BuildNew(x, y, width, height);controlPool.Add(loadingProgBar)}function DrawAutoSpinBtn(){var b=canvasUI.width>canvasUI.height?canvasUI.height:canvasUI.width;var c=b/8;var a=c;autoSpinBtn=Button.BuildNew((canvasUI.width-c)>>1,canvasUI.height-a*1.2,c,a,"/static/js/H5Player/UI/AutoSpinNormal.png","/static/js/H5Player/UI/AutoSpinPressed.png");autoSpinBtn.OnClick=function(){if(0==autoSpinSpeed){if(0<=inertiaX){autoSpinSpeed=7}else{autoSpinSpeed=-7}}else{autoSpinSpeed=0}};controlPool.Add(autoSpinBtn)}function BoundingBox(b,d,c,a){this.x=b;this.y=d;this.width=c;this.height=a}var Control={BuildNew:function(b,f,c,a){b=(b+0.5)|0;f=(f+0.5)|0;c=(c+0.5)|0;a=(a+0.5)|0;var e={};e.bBox=new BoundingBox(b,f,c,a);e.lastBBox=new BoundingBox(b,f,c,a);e.startBBox=new BoundingBox(b,f,c,a);e.endBBox=new BoundingBox(b,f,c,a);e.frameIdxNow=0;e.frameCount=0;e.needValidate=false;e.visible=true;e.enable=true;e.iUpdate=null;e.Show=function(){e.visible=true;e.needValidate=true};e.Hide=function(){e.visible=false;e.needValidate=true};e.MoveTo=function(j,i,k,h,g){e.startBBox.x=e.bBox.x;e.startBBox.y=e.bBox.y;e.startBBox.width=e.bBox.width;e.startBBox.height=e.bBox.height;e.endBBox.x=(j+0.5)|0;e.endBBox.y=(i+0.5)|0;e.endBBox.width=(k+0.5)|0;e.endBBox.height=(h+0.5)|0;e.frameCount=g;e.frameIdxNow=0;d();e.needValidate=true};var d=function(){e.frameIdxNow++;var k=e.frameIdxNow/e.frameCount;var j=e.startBBox.x+(e.endBBox.x-e.startBBox.x)*k;var i=e.startBBox.y+(e.endBBox.y-e.startBBox.y)*k;var h=e.startBBox.width+(e.endBBox.width-e.startBBox.width)*k;var g=e.startBBox.height+(e.endBBox.height-e.startBBox.height)*k;e.bBox.x=(j+0.5)|0;e.bBox.y=(i+0.5)|0;e.bBox.width=(h+0.5)|0;e.bBox.height=(g+0.5)|0};e.Update=function(){contextUI.clearRect(e.lastBBox.x,e.lastBBox.y,e.lastBBox.width,e.lastBBox.height);e.lastBBox.x=e.bBox.x;e.lastBBox.y=e.bBox.y;e.lastBBox.width=e.bBox.width;e.lastBBox.height=e.bBox.height;if(e.visible){e.iUpdate()}if(e.frameIdxNow>=e.frameCount){e.needValidate=false}else{d()}};return e}};var Button={BuildNew:function(j,i,a,m,g,e){j=(j+0.5)|0;i=(i+0.5)|0;a=(a+0.5)|0;m=(m+0.5)|0;var f=Control.BuildNew(j,i,a,m);f.type="Switch";f.status="Normal";var l=null;var c=null;var k=null;var h=null;if(!g||!e){contextUI.fillStyle="#00FFFF";contextUI.fillRect(j,i,a,m)}else{l=new Image();l.src=g;if(l.complete){k=document.createElement("canvas");k.width=a;k.height=m;var d=k.getContext("2d");d.drawImage(l,0,0,k.width,k.height);contextUI.drawImage(k,j,i,a,m)}else{l.onload=function(){k=document.createElement("Canvas");k.width=a;k.height=m;var n=k.getContext("2d");n.drawImage(l,0,0,k.width,k.height);contextUI.drawImage(k,j,i,a,m)}}c=new Image();c.src=e;if(c.complete){h=document.createElement("canvas");h.width=a;h.height=m;var b=h.getContext("2d");b.drawImage(c,0,0,h.width,h.height)}else{c.onload=function(){h=document.createElement("Canvas");h.width=a;h.height=m;var n=h.getContext("2d");n.drawImage(c,0,0,h.width,h.height)}}}f.OnPress=function(){f.status="Pressed";f.needValidate=true};f.OnRelease=function(){f.status="Normal";f.needValidate=true};f.Click=function(){if("Switch"==f.type){switch(f.status){case"Normal":f.OnPress();break;case"Pressed":f.OnRelease();break}}f.OnClick()};f.OnClick=null;f.iUpdate=function(){switch(f.status){case"Normal":if(!l){return}if(l.complete){contextUI.drawImage(k,f.bBox.x,f.bBox.y,f.bBox.width,f.bBox.height)}else{l.onload=function(){contextUI.drawImage(k,f.bBox.x,f.bBox.y,f.bBox.width,f.bBox.height)}}break;case"Pressed":if(!c){return}if(c.complete){contextUI.drawImage(h,f.bBox.x,f.bBox.y,f.bBox.width,f.bBox.height)}else{c.onload=function(){contextUI.drawImage(h,f.bBox.x,f.bBox.y,f.bBox.width,f.bBox.height)}}break}};return f}};var ProgressBar={BuildNew:function(b,e,c,a){var d=Control.BuildNew(b,e,c,a);d.percentage=0;d.SetValue=function(f){d.percentage=(f+0.5)|0;d.needValidate=true};d.iUpdate=function(){if(!d.visible){return}var g;if(100<=d.percentage){g="已加载100%！";setTimeout(function(){contextUI.clearRect(d.bBox.x,d.bBox.y,d.bBox.width,d.bBox.height);d.visible=false},1200)}else{g="已加载"+d.percentage+"%，请稍候…"}var completePixelLength = d.percentage * (d.bBox.width - 2) / 100;	contextUI.fillStyle = '#939393';contextUI.font = '16px Franklin Gothic Medium';contextUI.fillText('Loading . . .', d.bBox.x + ((d.bBox.width >> 1) - 30), d.bBox.y + 16);contextUI.fillStyle = 'rgba(179, 179, 179, 0.6)';contextUI.fillRect(d.bBox.x, d.bBox.y + 20, d.bBox.width, 5);contextUI.fillStyle = '#FFFFFF';	contextUI.fillRect(d.bBox.x + 1 + completePixelLength, d.bBox.y + 21, d.bBox.width - completePixelLength - 2, 3);contextUI.fillStyle = 'rgba(152, 204, 243, 0.6)';contextUI.fillRect(d.bBox.x + 1, d.bBox.y + 21, completePixelLength, 3);};return d}};var Slider={BuildNew:function(b,e,c,a){var d=Control.BuildNew(b,e,c,a);d.slidable=true;return d}};var ControlPool={BuildNew:function(){var c={};var b=[];var a=0;c.Add=function(d){b[a++]=d};c.CheckTarget=function(d,f){for(var e=0;e<a;e++){if(!b[e].enable||!b[e].visible){continue}if(b[e].bBox.x<=d&&b[e].bBox.x+b[e].bBox.width>d&&b[e].bBox.y<=f&&b[e].bBox.y+b[e].bBox.height>f){return b[e]}}return null};c.HandleLoop=function(){for(var d=0;d<a;d++){if(b[d].needValidate){b[d].Update()}}};return c}};