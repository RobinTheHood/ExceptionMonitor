<div id="exception-monitor-head">
    <title><?php echo $exeptionMonitorArgs['class'] . ' in ' . $exeptionMonitorArgs['traceEntries'][0]->getRelativeFilePath(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style><?php include $exeptionMonitorArgs['fileStyle']; ?></style>
    <style><?php include $exeptionMonitorArgs['fileStyleSyntax']; ?></style>
    <script><?php include $exeptionMonitorArgs['fileScript']; ?></script>
</div>

<div class="exception-monitor" id="exception-monitor-content" >
    <div class="header-frame">
        <div class="exception-class">
            <?php echo $exeptionMonitorArgs['namespace'] ?>
            <span class="exception-name">
                <?php echo $exeptionMonitorArgs['class'] ?>
            </span>
        </div>

        <div class="exception-message">
            <?php echo $exeptionMonitorArgs['errorType'] . ': ' . $exeptionMonitorArgs['exception']->getMessage() ?>
        </div>
    </div>
    <div class="content-frame">
        <div class="trace-frame">
            <?php foreach($exeptionMonitorArgs['traceEntries'] as $traceEntry) { ?>
                <div class="trace-entry trace-entry-selected" data-code="<?php echo $traceEntry->getIndex() ?>">
                    <div class="error-exception-class">
                        <span class="error-exceptiron-number"><?php echo $traceEntry->getIndex(); ?></span>
                        <?php echo $traceEntry->getFunctionWithArgs(); ?>
                    </div>
                    <div class="error-exception-file-path">
                        <?php echo $traceEntry->getRelativeFilePath() . ':' . $traceEntry->getLine(); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="code-frame" id="code-frame">
            <?php foreach($exeptionMonitorArgs['traceEntries'] as $traceEntry) { ?>
                <div id="<?php echo 'code-' . $traceEntry->getIndex(); ?>" class="source-code">
                    <?php echo $traceEntry->getCode(); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
