const line1 = document.getElementById('line1');
const line2 = document.getElementById('line2');

const text1 = '¡Bienvenido/a a una';
const text2 = 'nueva aventura!';

function reset() {
    line1.style.width = '0ch';
    line1.style.borderRight = '3px solid white';
    line2.style.width = '0ch';
    line2.style.opacity = '0';
    line2.style.borderRight = '3px solid white';
    line1.classList.remove('animate-line1');
    line2.classList.remove('animate-line2');
    void line1.offsetWidth;  // reflow
    void line2.offsetWidth;
}

function startAnimation() {
    line1.textContent = text1;
    line2.textContent = text2;
    line1.classList.add('animate-line1');
    line2.classList.add('animate-line2');
}

function loop() {
    reset();
    setTimeout(startAnimation, 50); // pequeño delay para que tome efecto reset
}

loop(); // iniciar primera vez

setInterval(loop, 9000);
window.onload = function() {
    const popupOverlay = document.getElementById('popup-overlay');
    const popup = document.getElementById('popup');
    const msg = document.getElementById('popup-message');
    const messageData = document.getElementById('message-data');
    const error = messageData.dataset.error;
    const success = messageData.dataset.success;

    // Ocultar popup por defecto
    popupOverlay.style.display = 'none';

    if (error && error.trim().length > 0) {
        msg.textContent = error.trim();
        popup.style.borderColor = "red";
        msg.style.color = "white";
        popup.style.borderRadius = "15px";
        popupOverlay.style.display = "flex";
    } else if (success && success.trim().length > 0) {
        msg.textContent = success.trim();
        popup.style.borderColor = "green";
        msg.style.color = "white";
        popup.style.borderRadius = "15px";
        popup.style.display = "flex";
        popupOverlay.style.display = "flex";
    }else {
        popupOverlay.style.display = "none"; // Nada que mostrar
    }
};
