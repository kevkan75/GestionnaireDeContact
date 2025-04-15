document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript chargé !");
    const button = document.querySelector("button");
    if (button) {
        button.addEventListener("click", function () {
            alert("Vous avez cliqué sur le bouton !");
        });
    }
});
