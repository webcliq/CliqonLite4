<?php
require_once("widget.php");

$params = array(

	/*
	// used by Redbean or maybe ODBC
	'connection => array(
		'dsn' => '',
		'type' => 'mysql', // postgres, sqllite, mssql
		'server' => 'localhost',
		'port' => '3306',
		'database' => '', // or file name
		'username' => '',
		'password' => '',
	)'

	{
	    "widgetid": "stockticker1",
	    "iframe": "false",
	    "iframeheight": "",
	    "widget": "allstock.php"
	}
	*/

    'tbl'       => 'clqitem',											// Table
    'tblid'     => 'id',                                    			// Keyfield
    'where'     => array('clq_reference|<>|´z´', 'clq_type|=|´daily´'),  	// WHERE
    'orderby'   => 'clq_reference ASC',                         			// ORDER BY
    'page'		=> 10, // Or positive number 5, 10, 15 etc.
    'filter'	=> true,
    'flds'      => array(
        
        // Field Definition
        // 0 = type, 1 = title, 2 = if clq_extra, then which field
        // 3 = data-class, 4 = data-hide, 5 = data-name
        // 6 = data-sort-initial, 7 = data-sort-ignore
        // 8 = data-value
        // 'code', 'name', 'low', 'high', 'range', 'exchange'
        
        'clq_reference'     => 'text|576:Code||expand|||ascending||',
        'clq_title'      	=> 'text|555:Company Name|||||||',
        'clq_extra[low]'			=> 'numeric|574:Low|low|txt-right|||||',
        'clq_extra[high]'			=> 'numeric|575:High|high|txt-right|||||',

    ),
    'dataformat' => 'recordset', // json
);

?>
<div class="" style="width: 295px; height:300px; overflow: auto; padding:0px; margin: 0px; float:left;">
<?php echo $clqw->displayTable($params) ?>
</div>