<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $data['fileName'] ?> Result</title>
    <link href="<?php echo CSS_DIR ?>domain-checker-result.css" rel="stylesheet">
    <link rel="icon" type="image/png" href=""/>
</head>

<body>
<div class="result-content">
    <h1 class="header"><?php echo $data['fileName'] ?></h1>
    <?php if (!empty($data['domains'])) { ?>
        <div class="domains-container">
            <div class="domain-row header-row">
                <div class="id-cell cell">ID</div>
                <div class="domain-cell cell">Domain</div>
                <div class="validity-cell cell">Is Valid</div>
                <div class="expire-date-cell cell">Expire Date</div>
            </div>
            <div class="domain-row-group">
                <?php foreach ($data['domains'] as $domain) { ?>
                <div class="domain-row">
                    <div class="id-cell cell"><?php echo $domain['id']; ?></div>
                    <div class="domain-cell cell"><?php echo $domain['domain']; ?></div>
                    <div class="validity-cell cell"><span class="<?php echo $domain['is_valid'] ? 'valid' : 'invalid'; ?>"></span></div>
                    <div class="expire-date-cell cell"><?php echo $domain['expire_date']; ?></div>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if ($data['pagesCount']) { ?>
    <div class="pagination-container">
        <ul class="pagination">
            <?php for($i = 1; $i <= $data['pagesCount']; ++$i) { ?>
            <li><a href="/result/<?php echo $data['fileId']; ?>?p=<?php echo $i; ?>" class="<?php if($i === $data['currentPage']){ echo 'is--active';}?>"><?php echo $i; ?></a></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
</div>
</body>

</html>

