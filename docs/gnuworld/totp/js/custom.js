$(document).ready(function() {
    $("label").inFieldLabels();
});
function toggleQr() {
    $("#qr-hide-elem").toggle();
    $("#qr-div").toggle();
    $("#qr-show-elem").toggle();
    $("#key-div").toggle();
};
function showTwoStepAppsDialog() {
    $( "#twostep-apps" ).dialog({
        dialogClass: 'twostep',
        modal: true,
        minWidth: 490,
        position: { my: "center", at: "center", of: $(".tsdialog") }
    });
};
