<?php
 /* The following line of codes are used for pagination */

$tableRows = '';
$rowsperpage = 25;  //define total number of results you want per page  
if (isset($_REQUEST['currentpage'])) {
    $currentpage = (int)$_REQUEST['currentpage']; //first item to display on this page
    $offset = ($currentpage - 1) * $rowsperpage; 
} else {
    $currentpage = 1; //if no page var is given, default to 1
    $offset = (int)($currentpage - 1) * $rowsperpage; //determine the sql LIMIT starting number for the results on the displaying page  
}
$originalSearch = '';
//user input serach data
if (isset($_REQUEST['search'])) {
    $originalSearch = $_REQUEST['search'];
    $search = "%" . $originalSearch . "%";
    $searchStr = "&search=" . $originalSearch;
} else {
    $search = '%';
    $searchStr = '';
}

// find the total number of results stored in the database

$numrows_stmt = mysqli_prepare($link, "SELECT COUNT(DISTINCT(s.siteAddress)) FROM `sites` as s INNER JOIN `site_qr_codes` as qr ON s.site_id = qr.site_id WHERE s.active=1 AND ((s.client LIKE ?) OR (s.siteAddress LIKE ?) OR (qr.qr_code LIKE ?))");
mysqli_stmt_bind_param($numrows_stmt, 'sss', $search, $search, $search);
mysqli_stmt_execute($numrows_stmt);
$numrows_qry = mysqli_stmt_get_result($numrows_stmt);
$numrows_result = mysqli_fetch_array($numrows_qry);
$numrows = $numrows_result[0]; //total number of siteAddresses

// Get dataset stored in the database

$rows_stmt = mysqli_prepare($link, "SELECT s.*, qr.* FROM `sites` as s INNER JOIN `site_qr_codes` as qr ON s.site_id = qr.site_id WHERE s.active=1 AND ((s.client LIKE ?) OR (s.siteAddress LIKE ?) OR (qr.qr_code LIKE ?)) GROUP BY `s`.`siteAddress`  ORDER BY `qr`.`qr_code` DESC LIMIT ?, ?");
mysqli_stmt_bind_param($rows_stmt, 'sssii', $search, $search, $search, $offset, $rowsperpage);
mysqli_stmt_execute($rows_stmt);
$rows_qry = mysqli_stmt_get_result($rows_stmt);

$totalpages = ceil($numrows / $rowsperpage); //determine the total number of pages available

//display the retrieved result on the webpage  

if (mysqli_num_rows($rows_qry) > 0) {
    $tableRows = '<thead><tr><th style="border-top-left-radius:5px">QR Code</th><th>Client</th><th style="border-top-right-radius:5px">Address</th></tr></thead>';
    $tableRows .= '<tbody>';
    while ($data = mysqli_fetch_array($rows_qry)) {
        $tableRows .= '<tr data-siteid="' . $data['site_id'] . '" class="row" ><td class="quote_id">' . $data['qr_code'] . '</td><td>' . $data['client'] . '</td><td>' . $data['siteAddress'] . '</td></tr>';
    }
    $tableRows .= "</tbody>";
}
?>
<main>
    <section id="home">
        <a href="dashboard.php?page=addSDC_site" class="home-btns">
            <span class="material-icons orange">add</span>
            <h2>Add Site</h2>
        </a>
		<!--  Search Site Data   -->
        <div style="grid-column: span 3">
            <h2 style="text-align:center;">Search Sites</h2>
            <div class="searchBar">
                <input class="fc" placholder="Search..." value="<?php echo $originalSearch; ?>">
                <button type="button" class="search">
                    <span class="material-icons orange">search</span>
                </button>
            </div>
        </div>

        <div style="grid-column: span 3;width:90%;" class="table siteTable" data-template="">
            <table data-role="<?php echo $usrRole; ?>" data-option="" cellpadding="10px" cellspacing="0" style="width:100%;">
                <colgroup>
                    <col colspan="1" style="width:12%">
                    <col colspan="1" style="width:30%">
                    <col colspan="1" style="width:58%">
                </colgroup>
                <?php echo $tableRows; ?>
            </table>
			
			<!--  Start Pagination Display -->
			
            <div class="pagination">
                <?php
                if (isset($currentpage) && is_numeric($currentpage)) {
                    $currentpage = (int)$currentpage;
                } else {
                    $currentpage = 1;
                }
                if ($currentpage > $totalpages) {
                    $currentpage = $totalpages;
                }
                if ($currentpage < 1) {
                    $currentpage = 1;
                }
                $range = 5;
				
				// Add serach parameter with the pagination
				
                if ($currentpage > 1) {
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=1" . $searchStr . "'>First</a> ";
                    $prevpage = $currentpage - 1;
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=" . $prevpage . $searchStr . "'><</a> ";
                }
                for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
                    if (($x > 0) && ($x <= $totalpages)) {
                        if ($x == $currentpage) {
                            echo "<span class='p_box'>$x</span>";
                        } else {
                            echo " <a href='dashboard.php?page=admin_SDC&currentpage=$x" . $searchStr . "'>$x</a> ";
                        }
                    }
                }
				// If we are not on the very last page we can place the Next button
                if ($currentpage != $totalpages) {
                    $nextpage = $currentpage + 1;
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=$nextpage" . $searchStr . "'>></a> ";
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=$totalpages" . $searchStr . "'>Last</a> ";
                }
                ?>
            </div>
			
			<!--  End Pagination Display -->
        </div>
    </section>
</main>