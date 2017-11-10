
hideAllCodes = function()
{
    var codes = document.getElementsByClassName('source-code');
    for (let i=0; i<codes.length; i++) {
        codes[i].style.display = 'none';
    }
}

removeTraceEntrySelection = function()
{
    var traceEntries = document.getElementsByClassName('trace-entry');

    for (let i=0; i<traceEntries.length; i++) {
        //console.log(traceEntries[i]);
        traceEntries[i].classList.remove('trace-entry-selected');
    }
}

registertraceEntryClick = function()
{
    var traceEntries = document.getElementsByClassName('trace-entry');

    for (let i=0; i<traceEntries.length; i++) {
        traceEntries[i].onclick = function(element) {
            removeTraceEntrySelection();
            hideAllCodes();
            selectTraceEntry(this);
        };
    }
}

selectTraceEntry = function(traceEntry)
{
    traceEntry.classList.add('trace-entry-selected');
    var code = document.getElementById('code-' + traceEntry.dataset.code);
    code.style.display = 'block';

    // Try to center selected line
    var codeFrame = document.getElementById('code-frame');
    var selectedLine = code.getElementsByClassName('selected-line')[0];
    codeFrame.scrollTop = selectedLine.offsetTop - codeFrame.offsetHeight/2;
}

buildPage = function()
{
    var exceptionMonitorHead = document.getElementById('exception-monitor-head');
    var exceptionMonitorContent = document.getElementById('exception-monitor-content');
    var body = document.getElementsByTagName('body')[0];
    var head = document.getElementsByTagName('head')[0];

    head.innerHTML = exceptionMonitorHead.innerHTML;
    body.innerHTML =
        '<div id="exception-monitor-content" class="exception-monitor">'
        + exceptionMonitorContent.innerHTML
        + '</div>';
}

window.onload = function() {
    buildPage();
    removeTraceEntrySelection();
    registertraceEntryClick();
    hideAllCodes();

    // Select first TraceEnrty
    var traceEntries = document.getElementsByClassName('trace-entry');
    selectTraceEntry(traceEntries[0]);
};
