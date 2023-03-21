document.onkeydown = function (event)  {
    console.log(`Key down:[${event.key}] [${event.code}]`);
}

let element = document.getElementById("unity-container");
element.onmousedown = function (event)  {
    console.log(`Mouse down:[${event.key}] [${event.buttons}]`);
}
