document.onkeydown = function(evt) {
	evt = evt || window.event;
	var isEscape = false;
	if("key" in evt) {
		isEscape = (evt.key === "Escape" || evt.key === "Esc");
	} else {
		isEscape = (evt.keyCode === 27);
	}
	if(isEscape) {
		if(drawboardcontainer.style.visibility != 'visible') {
			initBoard();
			resize();
			drawboardcontainer.style.visibility = 'visible';
		} else {
			drawboardcontainer.style.visibility = 'collapse';
		}
	}
};

var canvas, ctx, flag = false,
	prevX = 0,
	currX = 0,
	prevY = 0,
	currY = 0,
	dot_flag = false;
var x = "black", y = 2;

function initBoard() {
	canvas = document.getElementById('drawboard');
	ctx = canvas.getContext("2d");
	w = canvas.width;
	h = canvas.height;
	canvas.addEventListener("mousemove", function (e) {
		findxy('move', e)
	}, false);
	canvas.addEventListener("mousedown", function (e) {
		findxy('down', e)
	}, false);
	canvas.addEventListener("mouseup", function (e) {
		findxy('up', e)
	}, false);
	canvas.addEventListener("mouseout", function (e) {
		findxy('out', e)
	}, false);
}

function color(obj) {
	switch(obj.id) {
		case "green":
			x = "green"; break;
		case "blue":
			x = "blue"; break;
		case "red":
			x = "red"; break;
		case "yellow":
			x = "yellow"; break;
		case "orange":
			x = "orange"; break;
		case "black":
			x = "black"; break;
		case "white":
			x = "white"; break;
		}
		if(x == "white") y = 14;
		else y = 2;
}

function draw() {
	ctx.beginPath();
	ctx.moveTo(prevX, prevY);
	ctx.lineTo(currX, currY);
	ctx.strokeStyle = x;
	ctx.lineWidth = y;
	ctx.stroke();
	ctx.closePath();
}

function erase() {
	var m = confirm("Want to clear");
	if(m) {
		ctx.clearRect(0, 0, w, h);
		document.getElementById("canvasimg").style.display = "none";
	}
}

function save() {
	document.getElementById("canvasimg").style.border = "2px solid";
	var dataURL = canvas.toDataURL();
	document.getElementById("canvasimg").src = dataURL;
	document.getElementById("canvasimg").style.display = "inline";
}

function findxy(res, e) {
	if(res == 'down') {
		prevX = currX;
		prevY = currY;
		currX = e.clientX - canvas.offsetLeft;
		currY = e.clientY - canvas.offsetTop;
		flag = true;
		dot_flag = true;
		if(dot_flag) {
			ctx.beginPath();
			ctx.fillStyle = x;
			ctx.fillRect(currX, currY, 2, 2);
			ctx.closePath();
			dot_flag = false;
		}
	}
	if(res == 'up' || res == "out") {
		flag = false;
	}
	if(res == 'move') {
		if(flag) {
			prevX = currX;
			prevY = currY;
			currX = e.clientX - canvas.offsetLeft;
			currY = e.clientY - canvas.offsetTop;
			draw();
		}
	}
}

function resize() {
	if(ctx.canvas.width != window.innerWidth
	|| ctx.canvas.height != window.innerHeight) {
		ctx.canvas.width = window.innerWidth;
		ctx.canvas.height = window.innerHeight;
	}
}
