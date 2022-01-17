<!DOCTYPE html>
<html lang="en">
<head>
    <title>Domain Checker</title>
    <script src="<?php echo JS_DIR ?>DomainChecker.js" type="text/javascript"></script>
    <link href="<?php echo CSS_DIR ?>domain-checker.css" rel="stylesheet">
    <link rel="icon" type="image/png" href=""/>
</head>

<body>
<div class="main-content">
    <div class="upload-container">
        <h1>Please, upload .csv file(s) to check domains.</h1>
        <form id="uploadFilesForm" method="POST" action="/create-queue" class="upload-form">
            <label class="upload-field">
                <span>Upload file(s) here</span>
                <input type="file" accept=".csv" class="f_files-input" multiple>
            </label>

            <button type="submit">SUBMIT</button>
        </form>
    </div>
    <div class="results-container <?php if (empty($data['domainFiles'])) { ?> is--hidden<?php } ?>" id="resultsContainer">
        <?php if (!empty($data['domainFiles'])) {
                foreach ($data['domainFiles'] as $domainFile) {?>
                    <div class="result-item<?php if ($domainFile['progress'] < 100) { ?> f_result-item<?php } ?>" data-id="<?php echo $domainFile['fileId'];?>">
                        <div class="result-item-cell title-cell">
                            <p class="title f_title"><?php echo $domainFile['name'];?></p>
                        </div>
                        <div class="result-item-cell progress-cell">
                            <div class="progress"><div class="progress-runner f_progress-runner" style="width: <?php echo $domainFile['progress'];?>%"></div><span class="progress-percent f_progress-percent"><?php echo $domainFile['progress'];?>%</span></div>
                        </div>
                        <div class="result-item-cell action-cell f_action-cell<?php if ($domainFile['progress'] < 100) { ?> is--hidden<?php } ?>">
                            <a href="/result/<?php echo $domainFile['fileId'];?>" target="_blank">RESULT</a>
                        </div>
                    </div>
                <?php }
              }?>
    </div>
</div>

<div class="result-item is--hidden" id="resultItemForClone">
    <div class="result-item-cell title-cell">
        <p class="title f_title"></p>
    </div>
    <div class="result-item-cell progress-cell">
        <div class="progress"><div class="progress-runner f_progress-runner" style="width: 0%"></div><span class="progress-percent f_progress-percent">0%</span></div>
    </div>
    <div class="result-item-cell action-cell f_action-cell is--hidden">
        <a class="f_result-link" href="javascript: void(0);" target="_blank">RESULT</a>
    </div>
</div>
</body>

</html>

