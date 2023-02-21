function changetoggle() {
    if (document.getElementsByClassName("inbound")) {
        document.getElementsByClassName("inbound").class = "outbound";
    } else {
        document.getElementsByClassName("outbound").class = "inbound";
    }
}